<?php

namespace VaahCms\Modules\Store\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            if (isset($response['data'])) {
                $response['data'] = null;
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
            return response()->json($response);
        }
    }

    //------------------------------------------------

    public function authSendPasswordResetCode(Request $request)
    {
        try {
            $response = User::sendResetPasswordEmail($request, 'can-login-in-backend');
            if (isset($response['data']) && $response['data'] === []) {
                $response['data'] = null;
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

            return response()->json($response);
        }
    }

    //------------------------------------------------

    public function authResetPassword(Request $request)
    {
        try {
            $response = User::resetPassword($request);
            if (isset($response['data'])) {
                $response['data'] = null;
            }
            return response()->json($response);

        }  catch (\Exception $e) {
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




    //------------------------------------------------

    public function authSignOut(Request $request)
    {
        try {
            if ($user = Auth::guard('api')->user()) {
                $user->currentAccessToken()->delete();
                $response = [
                    'success' => true,
                    'message' => ['Logout successfully.'],
                    'data' => null,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => ['No user is currently logged in.'],
                ];
                return response()->json($response);
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

            return response()->json($response);
        }
    }

    //-----------------------------------------------------------------------
    public function signUp(Request $request)
    {
        try {
            $response = \VaahCms\Modules\Store\Models\User::createItem($request);
            if (isset($response['success']) && $response['success'] === true) {
                $user = $response['data']['item'];

                $max_sessions = 5;
                if ($user->tokens()->count() >= $max_sessions) {
                    $user->tokens()->oldest()->first()->delete();
                }

                $expiration = Carbon::now()->addDays(2);

                $token = $user->createToken('VaahStore')->plainTextToken;

                $user->tokens()->latest()->first()->update(['expires_at' => $expiration]);
                return self::generateAuthResponse($user,$request,'Saved successfully.');
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

            return response()->json($response);
        }
    }
    //-----------------------------------------------------------------------

    public function authSignIn(Request $request)
    {
        try {
            $request->merge([
                'identifier_key' => $request->identifier_key ?? 'email',
                'authentication_type' => $request->authentication_type ?? 'password',
            ]);
            $validator = self::validateLoginRequest($request);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->all(),
                ]);
            }

            $user = self::findUser($request);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Invalid credentials'],
                ]);
            }

            if ($request->authentication_type === 'otp') {
                return self::handleOtpLogin($user, $request);
            }

            return self::handleStandardLogin($user, $request);

        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = [trans("vaahcms-general.something_went_wrong")];
            }
            return response()->json($response);
        }
    }
    //-----------------------------------------------------------------------

    protected static function validateLoginRequest(Request $request)
    {
        $rules = [
            'identifier_key' => 'required|in:email,username,phone',
            'identifier_value' => 'required',
            'authentication_type' => 'required|in:password,otp',
            'authentication_value' => 'required|string',
            'remember' => 'nullable|boolean',
        ];
        if ($request->identifier_key === 'phone') {
            $rules['identifier_value'] .= '|numeric';
        }
        $messages = [
            'identifier_key.required' => 'Identifier key is required.',
            'identifier_key.in' => 'The selected identifier key is invalid. It must be one of: email, username, phone.',
            'identifier_value.required' => 'Identifier value is required.',
            'authentication_type.required' => 'Authentication type is required.',
            'authentication_value.required' => 'Authentication value is required.',

            'identifier_value.numeric' => 'The phone number must be a valid numeric value.',
        ];

        return \Validator::make($request->all(), $rules, $messages);
    }
    //-----------------------------------------------------------------------

    protected static function findUser(Request $request)
    {
        $identifier_key = $request->identifier_key;
        $identifier_value = $request->identifier_value;

        switch ($identifier_key) {

            case 'email':
                return \VaahCms\Modules\Store\Models\User::where('email', $identifier_value)->first();
            case 'username':
                return \VaahCms\Modules\Store\Models\User::where('username', $identifier_value)->first();
            case 'phone':
                if (is_numeric($identifier_value)) {
                    return \VaahCms\Modules\Store\Models\User::where('phone', $identifier_value)->first();
                }
                return null;
            default:
                return null;
        }
    }
    //-----------------------------------------------------------------------

    protected static function handleStandardLogin($user, $request)
    {
        if (Hash::check($request->authentication_value, $user->password)) {
            return self::generateAuthResponse($user, $request, 'SignIn Successfully.');
        }

        return response()->json([
            'success' => false,
            'errors' => ['The password you entered is incorrect.'],
        ]);
    }
    //-----------------------------------------------------------------------

    protected static function generateAuthResponse($user, $request,$message = null)
    {
        $max_sessions = 5;
        if ($user->tokens()->count() >= $max_sessions) {
            $user->tokens()->oldest()->first()->delete();
        }

        $expiration = $request->remember_me ? Carbon::now()->addDays(7) : Carbon::now()->addDays(2);

        $token = $user->createToken('VaahStore')->plainTextToken;
        $user->tokens()->latest()->first()->update(['expires_at' => $expiration]);

        $user->makeVisible('api_token');
        $data = [
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'display_name' => $user->display_name,
            'username' => $user->username,
            'is_active' => $user->is_active,
            'created_ip' => $user->created_ip,
            'updated_by' => $user->updated_by,
            'updated_at' => $user->updated_at,
            'created_by' => $user->created_by,
            'uuid' => $user->uuid,
            'created_at' => $user->created_at,
            'id' => $user->id,
            'avatar' => $user->avatar,
            'name' => $user->name,
            'cart_uuid' => $user->cart_uuid,
            'cart_products_count' => $user->cart_products_count,
            'api_token' => $token,
            'expires_at' => $expiration->toDateTimeString(),
        ];
        $response = [
            'success' => true,
        ];
        if ($message) {
            $response['messages'] = [$message];
        }
        $response['data'] = $data;

        return $response;

    }
    //-----------------------------------------------------------------------

    protected static function handleOtpLogin($user, $request)
    {
        if (Hash::check(trim($request->authentication_value), $user->login_otp)) {
            Auth::login($user);

            $user->update([
                'login_otp' => null,
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return self::generateAuthResponse($user,$request, 'SignIn Successfully.');
        }

        return response()->json([
            'success' => false,
            'errors' => ['The OTP you entered is invalid.'],
        ]);
    }
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function refreshToken(Request $request)
    {
        $current_token = $request->bearerToken();

        if (!$current_token) {
            return response()->json(['success' => false, 'errors' => ['No token provided.']]);
        }

        // Find the token record directly
        $access_token = PersonalAccessToken::findToken($current_token);

        if (!$access_token) {
            return response()->json(['success' => false, 'errors' => ['Your session has expired because you logged in on another device.']]);
        }

        $user = $access_token->tokenable;

        // Check if expired
        if ($access_token->expires_at && now()->greaterThanOrEqualTo($access_token->expires_at)) {
            $access_token->delete();

            // Generate new token
            $expiration = $request->boolean('remember_me')
                ? now()->addDays(7)
                : now()->addDays(2);

            $new_token = $user->createToken('VaahStore')->plainTextToken;
            $user->tokens()->latest()->first()->update(['expires_at' => $expiration]);

            return response()->json([
                'success' => true,
                'messages' => ['Token refreshed successfully.'],
                'data' => [
                    'api_token' => $new_token,
                    'expires_at' => $expiration->toDateTimeString(),
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'messages' => ['Token is still valid.'],
            'data' => [
                'api_token' => $current_token,
                'expires_at' => $access_token->expires_at?->toDateTimeString(),
            ]
        ]);
    }
    //-----------------------------------------------------------------------

}
