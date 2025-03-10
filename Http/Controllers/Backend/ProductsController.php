<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use VaahCms\Modules\Store\Models\Category;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeGroup;
use VaahCms\Modules\Store\Models\AttributeValue;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Permission;
class ProductsController extends Controller
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
            $data['active_permissions'] = Permission::getActiveItems();
            Permission::syncPermissionsWithRoles();
            $data['rows'] = config('vaahcms.per_page');
            $data['fillable']['columns'] = Product::getFillableColumns();
            $data['fillable']['except'] = Product::getUnFillableColumns();
            $data['empty_item'] = Product::getEmptyItem();
            $data['taxonomy']['status'] = Taxonomy::getTaxonomyByType('product-status');
            $data['taxonomy']['types'] = Taxonomy::getTaxonomyByType('product-types');
            $data['empty_item']['in_stock'] = 0;
            $data['empty_item']['quantity'] = 0;
            $data['empty_item']['is_active'] = 1;
            $data['empty_item']['all_variation'] = [];
            $data['empty_item']['vendors'] = [];
            $data['empty_item']['type'] = null;
            $data['empty_item']['status'] = null;
            $data['empty_item']['quantity'] = 0;
            $active_stores = $this->getStores();
            $active_brands = $this->getBrands();
            $active_vendors = $this->getVendors();
            $data = array_merge($data, $active_stores, $active_brands, $active_vendors);

            // set default values of Store if it is not null


            // get min and max quantity from the product filter
            $product = Product::withTrashed()->get();
            if($product->isNotEmpty())
            {
                $quantities = $product->pluck('quantity')->toArray();
                $data['min_quantity'] = min($quantities);
                $data['max_quantity'] = max($quantities);
            }

            $data['taxonomy'] = [
                "product_status" => Taxonomy::getTaxonomyByType('product-status'),
                "types" => Taxonomy::getTaxonomyByType('product-types'),
                "product_vendor_status" => Taxonomy::getTaxonomyByType('vendor-status'),
            ];
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
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response;
    }

    //------------------------------------------------------------------------------------

    //------------------------Get Brand data for dropdown----------------------------------
    public function getBrandData(){
        try{
            $data['brands']=Brand::where('is_active',1)->get();
            return $data;
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

    //------------------------Get Store data for dropdown----------------------------------
    public function getStoreData(){
        try{
            $data['stores']=Store::where('is_active',1)->get();
            return $data;
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
    public function getStores(){
        $stores = Store::where('is_active',1)->get(['id','name', 'slug', 'is_default']);
        if ($stores){
            return [
                'active_stores' =>$stores
            ];
        }else{
            return [
                'active_stores' => null
            ];
        }
    }

    //----------------------------------------------------------
    public function getBrands(){
        $brands = Brand::where('is_active',1)->get(['id','name', 'slug', 'is_default']);
        if ($brands){
            return [
                'active_brands' =>$brands
            ];
        }else{
            return [
                'active_brands' => null
            ];
        }
    }

    //----------------------------------------------------------
    public function getVendors(){
        $vendors = Vendor::with('status')->where('is_active', 1)
            ->get(['id','name','slug','is_default']);
        if ($vendors){
            return [
                'active_vendors' =>$vendors
            ];
        }else{
            return [
                'active_vendors' => null
            ];
        }
    }

    //----------------------------------------------------------
    public function attachVendors(Request $request, $id){
        try{
            return Product::attachVendors($request,$id);
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

    public function removeVendor(Request $request,$id)
    {
        try{
            return Product::removeVendor($request,$id);
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

    public function bulkRemoveVendor(Request $request,$id)
    {
        try{
            return Product::bulkRemoveVendor($request,$id);
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

    public function getAttributeList(Request $request){
        $input = $request->all();
        switch ($input['attribute_type']){
            case 'attribute':
                $item = Attribute::get(['name', 'id', 'type']);
                break;
            case 'attribute_group':
                if ($input['get_attribute_from_group']){
                    $record = AttributeGroup::where('id', $input['selected_attribute']['id'])->with(['attributesList'])->get();
                    if ($record) {
                        $item = $record->toArray()[0]['attributes_list'];

                    }
                }else{
                    $item = AttributeGroup::get(['name', 'id']);

                }
                break;
        }

        return [
            'data' => $item
        ];
    }

    //----------------------------------------------------------
    public function getVendorsList(){
        try{
            $data = [];

            $result = Vendor::where('is_active', 1)->get(['id','name','slug','is_default']);

            $data['vendors_list'] = $result;
            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
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
    public function getAttributeValue(Request $request){
        $input = $request->all();

        $result = [];

        foreach ($input['attribute'] as $key=>$value){

            $att = Attribute::find($value['id']);

            $item = AttributeValue::where('vh_st_attribute_id', $value['id'])->get(['id', 'vh_st_attribute_id', 'value']);

            if ($item){
                $record = $item->toArray();

                foreach ($record as $k=>$v){
                    $result[$value['name']][$k] = $v;
                }
            }

        }

        $product_detail = Product::find($input['product_id']);

        if ($input['method'] == 'generate'){
            $combination = [];

            // making all possible combination of attribute values
            foreach ($result as $k => $v){
                $temp_result = [];
                if(count($combination) == 0){
                    foreach ($v as $key => $value){
                        $temp = [];
                        $temp[$k]['value'] = $value['value'];
                        $temp[$k]['vh_st_attribute_id'] = $value['vh_st_attribute_id'];
                        $temp[$k]['id'] = $value['id'];
                        array_push($temp_result, $temp);
                    }
                    $combination = $temp_result;
                }else{
                    foreach ($v as $key => $value){
                        foreach ($combination as $n_key => $n_value){
                            $temp = [];
                            $temp[$k]['value'] = $value['value'];
                            $temp[$k]['vh_st_attribute_id'] = $value['vh_st_attribute_id'];
                            $temp[$k]['id'] = $value['id'];
                            foreach ($n_value as $c_key => $c_value){
                                $temp[$c_key] = $c_value;
                            }

                            array_push($temp_result, $temp);
                        }
                    }
                    $combination = $temp_result;
                }
            }

            $structured_variation = [];
            $all_attribute_name = [];
            // adding additional structure for variation table
            foreach ($combination as $k => $v){
                if ($k == 0){
                    $all_attribute_name = array_keys($v);
                }
                //      $v['variation_name'] = 'variation name '.$k;
                $value_name = [];
                foreach ($all_attribute_name as $key=>$value){
                    array_push($value_name, $v[$value]['value']);
                }

                $v['variation_name'] = $product_detail->name.'-'.implode('/', $value_name);
                $v['is_selected'] = false;
                array_push($structured_variation, $v);
            }

            return [
                'data' => [
                    'structured_variations' => $structured_variation,
                    'all_attribute_names' => $all_attribute_name
                ]
            ];
        } else{
            return [
                'data' => [
                    'attribute_names' => array_keys($result),
                    'attribute_values' => $result
                ]
            ];
        }
    }

    //----------------------------------------------------------

    public function getList(Request $request)
    {
        try{
            return Product::getList($request);
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
        try{
            return Product::updateList($request);
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

        try{
            return Product::listAction($request, $type);
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
        try{
            return Product::deleteList($request);
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
        try{
            return Product::fillItem($request);
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
        try{
            return Product::createItem($request);
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

    public function generateVariation(Request $request,$id)
    {
        try{
            return Product::generateVariation($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
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

    public function searchStore(Request $request)
    {
        try {

            return Product::searchStore($request);
        }
        catch (\Exception $e){
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

    public function searchBrand(Request $request)
    {
        try {

            return Product::searchBrand($request);
        }
        catch (\Exception $e){
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
            return Product::getItem($id);
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
        try{
            return Product::updateItem($request,$id);
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
        try{
            return Product::deleteItem($request,$id);
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
        try{
            return Product::itemAction($request,$id,$action);
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

    public function searchProductVariation(Request $request)
    {
        try {

            return Product::searchProductVariation($request);
        }
        catch (\Exception $e){
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

    public function searchProductVendor(Request $request)
    {

        try {

            return Product::searchProductVendor($request);
        }
        catch (\Exception $e){
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
    public function searchVendorUsingUrlSlug(Request $request)
    {
        try{
            return Product::searchVendorUsingUrlSlug($request);
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
    public function searchBrandUsingUrlSlug(Request $request)
    {
        try{
            return Product::searchBrandUsingUrlSlug($request);
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
    public function searchVariationUsingUrlSlug(Request $request)
    {

        try{
            return Product::searchVariationUsingUrlSlug($request);
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
    public function searchStoreUsingUrlSlug(Request $request)
    {

        try{
            return Product::searchStoreUsingUrlSlug($request);
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

    public function searchProductTypeUsingUrlSlug(Request $request)
    {

        try{
            return Product::searchProductTypeUsingUrlSlug($request);
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
    public function searchVendor(Request $request)
    {

        try {

            return Product::searchVendor($request);
        }
        catch (\Exception $e){
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

    public function defaultStore(Request $request)
    {
        try{
            return Product::defaultStore($request);
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

    public function searchUsers(Request $request)
    {
        try{
            return Product::searchUsers($request);
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

    public function addProductToCart(Request $request)
    {
        try{
            return Product::addProductToCart($request);
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

    public function generateCart(Request $request)
    {
        try{
            return Product::generateCart($request);
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

    public function deleteCategory(Request $request)
    {
        try{
            return Product::deleteCategory($request);
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

    public function getVendorsListForPrduct(Request $request, $id)
    {
        try{
            return Product::getVendorsListForPrduct($id);
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

    public function disableActiveCart(Request $request)
    {

        try{
            Session::forget('vh_user_id');
            return [
                'success' => true,
                'message' => trans("vaahcms-general.cart_disabled_successfully")
            ];
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

    public function searchCategoryUsingSlug(Request $request)
    {

        try{
            return Product::searchCategoryUsingSlug($request);
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

    public function vendorPreferredAction(Request $request, $id, $vendor_id)
    {

        try{
            return Product::vendorPreferredAction($request, $id, $vendor_id);
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
    public function getCategories(Request $request){
        try{

            $data = [];

            $categories=Category::with('subCategories')
                ->whereNull('parent_id')->where('is_active', 1)->get();
            $data['categories'] = $categories;
            $response['success'] = true;
            $response['data'] = $data;
            return $response;
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

    public function topSellingProducts(Request $request)
    {
        try{
            return Product::topSellingProducts ($request);
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

    public function topSellingBrands(Request $request)
    {
        try{
            return Product::topSellingBrands ($request);
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

    public function topSellingCategories(Request $request)
    {
        try{
            return Product::topSellingCategories ($request);
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

    public function exportData(Request $request)
    {
        try {
            return Product::exportData($request);
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }
    }

}
