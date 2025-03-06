<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Setting;
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
        $data['charts_data_filtered_by'] = Setting::getSettingByKey('charts_filter');
        $data['vendor_img_1'] = vh_module_assets_url("Store", "img/vendor/1st-vendor.png");
        $data['vendor_img_2'] = vh_module_assets_url("Store", "img/vendor/2nd-vendor.png");
        $data['vendor_img_3'] = vh_module_assets_url("Store", "img/vendor/3rd-vendor.png");
        $data['vendor_img_4'] = vh_module_assets_url("Store", "img/vendor/4th-vendor.png");

        $response['success'] = true;
        $response['data'] = $data;
        return $response;

    }

}
