<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VaahCms\Modules\Store\Models\Address;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeGroup;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\CustomerGroup;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductAttribute;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductStock;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\User;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\Warehouse;
use VaahCms\Modules\Store\Models\Wishlist;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\Setting;
use WebReinvent\VaahExtend\Libraries\VaahArtisan;

class SettingsController extends Controller
{
    //----------------------------------------------------------
    public function __construct()
    {
    }
    //----------------------------------------------------------
    public function getAssets(Request $request): JsonResponse
    {
        /*if (!Auth::user()->hasPermission('has-access-of-settings-section')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return response()->json($response);
        } */

        try {
            $data = [];

            $response['success'] = true;
            $response['data'] = $data;
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return response()->json($response);
    }
    //----------------------------------------------------------
    public function getList(Request $request): JsonResponse
    {

        try {
            $list = Setting::where('category', 'global')
                ->get()
                ->pluck('value', 'key' )
                ->toArray();

            $response['success'] = true;
            $response['data'] = $list;

        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = 'Something went wrong.';
            }
        }
        return response()->json($response);
    }
    //----------------------------------------------------------
    public function updateList(Request $request): JsonResponse
    {

        try {
            foreach ($request->list as $key => $value){
                $setting = Setting::query()
                    ->where('category', 'global')
                    ->where('key', $key)
                    ->first();

                if (!$setting) {
                    $setting = new Setting();
                    $setting->key = $key;
                    $setting->value = $value;
                    $setting->category = 'global';
                    $setting->save();
                } else {
                    Setting::query()
                        ->where('category', 'global')
                        ->where('key', $key)
                        ->update(['value' => $value]);
                }
            }

            VaahArtisan::clearCache();

            $response['success'] = true;
            $response['data'][] = '';
            $response['messages'][] = 'Settings successful saved';
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return response()->json($response);
    }
    //----------------------------------------------------------


    public function bulkCreateRecords(Request $request)
    {
        $response = [];

        try {
            $data = $request->params;

            $crud = $data['crud'];

            $quantity = $data['quantity'];

            if(empty($crud))
            {
                $response['success'] = false;
                $response['errors'][] = trans("vaahcms-general.select_a_crud");
                return $response;

            }
            if(empty($quantity))
            {
                $response['success'] = false;
                $response['errors'][] = trans("vaahcms-general.fill_quantity");
                return $response;

            }

            switch ($crud) {
                case "Store":
                    Store::seedSampleItems($quantity);
                    break;
                case "Address":
                    Address::seedSampleItems($quantity);
                    break;
                case "Wishlists":
                    Wishlist::seedSampleItems($quantity);
                    break;
                case "Brand":
                    Brand::seedSampleItems($quantity);
                    break;
                case "Attributes":
                    Attribute::seedSampleItems($quantity);
                    break;
                case "Customer":
                    $customer_role = Role::where('slug', 'customer')->first();
                    if(!$customer_role)
                    {
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_customer_role");
                        return $response;
                    }
                    User::seedSampleItems($quantity);
                    break;
                case "CustomerGroup":
                    $user= User::all()->count();
                    if(!$user)
                    {
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_customer");
                        return $response;
                    }
                    CustomerGroup::seedSampleItems($quantity);
                    break;
                case "Vendors":
                    $store = Store::exists();
                    if(!$store){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_store");
                        return $response;
                    }
                    Vendor::seedSampleItems($quantity);
                    break;
                case "Product":
                    $store = Store::exists();
                    if(!$store){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_store");
                        return $response;
                    }
                    Product::seedSampleItems($quantity);
                    break;
                case "ProductVariations":

                    $product = Product::exists();

                    $store = Store::exists();

                    if(!$product && !$store)
                    {
                        $response['success'] = false;
                        $response['errors'][] = "create store and product respectively";
                        return $response;
                    }

                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_product");
                        return $response;
                    }
                    ProductVariation::seedSampleItems($quantity);
                    break;
                case "Warehouses":
                    $vendor = Vendor::exists();

                    $store = Store::exists();

                    if(!$vendor && !$store)
                    {
                        $response['success'] = false;
                        $response['errors'][] = "create store and vendor respectively";
                        return $response;
                    }

                    if(!$vendor){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_vendor");
                        return $response;
                    }
                    Warehouse::seedSampleItems($quantity);
                    break;
                case "AttributeGroups":
                    $attribute = Attribute::all()->count();
                    if(!$attribute)
                    {
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_attribute");
                        return $response;
                    }
                    AttributeGroup::seedSampleItems($quantity);
                    break;
                case "ProductAttribute":

                    $store = Store::exists();

                    $product = Product::exists();

                    $product_variation = ProductVariation::exists();

                    $attribute = Attribute::exists();

                    if(!$product && !$store && !$product_variation && !$attribute)
                    {
                        $response['success'] = false;
                        $response['errors'][] = "create store , attribute , product and product variation respectively";
                        return $response;
                    }

                    if(!$attribute && !$product && !$product_variation)
                    {
                        $response['success'] = false;
                        $response['errors'][] = "create attribute , product and product variation respectively";
                        return $response;
                    }

                    if(!$attribute && !$product_variation)
                    {
                        $response['success'] = false;
                        $response['errors'][] = "create attribute and product variation respectively";
                        return $response;
                    }

                    if(!$product_variation){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_variation");
                        return $response;
                    }
                    $attribute = Attribute::all()->count();
                    if(!$attribute)
                    {
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_attribute");
                        return $response;
                    }
                    ProductAttribute::seedSampleItems($quantity);
                    break;
                case "ProductMedia":
                    $product = Product::exists();

                    $store = Store::exists();

                    if(!$product && !$store)
                    {
                        $response['success'] = false;
                        $response['errors'][] = "create store and product respectively";
                        return $response;
                    }

                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_product");
                        return $response;
                    }
                    ProductMedia::seedSampleItems($quantity);
                    break;
                case "ProductStock":
                    $vendor = Vendor::all()->count();
                    if(!$vendor){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_vendor");
                        return $response;
                    }
                    $product = Product::all()->count();
                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_product");
                        return $response;
                    }
                    $product_variation = ProductVariation::all()->count();
                    if(!$product_variation){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_variation");
                        return $response;
                    }
                    $warehouse = Warehouse::all()->count();
                    if(!$warehouse){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_warehouse");
                        return $response;
                    }
                    ProductStock::seedSampleItems($quantity);
                    break;
                case "VendorsProduct":
                    $product = Product::exists();

                    $store = Store::exists();

                    $vendor = Vendor::exists();

                    if(!$product && !$store && !$vendor) {

                        $response['success'] = false;
                        $response['errors'][] = "create a store , vendor and product respectively";
                        return $response;
                    }

                    if(!$product && !$vendor) {

                        $response['success'] = false;
                        $response['errors'][] = "create a vendor and product respectively";
                        return $response;
                    }
                    if(!$store && !$vendor) {

                        $response['success'] = false;
                        $response['errors'][] = "create a store , vendor respectively";
                        return $response;
                    }
                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_product");
                        return $response;
                    }
                    if(!$store){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_store");
                        return $response;
                    }
                    if(!$vendor){
                        $response['success'] = false;
                        $response['errors'][] = trans("vaahcms-general.create_vendor");
                        return $response;
                    }
                    ProductVendor::seedSampleItems($quantity);
                    break;
                case "All":

                    Store::seedSampleItems($quantity);

                    Wishlist::seedSampleItems($quantity);

                    Address::seedSampleItems($quantity);

                    Brand::seedSampleItems($quantity);

                    Attribute::seedSampleItems($quantity);

                    AttributeGroup::seedSampleItems($quantity);

                    User::seedSampleItems($quantity);

                    CustomerGroup::seedSampleItems($quantity);

                    Product::seedSampleItems($quantity);

                    ProductVariation::seedSampleItems($quantity);

                    Vendor::seedSampleItems($quantity);

                    ProductAttribute::seedSampleItems($quantity);

                    ProductMedia::seedSampleItems($quantity);

                    ProductVendor::seedSampleItems($quantity);

                    Warehouse::seedSampleItems($quantity);

                    ProductStock::seedSampleItems($quantity);

                    break;

                default:
                    break;
            }

        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }


        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_created");
        return $response;
    }

    //----------------------------------------------------------
    //----------------------------------------------------------
}
