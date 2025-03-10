<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Entities\User;


class ProductVendorsController extends Controller
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

            $data['permissions'] = \Auth::user()->permissions(true);
            $data['rows'] = config('vaahcms.per_page');

            $data['fillable']['columns'] = ProductVendor::getFillableColumns();
            $data['fillable']['except'] = ProductVendor::getUnFillableColumns();
            $data['empty_item'] = ProductVendor::getEmptyItem();

            $data['auth_users'] = auth()->user()->get();
            $data['taxonomy']['status'] = Taxonomy::getTaxonomyByType('product-vendor-status');

            $data['empty_item']['can_update'] = 0;
            $data['empty_item']['status'] = null;
            $data['empty_item']['is_active'] = 1;
            $data['empty_item']['is_active_product_price'] = 1;
            $data['empty_item']['can_Update'] = 0;
            $data['empty_item']['vendor'] = $this->getDefaultVendor();
            $data['empty_item']['vh_st_vendor_id'] = Vendor::where(['is_active'=>1,'deleted_at'=>null,'is_default'=>1])
                                                     ->pluck('id')->first();
            $data['empty_item']['added_by_user'] = $this->getActiveUser();
            $data['empty_item']['added_by'] = auth()->user()->id;

            $data['actions'] = [];


            $data['active_stores'] = $this->getActiveStore();



            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response;
    }
    //----------------------------------------------------------
    public function getActiveUsers(){
        $users = User::where('is_active',1)->get(['id','first_name','email']);
        if ($users){
            return $users;
        }else{
            return null;
        }
    }

    //----------------------------------------------------------
    public function getActiveUser(){
        $active_user = auth()->user();

        return [
            'id' => $active_user->id,
            'first_name' => $active_user->first_name,
            'email' => $active_user->email,
        ];
    }

    //------------------------Get Vendor data for dropdown----------------------------------
    public function getActiveVendor(){
        try{
            return Vendor::where(['is_active'=>1,'deleted_at'=>null])->get();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
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
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //------------------------Get Vendor data for dropdown----------------------------------
    public function getDefaultVendor(){
        try{
            return Vendor::where(['is_active'=>1,'deleted_at'=>null,'is_default'=>1])->first();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //------------------------Get Active Store list----------------------------------
    public function getActiveStore(){
        try{
            return Store::where(['is_active'=>1,'deleted_at'=>null])->get();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //------------------------Get Active Product Variation list----------------------------------
    public function getActiveProductVariation(){
        try{
            return ProductVariation::where(['is_active'=>1,'deleted_at'=>null])->get();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //------------------------Get Default Product Variation----------------------------------
    public function getDefaultProductVariation(){
        try{
            return ProductVariation::where(['is_active'=>1, 'deleted_at'=>null, 'is_default'=>1])
                ->first();
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //---------------------To save and update product price-------------------------------------
    public function addProductPrices(Request $request,$id)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::addProductPrices($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function getList(Request $request)
    {
        try{
            return ProductVendor::getList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function productForStore(Request $request){
        try{
            return ProductVendor::productForStore($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");

            }
            return $response;
        }
    }


    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::updateList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");

            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function listAction(Request $request, $type)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }


        try{
            return ProductVendor::listAction($request, $type);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;

        }
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::deleteList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function fillItem(Request $request)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::fillItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::createItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        try{
            return ProductVendor::getItem($id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::updateItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::deleteItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function itemAction(Request $request,$id,$action)
    {
        $permission_slug = 'can-update-module';
        if (!Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        try{
            return ProductVendor::itemAction($request,$id,$action);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function searchProduct(Request $request)
    {
        try{
            return ProductVendor::searchProduct($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function searchVendor(Request $request)
    {
        try{
            return ProductVendor::searchVendor($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }

    //------------------------------------------------------------

    public function searchAddedBy(Request $request)
    {
        try{
            return ProductVendor::searchAddedBy($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function searchStatus(Request $request)
    {
        try{
            return ProductVendor::searchStatus($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }

    //----------------------------------------------------------
    public function searchActiveStores(Request $request)
    {
        try{
            return ProductVendor::searchActiveStores($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }

    //----------------------------------------------------------

    public function getProduct(Request $request)
    {
        try{
            return ProductVendor::getProduct($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------

    public function getProductsBySlug(Request $request)
    {
        try{
            return ProductVendor::getProductsBySlug($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }

    //----------------------------------------------------------



    //----------------------------------------------------------

    public function searchVariationOfProduct(Request $request)
    {
        try{
            return ProductVendor::searchVariationOfProduct($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }

    //----------------------------------------------------------
    public function getDefaultValues(Request $request)
    {
        try{
            return ProductVendor::getdefaultValues($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //----------------------------------------------------------
}
