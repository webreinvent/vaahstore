<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Product;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Permission;


class ProductVariationsController extends Controller
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
            $data['permission'] = \Auth::user()->permissions(true);
            $data['rows'] = config('vaahcms.per_page');
            $data['fillable']['columns'] = ProductVariation::getFillableColumns();
            $data['fillable']['except'] = ProductVariation::getUnFillableColumns();
            $data['empty_item'] = ProductVariation::getEmptyItem();
            $data['taxonomy']['status'] = Taxonomy::getTaxonomyByType('product-variation-status');
            $data['active_products'] = $this->getActiveProducts();
            $data['empty_item']['vh_st_product_id'] = $this->getDefaultProduct();
            $data['actions'] = [];
            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return $response;
    }

    //------------------------Get Product data for dropdown----------------------------------

    public function getActiveProducts(){
        try{
            return Product::where(['is_active'=>1,'deleted_at'=>null])->get();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }

    //----------------------------------------------------------

    public function getDefaultProduct(){
        try{
            return Product::where(['is_default'=>1,'deleted_at'=>null])->get();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }

    //------------------------Get Product data for dropdown----------------------------------


    public function getList(Request $request)
    {
        try{
            return ProductVariation::getList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::updateList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::listAction($request, $type);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::deleteList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
    public function fillItem(Request $request)
    {
        try{
            return ProductVariation::fillItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::createItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::getItem($id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::updateItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::deleteItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
    public function searchProduct(Request $request)
    {
        try{
            return ProductVariation::searchProduct($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
            return ProductVariation::itemAction($request,$id,$action);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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
