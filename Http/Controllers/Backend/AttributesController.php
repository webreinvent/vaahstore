<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\ProductVariation;
use WebReinvent\VaahCms\Models\Permission;

class AttributesController extends Controller
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
            /*$data['active_permissions'] = Permission::getActiveItems();*/
            Permission::syncPermissionsWithRoles();
            $data['rows'] = config('vaahcms.per_page');

            $data['fillable']['columns'] = Attribute::getFillableColumns();
            $data['fillable']['except'] = Attribute::getUnFillableColumns();
            $data['empty_item'] = Attribute::getEmptyItem();
            $data['empty_item']['is_active'] = 1;
            $data['empty_item']['value'] = [];
            $get_product_variation_data = self::getProductVariationData();
            $data = array_merge($data, $get_product_variation_data);
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

    //------------------------Get Product Variation data for dropdown----------------------------------

    public function getProductVariationData(){
        try{
            $data['product_variations'] = ProductVariation::where(['is_active'=>1,'deleted_at'=>null])->get();
            return $data;
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


    public function getList(Request $request)
    {

        try{
            return Attribute::getList($request);
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
            return Attribute::updateList($request);
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
            return Attribute::listAction($request, $type);
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
            return Attribute::deleteList($request);
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
            return Attribute::fillItem($request);
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
            return Attribute::createItem($request);
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
            return Attribute::getItem($id);
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
            return Attribute::updateItem($request,$id);
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
            return Attribute::deleteItem($request,$id);
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
            return Attribute::itemAction($request,$id,$action);
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
