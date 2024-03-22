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
use VaahCms\Modules\Store\Models\ProductVariation;
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
                $response['errors'][] = 'select a crud first';
                return $response;

            }
            if(empty($quantity))
            {
                $response['success'] = false;
                $response['errors'][] = 'Kindly fill a quantity';
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
                    if($customer_role)
                    {
                        User::seedSampleItems($quantity);
                    }
                    break;
                case "CustomerGroup":
                    $user= User::all()->count();
                    if($user > 1)
                    {
                        CustomerGroup::seedSampleItems($quantity);
                        break;
                    }
                    break;
                case "Vendors":
                    $store = Store::all()->count();
                    if(!$store){
                        $response['success'] = false;
                        $response['errors'][] = 'Create a store first';
                        return $response;
                    }
                    Vendor::seedSampleItems($quantity);
                    break;
                case "Product":
                    $store = Store::all()->count();
                    if(!$store){
                        $response['success'] = false;
                        $response['errors'][] = 'Create a store first';
                        return $response;
                    }
                    Product::seedSampleItems($quantity);
                    break;
                case "ProductVariations":
                    $product = Product::all()->count();
                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = 'Create a product first';
                        return $response;
                    }
                    ProductVariation::seedSampleItems($quantity);
                    break;
                case "Warehouses":
                    $vendor = Vendor::all()->count();
                    if(!$vendor){
                        $response['success'] = false;
                        $response['errors'][] = 'Create a vendor first';
                        return $response;
                    }
                    Warehouse::seedSampleItems($quantity);
                    break;
                case "AttributeGroups":
                    $attribute = Attribute::all()->count();
                    if(!$attribute)
                    {
                        $response['success'] = false;
                        $response['errors'][] = 'Create a attribute first';
                        return $response;
                    }
                    AttributeGroup::seedSampleItems($quantity);
                    break;
                case "ProductAttribute":
                    $product = ProductVariation::all()->count();
                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = 'Create a product variations first';
                        return $response;
                    }
                    $attribute = Attribute::all()->count();
                    if(!$attribute)
                    {
                        $response['success'] = false;
                        $response['errors'][] = 'Create a attribute first';
                        return $response;
                    }
                    ProductAttribute::seedSampleItems($quantity);
                    break;
                case "ProductMedia":
                    $product = Product::all()->count();
                    if(!$product){
                        $response['success'] = false;
                        $response['errors'][] = 'Create a product first';
                        return $response;
                    }
                    ProductMedia::seedSampleItems($quantity);
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
                $response['errors'][] = 'Something went wrong.';
            }
        }


        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been Created';
        return $response;
    }

    //----------------------------------------------------------
    //----------------------------------------------------------
}
