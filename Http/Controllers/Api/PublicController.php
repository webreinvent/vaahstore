<?php

namespace VaahCms\Modules\Store\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use WebReinvent\VaahCms\Models\User;

class PublicController  extends Controller
{
    public function __construct()
    {
    }
    //------------------------------------------------

    public function postGenerateOTP(Request $request)
    {
        $response = User::sendLoginOtp($request, 'can-login-in-backend');
        return response()->json($response);

    }
    //------------------------------------------------

    public function postSendResetCode(Request $request)
    {
        $response = User::sendResetPasswordEmail($request, 'can-login-in-backend');

        return response()->json($response);
    }
}
