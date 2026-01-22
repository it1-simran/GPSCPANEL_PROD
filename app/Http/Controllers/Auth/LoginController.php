<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 👈 use this
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OtpMail;
use App\Mail\TwoFactorTokenMail;
use App\Models\User;
use App\Writer;
use Illuminate\Support\Facades\Session;
use Exception;

class LoginController extends Controller
{
    // REMOVE use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:admin')->except('logout');
        $this->middleware('guest:writer')->except('logout');
    }

    // ... the rest of your code
    //}

    // <?php



    // namespace App\Http\Controllers\Auth;



    // use App\Http\Controllers\Controller;

    // use Illuminate\Foundation\Auth\AuthenticatesUsers;

    // use Illuminate\Http\Request;

    // use Auth;

    // use DB;

    // use Carbon\Carbon;
    // use Illuminate\Support\Facades\Hash;
    // use Illuminate\Support\Facades\Config;
    // use Illuminate\Support\Facades\Mail;
    // use Illuminate\Support\Str;
    // use App\Mail\OtpMail;
    // use Exception;


    // class LoginController extends Controller

    // {

    //     /*

    //     |--------------------------------------------------------------------------

    //     | Login Controller

    //     |--------------------------------------------------------------------------

    //     |

    //     | This controller handles authenticating users for the application and

    //     | redirecting them to your home screen. The controller uses a trait

    //     | to conveniently provide its functionality to your applications.

    //     |

    //     */



    //     use AuthenticatesUsers;



    //     /**

    //      * Where to redirect users after login.

    //      *

    //      * @var string

    //      */

    //     protected $redirectTo = '/home';



    //     /**

    //      * Create a new controller instance.

    //      *

    //      * @return void

    //      */

    //     public function __construct()

    //     {

    //         $this->middleware('guest')->except('logout');

    //         $this->middleware('guest:admin')->except('logout');

    //         $this->middleware('guest:writer')->except('logout');
    //     }


    public function showLoginForm()
    {
        return view('auth.login_new'); // your single login blade
    }

    public function getTwoFactorAuthentication(Request $request)
    {
        return view('auth.twoFactorAuthentication');
    }
    public function submitTwoFactorAuthentication(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'required|numeric',
            'email' => 'required|email',
        ]);

        $user = Writer::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if ($user->twoFactorAuthToken != $request->two_factor_code) {
            return back()->withErrors(['two_factor_code' => 'Invalid OTP']);
        }

        if (Carbon::parse($user->two_factor_expires_at)->lt(now())) {
            return back()->withErrors(['two_factor_code' => 'OTP expired']);
        }

        // Reset OTP
        $user->update([
            'twoFactorAuthToken' => null,
            'two_factor_expires_at' => null,
        ]);

        Auth::login($user);

        switch (strtolower($user->user_type)) {
            case 'admin':
                return redirect()->intended('/admin');
            case 'user':
                return redirect()->intended('/user');
            case 'reseller':
                return redirect()->intended('/reseller');
            case 'support':
                return redirect()->intended('/support');
            default:
                return redirect()->intended('/login'); // fallback
        }
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'required|in:on',
        ], [
            'remember.required' => 'Checkbox must be checked.',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            // dd($user);
            // ✅ TWO FACTOR AUTHENTICATION
            if ($user->twoFactorAuthentication) {
                // Generate and store 2FA token
                $token = rand(1000, 9999);
                DB::table('writers')
                    ->where('id', '=', $user->id)
                    ->update(['twoFactorAuthToken' => $token, 'two_factor_expires_at' => now()->addMinutes(10)]);

                // Send token via email
                Mail::to($user->email)->send(new TwoFactorTokenMail($token, $user));

                // Logout temporarily
                Auth::logout();

                // Store 2FA session
                Session::put('email', $user->email);
                Session::put('2fa:remember', $request->filled('remember'));

                return redirect()->route('2fa.form');
            }

            // ✅ Reset today_pings if outdated
            if ($user->pings_date != Carbon::today()->toDateString()) {
                DB::table('writers')
                    ->where('pings_date', '!=', Carbon::today()->toDateString())
                    ->update(['today_pings' => 0]);
            }

            // ✅ Redirect based on user_type
            switch (strtolower($user->user_type)) {
                case 'admin':
                    return redirect()->intended('/admin');
                case 'user':
                    return redirect()->intended('/user');
                case 'reseller':
                    return redirect()->intended('/reseller');
                case 'support':
                    return redirect()->intended('/support');
                default:
                    return redirect()->intended('/login'); // fallback
            }
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //         'remember' => 'required|in:on',
    //     ], [
    //         'remember.required' => 'Checkbox must be checked.',
    //     ]);

    //     $credentials = $request->only('email', 'password');
    //     //dd(Auth::attempt($credentials, $request->filled('remember')));

    //     // Attempt login with writer guard (or default guard for unified login)
    //     if (Auth::attempt($credentials, $request->filled('remember'))) {
    //         $user = Auth::user();

    //         // Reset today_pings if needed
    //         if ($user->pings_date != Carbon::today()->toDateString()) {
    //             DB::table('writers')
    //               ->where('pings_date', '!=', Carbon::today()->toDateString())
    //               ->update(['today_pings' => 0]);
    //         }

    //         // Redirect based on lowercase user_type
    //         switch (strtolower($user->user_type)) {
    //             case 'admin':
    //                 return redirect()->intended('/admin');
    //             case 'user':
    //                 return redirect()->intended('/user');
    //             case 'reseller':
    //                 return redirect()->intended('/reseller');
    //             case 'support':
    //                 return redirect()->intended('/support');
    //             default:
    //                 return redirect()->intended('/login'); // fallback
    //         }
    //     }
    //     return back()->withErrors([
    //         'password' => 'The provided credentials do not match our records.',
    //     ])->withInput($request->only('email', 'remember'));
    // }


    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     // Attempt login using writer guard
    //     if (Auth::guard('web')->attempt($credentials)) {
    //         $request->session()->regenerate();

    //         // Get logged-in user
    //         $user = Auth::guard('web')->user();
    //         // dd($user->user_type);
    //         // Redirect based on user_type
    //         switch ($user->user_type) {
    //             case 'Admin':
    //                 return redirect()->intended('/admin');
    //             case 'User':
    //                 return redirect()->intended('/user');
    //             case 'Reseller':
    //                 return redirect()->intended('/reseller');
    //             default:
    //                 return redirect()->intended('/login'); // fallback
    //         }
    //     }

    //     return back()->withErrors([
    //         'email' => 'The credentials do not match our records.',
    //     ]);
    // }
    /**

     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View

     */

    public function showAdminLoginForm()

    {
        return view('auth.login_new', ['url' => 'admin']);

        // return view('auth.login_new', [

        //     'url' => Config::get('constants.guards.admin')

        // ]);
    }



    /**

     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View

     */

    public function showWriterLoginForm()
    {

        return view('auth.login_new', ['url' => 'writer']);
    }

    public function showResellerLoginForm()
    {

        return view('auth.login_new', ['url' => 'reseller']);
    }



    /**

     * @param Request $request

     * @return array

     */

    protected function validator(Request $request)

    {

        return $this->validate($request, [

            'email'   => 'required|email',

            'password' => 'required|min:6'

        ]);
    }



    /**

     * @param Request $request

     * @param $guard

     * @return bool

     */

    protected function guardLogin(Request $request, $guard)

    {

        $this->validator($request);



        return Auth::guard($guard)->attempt(

            [

                'email' => $request->email,

                'password' => $request->password

            ],

            $request->get('remember')

        );
    }



    /**

     * @param Request $request

     *

     * @return \Illuminate\Http\RedirectResponse

     */

    public function adminLogin(Request $request)

    {
        $validator  = $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required',
            'remember' =>  'required:in:on',
        ], [
            'remember.required' => 'checkbox must be checked.'
        ]);

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password, 'user_type' => 'Admin'], $request->get('remember'))) {
            $todatPingsDate = DB::table('writers')->get();

            foreach ($todatPingsDate as $key => $value) {
                if ($value->pings_date != Carbon::today()->toDateString()) {
                    DB::table('writers')->Where('pings_date', '!=', Carbon::today()->toDateString())->update([
                        'today_pings' => '0'
                    ]);
                }
            }
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.'
        ])->withInput($request->only('email', 'remember'));
    }







    /**

     * @param Request $request

     *

     * @return \Illuminate\Http\RedirectResponse

     */

    public function writerLogin(Request $request)

    {

        $this->validate($request, [

            'email'   => 'required|email',
            'password' => 'required',
            'remember' =>  'required:in:on',
        ], [
            'remember.required' => 'checkbox must be checked.'
        ]);



        if (Auth::guard('writer')->attempt(['is_deleted' => 0, 'user_type' => 'User', 'email' => $request->email, 'password' => $request->password], $request->get('remember'))) {



            $todatPingsDate = DB::table('writers')->get();

            foreach ($todatPingsDate as $key => $value) {







                if ($value->pings_date != Carbon::today()->toDateString()) {



                    DB::table('writers')->Where('pings_date', '!=', Carbon::today()->toDateString())->update([

                        'today_pings' => '0'



                    ]);
                }
            }



            return redirect()->intended('/user');
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.'
        ])->withInput($request->only('email', 'remember'));
    }

    public function resellerLogin(Request $request)

    {

        $this->validate($request, [

            'email'   => 'required|email',

            'password' => 'required',
            'remember' =>  'required:in:on',
        ], [
            'remember.required' => 'checkbox must be checked.'


        ]);



        if (Auth::guard('reseller')->attempt(['is_deleted' => 0, 'user_type' => 'Reseller', 'email' => $request->email, 'password' => $request->password], $request->get('remember'))) {



            $todatPingsDate = DB::table('writers')->get();

            foreach ($todatPingsDate as $key => $value) {







                if ($value->pings_date != Carbon::today()->toDateString()) {



                    DB::table('writers')->Where('pings_date', '!=', Carbon::today()->toDateString())->update([

                        'today_pings' => '0'



                    ]);
                }
            }


            return redirect()->intended('/reseller');
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.'
        ])->withInput($request->only('email', 'remember'));
    }
    public function sendOtp(Request $request)
    {
        $otp = Str::random(6); // Generate OTP
        $email = $request->email;
        // Update OTP for the user
        $updateCount = DB::table('writers')
            ->where('email', $email)
            ->update(['otp' => $otp]);
        // $mailText = ' <h2>Your OTP for Login</h2><p>Your OTP is: <strong>'.$otp.'</strong></p><p>This OTP is valid for a limited time and should not be shared with anyone.</p>';
        // try {
        //     $otp = strval(random_int(100000, 999999)); // Generate OTP

        //     // Send email with OTP
        //     Mail::to($email)->send(new OtpMail($otp));

        //     return "OTP sent successfully!";
        // } catch (Exception $e) {
        //     // Log the exception or handle it gracefully
        //     return "Failed to send OTP: " . $e->getMessage();
        // }


        // Mail::send([], [], function($message) use ($request, $otp) {
        //     $message->to($request->email)
        //             ->subject('Your OTP for verification')
        //             ->setBody('Your OTP is: ' . $otp);
        // });
        if ($updateCount) {
            return json_encode(['status' => 200, 'success' => 'OTP updated successfully.', 'email' => $email]);
        }
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp.*' => 'required', // Each OTP digit is required and must be a single digit
        ]);

        $otp = implode('', $request->otp);
        $user = DB::table('writers')
            ->where('email', $request->verifyEmail) // Example: Use session to fetch email
            ->first();
        if (!$user) {
            return json_encode(['status' => 403, 'message' => 'User not found!']);
        }
        if ($otp != $user->otp) {
            return json_encode(['status' => 403, 'message' => 'Invalid OTP!']);
        }


        DB::table('writers')
            ->where('email', $user->email)
            ->update(['otp' => null]);

        return json_encode(['status' => 200, 'success' => 'otp verified Successfully', 'id' => $user->id]);
    }
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'newPassword' => 'required|string|min:4',
            'confirmNewPassword' =>  'required|string|min:4',
        ],);
        $password = $request->newPassword;
        $confirmPassword = $request->confirmNewPassword;
        if ($password != $confirmPassword) {
            return json_encode(['status' => 403, 'message' => 'password and confirm password is not  matched .']);
        }
        // Update OTP for the user
        $updateCount = DB::table('writers')
            ->where('id', $request->userId)
            ->update(['password' => Hash::make($request->newPassword), 'LoginPassword' => $request->newPassword, 'showLoginPassword' => $request->newPassword]);
        if ($updateCount) {
            return json_encode(['status' => 200, 'message' => 'password reset sucessfully!!']);
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }
    }
    public function logout(Request $request)
    {

        Auth::logout();
        // Auth::guard('admin')->logout();
        // Auth::guard('writer')->logout();
        // Auth::guard('user')->logout();
        // Auth::guard('reseller')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Redirect to a desired page after logout
    }
}
