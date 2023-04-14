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

            $data['empty_item']['base_path'] = url('storage/media');
            $data['empty_item']['images'] = [];
            $data['actions'] = [];
            $data['empty_item']['is_active'] = 1;
            $data['product']=Product::select('id','name','slug','is_default','deleted_at','is_active')->where(['is_active'=>1,'deleted_at'=>null])->paginate(config('vaahcms.per_page'));
            $data['product_variation']=ProductVariation::select('id','name','slug','deleted_at','is_active')->where(['is_active'=>1,'deleted_at'=>null])->paginate(config('vaahcms.per_page'));
            $data['status'] = Taxonomy::getTaxonomyByType('product-medias-status');
            $default_product = [];
            foreach($data['product'] as $l=>$product)
            {
                if($product['is_default']==1)
                {
                    $default_product['id'] = $product->id;
                    $default_product['name'] = $product->name;
                    $default_product['is_default'] = $product->is_default;
                }
            }
            $data['empty_item']['vh_st_product_id'] = $default_product;
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

                    $new_array = [];
                    $new_array['image_size'] = $response['data']['image_size'];
                    $new_array['thumbnail_name'] = $response['data']['thumbnail_name'];
                    $new_array['image_name'] = $response['data']['image_name'];
                    $new_array['thumbnail_size'] = $response['data']['thumbnail_size'];
                    $response_list[] = $new_array;

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
