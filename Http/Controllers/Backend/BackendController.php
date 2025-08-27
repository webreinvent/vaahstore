<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Setting;
use VaahCms\Modules\Store\Models\Store;
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
        $upload_allowed_file_size = config('settings.global.upload_allowed_file_size',5);
        $data['max_file_size'] = $upload_allowed_file_size* (1024 * 1024);
        $data['timezone'] = env("APP_TIMEZONE");
        $data['server_date_time'] = \Carbon::now();

        $data['product_types'] = Taxonomy::getTaxonomyByType('product-types');
        $data['urls']['brands'] = route('vh.backend.store.brands.list');
        $approved_status = Taxonomy::getTaxonomyByType('store-status')
            ->where('slug', 'approved')
            ->first();

        if (!$approved_status) {
            $data['stores']=null;
        }

        $data['stores'] = Store::with('defaultCurrency')
            ->where([
                ['is_active', 1],
                ['taxonomy_id_store_status', $approved_status->id],
            ])
            ->get();

        $data['default_store'] = Store::with('defaultCurrency')
        ->firstWhere([
            ['is_active', 1],
            ['is_default', 1],
        ]);
        $data['charts_data_filtered_by'] = Setting::getSettingByKey('charts_filter');
        $data['vendor_images'] = [
            vh_module_assets_url("Store", "img/vendor/1st-vendor.png"),
            vh_module_assets_url("Store", "img/vendor/2nd-vendor.png"),
            vh_module_assets_url("Store", "img/vendor/3rd-vendor.png"),
            vh_module_assets_url("Store", "img/vendor/4th-vendor.png"),
        ];
        $data['category_images'] = [
            vh_module_assets_url("Store", "img/category/1st-category.png"),
            vh_module_assets_url("Store", "img/category/2nd-category.png"),
            vh_module_assets_url("Store", "img/category/3rd-category.png"),
            vh_module_assets_url("Store", "img/category/4th-category.png"),
        ];

        $response['success'] = true;
        $response['data'] = $data;
        return $response;

    }

}
