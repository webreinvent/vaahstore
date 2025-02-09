<?php

namespace VaahCms\Modules\Store\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            $status = $response['success'] ? 200 : ($response['status'] ?? 422);
            return response()->json($response, $status);
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

            $status = $response['success'] ? 200 : ($response['status'] ?? 422);

            return response()->json($response, $status);

        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], $e->getStatusCode());

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

            $status = $response['success'] ? 200 : ($response['status'] ?? 422);

            return response()->json($response, $status);

        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], $e->getStatusCode());

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




    //------------------------------------------------

    public function authSignOut(Request $request)
    {
        try {
            if ($user = Auth::guard('api')->user()) {
                $user->currentAccessToken()->delete();
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
                return response()->json($response, 404);
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

                $max_sessions = 5;
                if ($user->tokens()->count() >= $max_sessions) {
                    $user->tokens()->oldest()->first()->delete();
                }

                $expiration = Carbon::now()->addHours(24);

                $token = $user->createToken('VaahStore')->plainTextToken;

                $user->tokens()->latest()->first()->update(['expires_at' => $expiration]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'item' => array_merge($user->toArray(), [
                            'api_token' => $token,
                            'expires_at' => $expiration->toDateTimeString(),
                        ]),
                    ],
                ],201);
            }
            return response()->json($response,422);
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
                ], 422);
            }

            $user = self::findUser($request);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Invalid credentials'],
                ], 401);
            }

            if ($request->authentication_type === 'otp') {
                return self::handleOtpLogin($user, $request);
            }

            return self::handleStandardLogin($user, $request);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [env('APP_DEBUG') ? $e->getMessage() : trans('vaahcms-general.something_went_wrong')],
                'hint' => env('APP_DEBUG') ? $e->getTrace() : null,
            ]);
        }
    }

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

            'identifier_value.phone' => 'The phone number format is invalid.',
            'identifier_value.numeric' => 'The phone number must be a valid numeric value.',
        ];

        return \Validator::make($request->all(), $rules, $messages);
    }

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

    protected static function handleStandardLogin($user, $request)
    {
        if (Hash::check($request->authentication_value, $user->password)) {
            return self::generateAuthResponse($user, $request);
        }

        return response()->json([
            'success' => false,
            'errors' => ['The password you entered is incorrect.'],
        ], 401);
    }

    protected static function generateAuthResponse($user, $request)
    {
        $max_sessions = 5;
        if ($user->tokens()->count() >= $max_sessions) {
            $user->tokens()->oldest()->first()->delete();
        }

        $expiration = Carbon::now()->addDays(2);

        $token = $user->createToken('VaahStore')->plainTextToken;
        $user->tokens()->latest()->first()->update(['expires_at' => $expiration]);

        $user->makeVisible('api_token');

        return response()->json([
            'success' => true,
            'data' => [
                'item' => array_merge($user->toArray(), [
                    'api_token' => $token,
                    'expires_at' => $expiration->toDateTimeString(),
                ]),
            ],
        ]);
    }

    protected static function handleOtpLogin($user, $request)
    {
        if (Hash::check(trim($request->authentication_value), $user->login_otp)) {
            Auth::login($user, $request->boolean('remember'));

            $max_sessions = 5;
            if ($user->tokens()->count() >= $max_sessions) {
                $user->tokens()->oldest()->first()->delete();
            }

            $expiration = Carbon::now()->addHours(24);

            $token = $user->createToken('VaahStore')->plainTextToken;

            $user->tokens()->latest()->first()->update(['expires_at' => $expiration]);

            $user->update([
                'login_otp' => null,
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'item' => array_merge($user->toArray(), [
                        'api_token' => $token,
                        'expires_at' => $expiration->toDateTimeString(),
                    ]),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'errors' => ['The OTP you entered is invalid.'],
        ], 401);
    }


}
