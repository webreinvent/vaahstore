<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductVariation;
use WebReinvent\VaahCms\Entities\Taxonomy;


class ProductMediasController extends Controller
{


    //----------------------------------------------------------
    public function __construct()
    {

    }

    //----------------------------------------------------------

    public function getAssets(Request $request)
    {

        try{

            $data = [];

            $data['permission'] = [];
            $data['rows'] = config('vaahcms.per_page');

            $data['fillable']['except'] = [
                'uuid',
                'created_by',
                'updated_by',
                'deleted_by',
            ];

            $model = new ProductMedia();
            $fillable = $model->getFillable();
            $data['fillable']['columns'] = array_diff(
                $fillable, $data['fillable']['except']
            );

            foreach ($fillable as $column)
            {
                $data['empty_item'][$column] = null;
            }

            $data['empty_item']['base_path'] = url('');
            $data['empty_item']['images'] = [];
            $data['empty_item']['product'] = null;
            $data['empty_item']['product_variation'] = null;
            $data['empty_item']['status'] = null;
            $data['actions'] = [];
            $data['empty_item']['is_active'] = 1;

            $active_products = $this->getActiveProducts();
            $active_product_variations = $this->getActiveProductVariations();

            $data = array_merge($data, $active_products, $active_product_variations);

            $data['status'] = Taxonomy::getTaxonomyByType('product-medias-status');

            $data['empty_item']['vh_st_product_id'] = $this->getDefaultProduct();
            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
        }
        return $response;
    }

    //----------------------------------------------------------
    public function getDefaultProduct(){
        return Product::where(['is_active' => 1, 'is_default' => 1])->get(['id','name', 'slug', 'is_default'])->first();
    }

    //----------------------------------------------------------
    public function getActiveProducts(){
        $active_products = Product::where('is_active', 1)->get(['id','name','slug','is_default']);
        if ($active_products){
            return [
                'active_products' =>$active_products
            ];
        }else{
            return [
                'active_products' => null
            ];
        }
    }

    //----------------------------------------------------------
    public function getActiveProductVariations(){
        $active_product_variations = ProductVariation::Where(['is_active'=>1,'deleted_at'=>null])->get(['id','name','slug','deleted_at','is_active']);
        if ($active_product_variations){
            return [
                'active_product_variations' =>$active_product_variations
            ];
        }else{
            return [
                'active_product_variations' => null
            ];
        }
    }

    //----------------------------------------------------------
    public function uploadImage(Request $request){
        try{

            $inputs = $request->all();



            $response_list = [];

            foreach ($inputs['images'] as $file){
                $list = [];
                $list['file'] = $file;
                $list['folder_path'] = 'public/media';

                $response = ProductMedia::saveUploadImage(new Request($list));
                if ($response['status']){
                    $response_list[] = $response['data'];
                }else{
                    $response_list[] = $response;
                }

            }

            return [
                'status' => $response['status'],
                'data'  => $response_list
            ];

//            return ProductMedia::saveUploadImage($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }

    //----------------------------------------------------------
    public function getList(Request $request)
    {
        try{
            return ProductMedia::getList($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        try{
            return ProductMedia::updateList($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';

            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function listAction(Request $request, $type)
    {


        try{
            return ProductMedia::listAction($request, $type);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;

        }
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        try{
            return ProductMedia::deleteList($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        try{
            return ProductMedia::createItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        try{
            return ProductMedia::getItem($id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        try{
            return ProductMedia::updateItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        try{
            return ProductMedia::deleteItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function itemAction(Request $request,$id,$action)
    {
        try{
            return ProductMedia::itemAction($request,$id,$action);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
            return $response;
        }
    }
    //----------------------------------------------------------


}
