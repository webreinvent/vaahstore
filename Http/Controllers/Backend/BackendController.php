<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BackendController extends Controller
{


    public function __construct()
    {

    }

    public function index()
    {
        //return view('store::backend.pages.index');
        return view('store::backend.pages.app');
    }

    public function getAssets(Request $request)
    {
        $data=[];

        $data['module'] = [
            'name' => config('store.name'),
            'version' => config('store.version')
        ];

        $response['success'] = true;
        $response['data'] = $data;
        return $response;

    }

}
