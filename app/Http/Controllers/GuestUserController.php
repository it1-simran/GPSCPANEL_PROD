<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuestApprovalUser;
use App\Writer;
use App\DeviceCategory;
use App\Helper\CommonHelper;
use App\Mail\SendAccountRequestMail;
use App\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRejectedMail;
use Illuminate\Support\Facades\DB;

class GuestUserController extends Controller
{
    /**
     * Show the user registration form.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 1️⃣ Check if the signed link is still valid
        if (!$request->hasValidSignature()) {
            abort(403, 'This link has expired or is invalid.');
        }

        $email = $request->query('email');
        $name = $request->query('name');

        // 2️⃣ Check if the user already exists in the approval table
        $user = GuestApprovalUser::where('email', $email)->first();

        if ($user) {
            if ($user->status === 'approved') {
                return response()->view('errors.custom_message', [
                    'title' => 'Already Registered',
                    'message' => 'You have already completed your registration. Please log in to continue.',
                    'color' => '#28a745', // green
                ], 403);
            }

            // Case B: If registration request is already submitted and pending
            if ($user->status !== 'RequestMailSent') {
                return response()->view('errors.custom_message', [
                    'title' => 'Pending Approval',
                    'message' => 'Your registration request has been submitted successfully and is awaiting approval.',
                    'color' => '#ffc107', // yellow
                ], 403);
            }
        }

        // 3️⃣ Otherwise, show the registration form
        return view('userRegister', compact('name', 'email'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'required|string|max:15',
            'user_type'      => 'required|string',
            'device_category' => 'required|string',
        ]);

        $config = $request->config ?? [];
        $idSet = $request->ids ?? [];

        $formattedRow = [];
        foreach ($config as $key => $value) {
            if ($key === 'template') continue;
            $formattedRow[$key] = [
                'id'    => $idSet[$key . '_id'] ?? null,
                'value' => $value,
            ];
        }

        // Check if record exists by email or phone
        $existing = GuestApprovalUser::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();

        if ($existing) {
            // ✅ Update existing record
            $existing->update([
                'name'           => $request->name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'userType'       => $request->user_type,
                'deviceCategory' => $request->device_category,
                'configurations' => json_encode($formattedRow),
                'status'         => 'SupportApprovalPending', // or whatever your column name is
                'timezone'       => $request->timezone
            ]);

            $message = 'User information updated successfully and status set to pending.';
        } else {
            // ✅ Create new record
            GuestApprovalUser::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'userType'       => $request->user_type,
                'deviceCategory' => $request->device_category,
                'configurations' => json_encode($formattedRow),
                'status'         => 'SupportApprovalPending', // ensure this column exists
                'timezone'       => $request->timezone
            ]);

            $message = 'Registration submitted successfully! Pending approval.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function send(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        $guest = GuestApprovalUser::where('email', $request->email)->first();

        // Send the email
        Mail::to($request->email)->send(new SendAccountRequestMail($request->name, $request->email));

        if ($guest) {
            // Update existing guest
            $guest->update([
                'name'         => $request->name,
                'status'       => 'RequestMailSent',
                'resend_count' => $guest->resend_count + 1,
            ]);
        } else {
            // Create new guest entry
            GuestApprovalUser::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'status'       => 'RequestMailSent',
                'resend_count' => 1,
            ]);
        }

        return redirect()->back()->with('success', 'Request sent successfully to user.');
    }

    public function showApprovalRequest()
    {
        $url_type = self::getURLType();
        if (Auth()->user()->user_type === 'Admin') {
            // If user is admin → fetch based on supportApproved pending
            $pendingRequests = GuestApprovalUser::get();
        } else {
            // Otherwise → fetch based on normal status pending
            $pendingRequests = GuestApprovalUser::where('status', 'SupportApprovalPending')->orWhere('status', 'RequestMailSent')->get();
        }

        return view('view-approval-request', compact('pendingRequests', 'url_type'));
    }
    public function deleteRequest($id)
    {
        $guest = GuestApprovalUser::find($id);
        if (!$guest) {
            return response()->json([
                'status' => false,
                'message' => 'Guest not found.'
            ], 404);
        }
        $guest->delete();
        return  redirect()->back()->with('success', 'Delete Successfully !!');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = GuestApprovalUser::findOrFail($id);
        $userConfiguration = json_decode($user->configurations, true);

        if ($request->action === 'Approved') {
            $existingWriter = Writer::where('email', $user->email)
                ->where('device_category_id', $user->deviceCategory)
                ->first();
            if ($existingWriter) {
                if (Auth::user()->user_type != 'admin') {
                    $user->status = 'RejectedBySupport';
                } else {
                    $user->status = 'RejectedByAdmin';
                }
                $user->save();
                return redirect()->back()->with('error', 'Writer already exists for this user & device category. Request rejected.');
            }
            $user->status = 'Approved';
            $user->save();
            $defaultTemplate = Template::where([
                'device_category_id' => $user->deviceCategory,
                'default_template'   => 1
            ])->first();
            $writerConfigArr = [];
            if ($defaultTemplate) {
                $defaultTempConfig = json_decode($defaultTemplate->configurations, true);
                $final = $userConfiguration + $defaultTempConfig;
                $writerConfigArr[] = $final;
            }
            Writer::create([
                'name'              => $user->name,
                'email'             => $user->email,
                'mobile'            => $user->phone,
                'userType'          => $user->userType,
                'timezone'          => $user->timezone,
                'password'          => Hash::make('123456'),
                'LoginPassword'     => '123456',
                'showLoginPassword' => '123456',
                'device_category_id' => $user->deviceCategory,
                'configurations'    => json_encode($writerConfigArr),
                'created_by'        => Auth::id()
            ]);
        } elseif ($request->action === 'AdminApprovalPending') {
            $user->status = 'AdminApprovalPending';
            $user->save();
        } elseif ($request->action === 'reject') {
            if (Auth::user()->user_type != 'admin') {
                $user->status = 'RejectedBySupport';
            } else {
                $user->status = 'RejectedByAdmin';
            }
            $user->description = $request->reason;
            $user->save();
            Mail::to($user->email)->send(new UserRejectedMail($user, $request->reason));
        }
        return redirect()->back()->with('success', 'User request has been ' . $user->status . ' successfully.');
    }


    public function getDeviceCategoryConfig($id)
    {
        $category = DeviceCategory::findOrFail($id);
        $templates = Template::where(['device_category_id' => $id])->get();

        $fields = json_decode($category->inputs, true);
        
        $formattedFields = [];
        foreach ($fields as $field) {
            $dataFieldOptions = CommonHelper::getDataFieldById($field['id']);
            $validationConfig = json_decode($dataFieldOptions->validationConfig);
            $formattedFields[] = [
                'id' => $field['id'],
                'key' => $field['key'],
                'type' => $field['type'],
                'default' => $field['default'],
                'validation' => $validationConfig,
                'options' => $dataFieldOptions->options ?? []
            ];
        }
        // Assuming "configuration" column stores your JSON
        return response()->json([
            'config' => $formattedFields,
            'templates' => $templates ?? []
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email',
            'phone'           => 'required|string|max:15',
            'user_type'       => 'required|string',
            'device_category' => 'required|string',
        ]);

        // 🔹 Check if already exists in Writers
        $existsInWriters = DB::table('writers')
            ->where('email', $request->email)
            ->orWhere('mobile', $request->phone)
            ->exists();

        if ($existsInWriters) {
            return response()->json([
                'success' => false,
                'message' => 'Email or Phone already exists in the system.'
            ]);
        }

        // 🔹 Check if already exists in GuestApprovalUser
        $existsInGuestApproval = DB::table('guestapprovaluser')
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->where('status', '!=', 'RequestMailSent');
            })
            ->exists();


        if ($existsInGuestApproval) {
            return response()->json([
                'success' => false,
                'message' => 'Request already exists and is pending for approval.'
            ]);
        }

        // 🔹 Generate OTP
        $otp = rand(100000, 999999); // 6-digit OTP
        Session::put('otp', $otp);
        Session::put('otp_email', $request->email); // Store email for verification step

        // 🔹 Send OTP email
        Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Your Email Verification OTP');
        });

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to ' . $request->email
        ]);
    }


    public function verifyOtp(Request $request)
    {
        if ($request->otp == Session::get('otp')) {
            Session::forget('otp');
            return response()->json(['valid' => true]);
        }
        return response()->json(['valid' => false]);
    }
    // public function updateStatus(Request $request, $id)
    // {
    //     $user = GuestApprovalUser::findOrFail($id);

    //     if ($request->action === 'approve') {
    //         $user->status = 'approved';
    //     } elseif ($request->action === 'reject') {
    //         $user->status = 'rejected';
    //     }

    //     $user->save();

    //     return redirect()->back()->with('success', 'User request has been ' . $user->status . ' successfully.');
    // }
}
