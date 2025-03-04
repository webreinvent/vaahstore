<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VaahCms\Modules\Store\Models\Address;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeGroup;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Cart;
use VaahCms\Modules\Store\Models\Category;
use VaahCms\Modules\Store\Models\CustomerGroup;
use VaahCms\Modules\Store\Models\Order;
use VaahCms\Modules\Store\Models\Payment;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductAttribute;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductStock;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\Shipment;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\User;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\Setting;
use VaahCms\Modules\Store\Models\Warehouse;
use VaahCms\Modules\Store\Models\Wishlist;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahExtend\Libraries\VaahArtisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $list = Setting::pluck('value', 'key')->toArray();

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


    public function createBulkRecords(Request $request)
    {
        $response = [];

        try {


            $data = $request->all();

            if(empty($data['selectedCrud']))
            {
                $response['success'] = false;
                $response['errors'][] =  trans("vaahcms-vaahstore-crud-action.select_crud");
                return $response;

            }

            foreach ($data['selectedCrud'] as $item) {

                $crud = $item['value'];

                $quantity = $item['quantity'];

                $is_check = $item ['is_checked'];

                switch ($crud) {
                    case "Store":
                        if($is_check === false)
                        {
                            $response['success'] = false;
                            $response['errors'][] =trans("vaahcms-vaahstore-crud-action.quantity_required");
                            return $response;
                        }
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
                        if (!$customer_role) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_customer_role");
                            return $response;
                        }
                        User::seedSampleItems($quantity);
                        break;
                    case "CustomerGroup":
                        $user = User::all()->count();
                        if ($user < 2 ) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_customer");
                            return $response;
                        }
                        CustomerGroup::seedSampleItems($quantity);
                        break;
                    case "Vendors":
                        $store = Store::exists();
                        if (!$store) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store");
                            return $response;
                        }
                        Vendor::seedSampleItems($quantity);
                        break;
                    case "Product":
                        $store = Store::exists();
                        if (!$store) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store");
                            return $response;
                        }
                        Product::seedSampleItems($quantity);
                        break;
                    case "ProductVariations":

                        $product = Product::exists();

                        $store = Store::exists();

                        if (!$product && !$store) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_and_product");
                            return $response;
                        }

                        if (!$product) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product");
                            return $response;
                        }
                        ProductVariation::seedSampleItems($quantity);
                        break;
                    case "Warehouses":
                        $vendor = Vendor::exists();

                        $store = Store::exists();

                        if (!$vendor && !$store) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_and_vendor");
                            return $response;
                        }

                        if (!$vendor) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_vendor");
                            return $response;
                        }
                        Warehouse::seedSampleItems($quantity);
                        break;
                    case "AttributeGroups":
                        $attribute = Attribute::exists();
                        if (!$attribute) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_attribute");
                            return $response;
                        }
                        AttributeGroup::seedSampleItems($quantity);
                        break;
                    case "ProductAttribute":

                        $store = Store::exists();

                        $product = Product::exists();

                        $product_variation = ProductVariation::exists();

                        $attribute = Attribute::exists();

                        if (!$store && !$attribute && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_attribute_product_and_product_variation");
                            return $response;
                        }

                        if (!$store && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_product_and_product_variation");
                            return $response;
                        }

                        if (!$attribute && !$product_variation && !$product) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_attribute_product_and_product_variation");
                            return $response;
                        }

                        if (!$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product_and_product_variation");
                            return $response;
                        }

                        if (!$attribute) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_attributes");
                            return $response;
                        }
                        if (!$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product_variation");
                            return $response;
                        }
                        ProductAttribute::seedSampleItems($quantity);
                        break;
                    case "ProductMedia":
                        $product = Product::exists();

                        $store = Store::exists();

                        if (!$product && !$store) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_and_product");
                            return $response;
                        }

                        if (!$product) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product");
                            return $response;
                        }
                        ProductMedia::seedSampleItems($quantity);
                        break;
                    case "ProductStock":
                        $vendor = Vendor::exists();

                        $product = Product::exists();

                        $product_variation = ProductVariation::exists();

                        $warehouse = Warehouse::exists();

                        $store = Store::exists();

                        if (!$store && !$warehouse && !$vendor && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_vendor_warehouse_product_and_product_variation");
                            return $response;
                        }

                        if (!$vendor && !$warehouse && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_vendor_warehouse_product_and_product_variation");
                            return $response;
                        }

                        if (!$warehouse && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_warehouse_product_and_product_variation");
                            return $response;
                        }

                        if (!$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product_variation");
                            return $response;
                        }

                        if (!$warehouse) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_warehouse");
                            return $response;
                        }
                        ProductStock::seedSampleItems($quantity);
                        break;
                    case "Carts":
                        $vendor = Vendor::exists();

                        $product = Product::exists();

                        $product_variation = ProductVariation::exists();

                        $warehouse = Warehouse::exists();

                        $store = Store::exists();

                        if (!$store && !$warehouse && !$vendor && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_vendor_warehouse_product_and_product_variation");
                            return $response;
                        }

                        if (!$vendor && !$warehouse && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_vendor_warehouse_product_and_product_variation");
                            return $response;
                        }

                        if (!$warehouse && !$product && !$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_warehouse_product_and_product_variation");
                            return $response;
                        }

                        if (!$product_variation) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product_variation");
                            return $response;
                        }

                        if (!$warehouse) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_warehouse");
                            return $response;
                        }
                        Cart::seedCarts($quantity);
                        break;

                    case "Payments":
                        $order = Order::exists();
                        if (!$order) {
                            $response['success'] = false;
                            $response['errors'][] = 'No Order Exist';
                            return $response;
                        }

                        Payment::seedSampleItems($quantity);
                        break;
                    case "VendorsProduct":
                        $product = Product::exists();

                        $store = Store::exists();

                        $vendor = Vendor::exists();

                        if (!$product && !$store && !$vendor) {

                            $response['success'] = false;
                            $response['errors'][] =  trans("vaahcms-vaahstore-crud-action.create_storevendor_and_product");
                            return $response;
                        }

                        if (!$product && !$vendor) {

                            $response['success'] = false;
                            $response['errors'][] =  trans("vaahcms-vaahstore-crud-action.create_vendor_and_product");
                            return $response;
                        }
                        if (!$store && !$vendor) {

                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store_and_vendor");
                            return $response;
                        }
                        if (!$product) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_product");
                            return $response;
                        }
                        if (!$store) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_store");
                            return $response;
                        }
                        if (!$vendor) {
                            $response['success'] = false;
                            $response['errors'][] = trans("vaahcms-vaahstore-crud-action.create_vendor");
                            return $response;
                        }
                        ProductVendor::seedSampleItems($quantity);
                        break;

                    default:
                        break;
                }

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
        return $response;
    }

    public function getItemsCount(Request $request): JsonResponse
    {
        try {

            $data['count']['Store'] = Store::all()->count();

            $data['count']['Wishlists'] = Wishlist::all()->count();

            $data['count']['Address'] = Address::all()->count();

            $data['count']['Brand'] = Brand::all()->count();

            $data['count']['Attributes'] = Attribute::all()->count();

            $data['count']['AttributeGroups'] = AttributeGroup::all()->count();

            $data['count']['Customer'] = User::all()->count() - 1;

            $data['count']['CustomerGroup'] = CustomerGroup::all()->count();

            $data['count']['Product'] = Product::all()->count();

            $data['count']['ProductVariations'] = ProductVariation::all()->count();

            $data['count']['Vendors'] = Vendor::all()->count();

            $data['count']['ProductAttribute'] = ProductAttribute::all()->count();

            $data['count']['ProductMedia'] = ProductMedia::all()->count();

            $data['count']['VendorsProduct'] = ProductVendor::all()->count();

            $data['count']['Warehouses'] = Warehouse::all()->count();

            $data['count']['ProductStock'] = ProductStock::all()->count();
            $data['count']['Orders'] = Order::all()->count();
            $data['count']['Carts'] = Cart::all()->count();
            $data['count']['Payments'] = Payment::all()->count();
            $data['count']['Shipments'] = Shipment::all()->count();
            $data['count']['Categories'] = Category::all()->count();

            $data['count']['All'] = $data['count']['ProductStock'] +  $data['count']['Warehouses']

                + $data['count']['VendorsProduct']  + $data['count']['ProductMedia'] +  $data['count']['ProductAttribute'] + $data['count']['Vendors']

                + $data['count']['ProductVariations'] +   $data['count']['Product'] + $data['count']['CustomerGroup']  +  $data['count']['Customer']  +

                $data['count']['Attributes'] +   $data['count']['Brand'] + $data['count']['Address'] + $data['count']['Wishlists'] +

                $data['count']['Store'] + $data['count']['AttributeGroups']+$data['count']['Orders']+$data['count']['Carts']+$data['count']['Payments']+$data['count']['Shipments']+$data['count']['Categories'] ;


            $response['success'] = true;

            $response['data'] = $data;

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
        return response()->json($response);
    }


    public function deleteConfirm(Request $request)
    {

        $rules = array(
            'confirm' => 'required',
        );

        $validator = \Validator::make( $request->all(), $rules);

        if ( $validator->fails() ) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return response()->json($response);
        }

        if($request->confirm != 'DELETE')
        {
            $response['success'] = false;
            $response['errors'][] = 'Type DELETE to confirm.';
            return response()->json($response);
        }

        try{

            if(!$request->delete_records)
            {
                $response['success'] = false;
                $response['errors'][] = 'Checked Delete All confirmed.';
                return response()->json($response);
            }

            $models_path = base_path('VaahCms/Modules/Store/Models');

            $namespace = 'VaahCms\Modules\Store\Models';

            // Check if the directory exists
            if (!File::exists($models_path)) {
                Log::error("The directory does not exist: $models_path");
                return Response::json([
                    'success' => false,
                    'errors' => ["The directory does not exist: $models_path"],
                ]);
            }

            // List of models to exclude from truncation
            $excluded_models = [
                'StoreTaxonomy',
                'Lingual',
                'Currency',
                'Setting',
            ];

            $auth_user = Auth::id();

            // Load all PHP files in the models directory
            $files = File::allFiles($models_path);

            // Loop through each file and delete the records if not in excluded list
            foreach ($files as $file) {
                $file_name = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                $class = $namespace . '\\' . $file_name;

                // Check if the class exists, is a model, and not in excluded list
                if (class_exists($class) &&
                    is_subclass_of($class, 'Illuminate\Database\Eloquent\Model') &&
                    !in_array($file_name, $excluded_models)) {

                    $model_instance = new $class();

                    if ($model_instance->getTable() === 'vh_users') {
                        // Delete all records except the logged-in user
                        $class::where('id', '!=', $auth_user)->delete();
                    } else {
                        $class::truncate();
                    }
                }

            }
            $tables_to_truncate = [
                'vh_st_product_categories',
                'vh_st_cart_products',
                'vh_st_currencies',
                'vh_st_lingual',
                'vh_st_product_variation_medias',
                'vh_st_user_customer_groups',
                'vh_st_vendor_pro_stores',
            ];

            foreach ($tables_to_truncate as $table) {
                DB::table($table)->truncate();
            }

            // Commit the transaction
            DB::commit();

            $response['success'] = true;
            $response['data'] = [];
            $response['messages'][] = "Action Was Successful";
            return $response;



        }catch(\Exception $e)

        {
            DB::rollBack();

            return Response::json([
                'success' => false,
                'errors' => [$e->getMessage()],
            ]);

        }

    }

    //----------------------------------------------------------
    public function storeChartFilterSettings(Request $request): JsonResponse
    {
        try {
            foreach ($request->list as $key => $value){
                $setting = Setting::query()

                    ->where('key', $key)
                    ->first();

                if (!$setting) {
                    $setting = new Setting();
                    $setting->key = $key;
                    $setting->value = $value;
                    $setting->save();
                } else {
                    Setting::query()

                        ->where('key', $key)
                        ->update(['value' => $value]);
                }
            }



            $response['success'] = true;
            $response['data'][] = '';
            $response['messages'][] = trans("vaahcms-general.settings_successful_saved");
        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTraceAsString();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return response()->json($response);
    }
    //----------------------------------------------------------
}
