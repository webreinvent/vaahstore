<?php

namespace VaahCms\Modules\Store\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Models\User;

class PublicController  extends Controller
{
    public function __construct()
    {
    }
    //------------------------------------------------

    public function authGenerateOTP(Request $request)
    {
        $response = User::sendLoginOtp($request, 'can-login-in-backend');
        return response()->json($response);

    }
    //------------------------------------------------

    public function authSendPasswordResetCode(Request $request)
    {
        $response = User::sendResetPasswordEmail($request, 'can-login-in-backend');

        return response()->json($response);
    }
    //------------------------------------------------

    public function authResetPassword(Request $request)
    {
        $response = User::resetPassword($request);

        return response()->json($response);

    }

    public function authSignIn(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'email' => 'required|max:150',
                'password' => 'required_without:type',
                'type' => 'nullable|in:otp',
                'login_otp' => 'required_if:type,otp',
            ], [
                'email.required' => trans('vaahcms-login.email_or_username_required'),
                'email.max' => trans('vaahcms-login.email_or_username_limit'),
                'password.required_without' => trans('vaahcms-login.password_required'),
                'login_otp.required_if' => trans('vaahcms-login.otp_required'),
            ]);

            $response = ['success' => false, 'errors' => []];

            $user = User::where('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'errors' => [trans('vaahcms-user.no_user_exist')]]);
            }
            if ($user->is_active != 1) {
                return response()->json(['success' => false, 'errors' => [trans('vaahcms-login.inactive_account')]]);
            }
            // Handle OTP login
            if ($request->type === 'otp') {
                return $this->handleOtpLogin($user, $request);
            }
            // Handle without Otp login
            return $this->handleStandardLogin($user, $request);

        }catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }

            return response()->json($response);
        }
    }

    protected function handleOtpLogin($user, $request)
    {
        // Check OTP
        if (Hash::check(trim($request->login_otp), $user->login_otp)) {
            Auth::login($user, $request->boolean('remember'));

            $user->update([
                'login_otp' => null,
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'api_token' => Str::random(80),
            ]);

            return response()->json([
                'success' => true,
                'data' => ['item' => $user->makeVisible(['api_token'])],
            ]);
        }

        return response()->json(['success' => false, 'errors' => [trans('vaahcms-login.invalid_credentials')]]);
    }

    protected function handleStandardLogin($user, $request)
    {
        $login_response = User::login($request);

        // If login failed
        if (!empty($login_response['status']) && $login_response['status'] === 'failed') {
            return response()->json($login_response);
        }

        // Update API token after successful login
        $user->update(['api_token' => Str::random(80)]);

        return response()->json([
            'success' => true,
            'data' => ['item' => $user->makeVisible(['api_token'])],
        ]);
    }


}
