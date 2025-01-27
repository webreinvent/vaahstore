<?php

namespace VaahCms\Modules\Store\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Models\User;

class AuthController  extends Controller
{
    public function __construct()
    {
    }
    //------------------------------------------------

    public function authGenerateOTP(Request $request)
    {
        try {
            $response = User::sendLoginOtp($request, 'can-login-in-backend');
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return response()->json($response, 500);
        }
    }

    //------------------------------------------------

    public function authSendPasswordResetCode(Request $request)
    {
        try {
            $response = User::sendResetPasswordEmail($request, 'can-login-in-backend');
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }

            return response()->json($response, 500);
        }
    }

    //------------------------------------------------

    public function authResetPassword(Request $request)
    {
        try {
            $response = User::resetPassword($request);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }

            return response()->json($response, 500);
        }
    }
    //------------------------------------------------


    public function authSignIn(Request $request)
    {
        try {
            // Validation
            $inputs = $request->all();
            $rules = [
                'email' => 'required|max:150',
                'password' => 'required_without:type',
                'type' => 'nullable|in:otp',
                'login_otp' => 'required_if:type,otp',
                'remember' => 'nullable|boolean',
            ]; $messages = [
                'email.required' => 'The email or username is required.',
                'email.max' => 'The email field may not be greater than :max characters',
                'password.required_without' => 'Password is required',
                'login_otp.required_if' => 'OTP is required ',
            ];
            $validator = \Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
                $errors = errorsToArray($validator->errors());
                return [
                    'success' => false,
                    'errors' => $errors,
                ];
            }

            $user = User::where('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'errors' => ['Invalid credentials']]);
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
    //------------------------------------------------

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

        return response()->json(['success' => false, 'errors' => ['The OTP you entered is invalid.']]);
    }
    //------------------------------------------------

    protected function handleStandardLogin($user, $request)
    {
        if (Hash::check($request->password, $user->password)) {

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
        return response()->json(['success' => false, 'errors' => ['The password you entered is incorrect.']]);
    }

    //------------------------------------------------

    public function authSignOut(Request $request)
    {
        try {
            if ($user = Auth::guard('api')->user()) {
                $user->update([
                    'api_token' => null,
                    'remember_token' => null,
                ]);
                $response = [
                    'success' => true,
                    'message' => ['Logout successfully.'],
                    'data' => [],
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => ['No user is currently logged in.'],
                ];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = [trans("vaahcms-general.something_went_wrong")];
            }

            return response()->json($response, 500);
        }
    }

    //-----------------------------------------------------------------------
    public function signUp(Request $request)
    {
        try {
            $response = \VaahCms\Modules\Store\Models\User::createItem($request);
            if (isset($response['success']) && $response['success'] === true) {
                $user = $response['data']['item'];

                $user->update(['api_token' => Str::random(80)]);
                return response()->json([
                    'success' => true,
                    'data' => ['item' => $user->makeVisible(['api_token'])],
                ]);
            }
            return response()->json($response);

        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }

            return response()->json($response, 500);
        }
    }
}
