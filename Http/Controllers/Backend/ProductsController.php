<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\Vendor;


class ProductsController extends Controller
{


    //----------------------------------------------------------
    public function __construct()
    {

    }

    //----------------------------------------------------------

    public function getAssets(Request $request)
    {

        $data = [];

        $data['permission'] = [];

        $data['fillable']['except'] = [
            'uuid',
            'vh_st_store_id',
            'vh_st_brand_id',
            'taxonomy_id_product_type',
            'vh_cms_content_form_field_id',
            'meta',
            'created_by',
            'updated_by',
            'deleted_by',
        ];

        $model = new Product();
        $fillable = $model->getFillable();
        $data['fillable']['columns'] = array_diff(
            $fillable, $data['fillable']['except']
        );

        foreach ($fillable as $column)
        {
            $data['empty_item'][$column] = null;
        }

        $data['stores'] = Store::active()->get();
        $data['default_store'] = Store::isDefault()->first();
        /*$data['default_vendor'] = Vendor::isDefault($data['default_store'])->first();
        $data['vendors'] = Vendor::active($data['default_store'])->take(10)
            ->orderBy('created_at', 'desc')
            ->get();*/

        $data['brands'] = Brand::active()->take(10)
            ->orderBy('created_at', 'desc')
            ->get();

        $data['actions'] = [];

        $response['success'] = true;
        $response['data'] = $data;

        return $response;
    }

    //----------------------------------------------------------
    public function getList(Request $request)
    {
        return Product::getList($request);
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        return Product::updateList($request);
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        return Product::deleteList($request);
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        return Product::createItem($request);
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        return Product::getItem($id);
    }

    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        return Product::updateItem($request,$id);
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        return Product::deleteItem($request,$id);
    }
    //----------------------------------------------------------
    //----------------------------------------------------------


}
