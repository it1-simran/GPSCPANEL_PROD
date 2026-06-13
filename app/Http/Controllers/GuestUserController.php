<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuestApprovalUser;
use App\Writer;
use App\DeviceCategory;
use App\Helper\CommonHelper;
use App\Mail\SendAccountRequestMail;
use App\Services\PermissionAssignmentService;
use App\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRejectedMail;
use App\Mail\UserApprovedMail;
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
            return response()->view('errors.link_expired', [
                'message' => 'This registration link has expired or is invalid. Please request a new invitation from your administrator.',
            ], 403);
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
            if (!in_array($user->status, ['RequestMailSent', 'RejectedBySupport', 'RejectedByAdmin'])) {
                return response()->view('errors.custom_message', [
                    'title' => 'Pending Approval',
                    'message' => 'Your registration request has been submitted successfully and is awaiting approval.',
                    'color' => '#ffc107', // yellow
                ], 403);
            }
        }

        // 3️⃣ Otherwise, show the registration form
        return view('userRegister', compact('name', 'email', 'user'));
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
            'user_type' => 'required|string|in:Manufacturer,Dealer',
        ]);

        $guest = GuestApprovalUser::where('email', $request->email)->first();

        // Send the email
        Mail::to($request->email)->send(new SendAccountRequestMail($request->name, $request->email));

        if ($guest) {
            // Update existing guest
            $guest->update([
                'name'         => $request->name,
                'userType'     => $request->user_type,
                'status'       => 'RequestMailSent',
                'resend_count' => $guest->resend_count + 1,
            ]);
        } else {
            // Create new guest entry
            GuestApprovalUser::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'userType'     => $request->user_type,
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
        // Permission check - only admin/support can delete requests
        if (Auth::user()->user_type !== 'Admin' && !Auth::user()->hasPermission('account_management.delete')) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to delete requests.'
            ], 403);
        }

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
        // Permission check - only admin/support can update approval status
        if (Auth::user()->user_type !== 'Admin' && Auth::user()->user_type !== 'Support' && !Auth::user()->hasPermission('account_management.edit')) {
            abort(403, 'You do not have permission to update approval status');
        }

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
            $finalConfig = $userConfiguration;
            if ($defaultTemplate) {
                $defaultTempConfig = json_decode($defaultTemplate->configurations, true);
                $finalConfig = $userConfiguration + $defaultTempConfig;
            }
            // Ensure ping_interval and is_editable are set if they are not in user configurations
            if (!isset($finalConfig['ping_interval'])) {
                $finalConfig['ping_interval'] = ["id" => 77, "value" => 4];
            }
            if (!isset($finalConfig['is_editable'])) {
                $finalConfig['is_editable'] = ["id" => 78, "value" => 1];
            }
            $writerConfigArr[] = $finalConfig;

            $targetUserType = (strtolower($user->userType) == 'manufacturer' ? 'Reseller' : 'User');
            $writer = Writer::create([
                'name'              => $user->name,
                'email'             => $user->email,
                'mobile'            => $user->phone,
                'user_type'         => $targetUserType,
                'timezone'          => $user->timezone,
                'password'          => Hash::make('123456'),
                'LoginPassword'     => '123456',
                'showLoginPassword' => '123456',
                'device_category_id' => $user->deviceCategory,
                'configurations'    => json_encode($writerConfigArr),
                'created_by'        => Auth::id(),
                'parent_user_id'    => Auth::user()->user_type === 'Reseller' ? Auth::id() : null,
            ]);

            $defaultPermissions = app(PermissionAssignmentService::class)
                ->getDefaultPermissionIdsForNewAccount(Auth::user(), $targetUserType);
            $writer->permissions()->sync($defaultPermissions);

            // ✅ Create default template for this user (mirrors Add User logic)
            Template::create([
                'id_user'            => $writer->id,
                'template_name'      => 'default',
                'device_category_id' => $user->deviceCategory,
                'configurations'     => json_encode($finalConfig),
                'default_template'   => 1,
                'verify'             => 2 // 2 corresponds to user-level template usually
            ]);

            // Send success email with credentials
            Mail::to($user->email)->send(new UserApprovedMail($user, '123456'));
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
            $formattedFields[] = [
                'id' => $field['id'],
                'key' => $field['key'],
                'type' => $field['type'],
                'default' => $field['default'],
                'required' => $field['requiredFieldInput'] ?? false,
                'maxValueInput' => $field['maxValueInput'] ?? null,
                'numberRange' => $field['numberRange'] ?? null,
                'selectOptions' => $field['selectOptions'] ?? [],
                'validation' => json_decode($dataFieldOptions->validationConfig ?? '{}')
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
            ->where('email', $request->email)
            ->whereNotIn('status', ['RequestMailSent', 'RejectedBySupport', 'RejectedByAdmin'])
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
        $user_name = $request->name;
        Mail::send('emails.registration_otp', ['otp' => $otp, 'user_name' => $user_name], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Email Verification OTP - ' . config('app.name'));
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
