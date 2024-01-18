<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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
        if (!Auth::user()->hasPermission('has-access-of-module-section')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }

        try{

            $data = [];

            $data['permissions'] = \Auth::user()->permissions(true);
            $data['rows'] = config('vaahcms.per_page');

            $data['fillable']['columns'] = ProductMedia::getFillableColumns();
            $data['fillable']['except'] = ProductMedia::getUnFillableColumns();
            $data['empty_item'] = ProductMedia::getEmptyItem();

            $data['empty_item']['base_path'] = url('');
            $data['empty_item']['images'] = [];
            $data['empty_item']['product'] = null;
            $data['empty_item']['product_variation'] = null;
            $data['empty_item']['status'] = null;
            $data['actions'] = [];
            $data['empty_item']['is_active'] = 0;

            $active_products = $this->getActiveProducts();
            $active_product_variations = $this->getActiveProductVariations();

            $data = array_merge($data, $active_products, $active_product_variations);

            $data['status'] = Taxonomy::getTaxonomyByType('product-medias-status');

            $data['empty_item']['vh_st_product_id'] = $this->getDefaultProduct();
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::updateList($request);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }


        try{
            return ProductMedia::listAction($request, $type);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::deleteList($request);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::fillItem($request);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::createItem($request);
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
            return ProductMedia::getItem($id);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::updateItem($request,$id);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::deleteItem($request,$id);
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
        if (!Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        }
        try{
            return ProductMedia::itemAction($request,$id,$action);
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
            return ProductMedia::searchProduct($request);
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
    public function searchProductVariation(Request $request)
    {
        try{
            return ProductMedia::searchProductVariation($request);
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
    public function searchStatus(Request $request)
    {
        try{
            return ProductMedia::searchStatus($request);
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
    public function searchVariation(Request $request)
    {
        try{
            return ProductMedia::searchVariation($request);
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

    public function variationForProduct(Request $request){
        try{
            $inputs = $request->all();
            $response = [];
            $data = ProductVariation::where('is_active', 1)->whereIn( 'vh_st_product_id', $inputs)

                ->orderBy('vh_st_product_id')
                ->orderBy('id')
                ->get(['id','name','slug']);
            $response['success'] = true;
            $response['data']= $data;
            return $response;
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
    public function searchVariationsUsingUrlSlug(Request $request)
    {
        try{
            return ProductMedia::searchVariationsUsingUrlSlug($request);
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
    public function searchStatusUsingUrlSlug(Request $request)
    {
        try{
            return ProductMedia::searchStatusUsingUrlSlug($request);
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


}
