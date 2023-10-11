<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use WebReinvent\VaahCms\Entities\Taxonomy;

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
            'version' => config('store.version'),
            'is_dev' => config('store.is_dev'),
        ];

        $data['timezone'] = env("APP_TIMEZONE");
        $data['server_date_time'] = \Carbon::now();

        $data['product_types'] = Taxonomy::getTaxonomyByType('product-types');
        $data['urls']['brands'] = route('vh.backend.store.brands.list');


        $response['success'] = true;
        $response['data'] = $data;
        return $response;

    }

}
