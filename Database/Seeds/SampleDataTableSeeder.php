<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Faker\Factory;
use Faker\Factory as Faker;
use Faker\Provider\ar_SA\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Helpers\OrderStatusHelper;
use VaahCms\Modules\Store\Models\Address;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeGroup;
use VaahCms\Modules\Store\Models\AttributeGroupItem;
use VaahCms\Modules\Store\Models\AttributeValue;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Cart;
use VaahCms\Modules\Store\Models\Category;
use VaahCms\Modules\Store\Models\Currency;
use VaahCms\Modules\Store\Models\Lingual;
use VaahCms\Modules\Store\Models\Order;
use VaahCms\Modules\Store\Models\OrderItem;
use VaahCms\Modules\Store\Models\PaymentMethod;
use VaahCms\Modules\Store\Models\Payment as OrderPayment;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductMediaImage;
use VaahCms\Modules\Store\Models\ProductStock;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\Shipment;
use VaahCms\Modules\Store\Models\ShipmentItem;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\User;
use VaahCms\Modules\Store\Models\User as StoreUser;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\Warehouse;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\TaxonomyType;
use WebReinvent\VaahExtend\Facades\VaahCountry;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Carbon\CarbonPeriod;

class SampleDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seeds();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    function seeds()
    {
        $this->seedStores();
        $this->seedPaymentMethods();
        $this->seedAttributes();
        $this->seedAttributeGroups();
        $this->seedBrands();
        $this->seedCategories();
        $this->seedCustomers();
        $this->seedAddresses();
        $this->seedVendors();
        $this->seedWarehouses();
        $this->seedProducts();
        $this->seedVendorProducts();
        $this->seedProductStocks();
        $this->seedOrders();
        $this->seedShipmentsItems();
        $this->seedPayment();

    }
    //---------------------------------------------------------------

    public function seedStores()
    {
        $faker = Faker::create();

        $statuses = Taxonomy::getTaxonomyByType('store-status')->pluck('id')->toArray();
        $status = Taxonomy::where('slug', 'approved')
            ->whereHas('type', function ($query) {
                $query->where('slug', 'store-status');
            })
            ->first();
        $currencies = vh_st_get_country_currencies();
        $languages = vh_st_get_country_languages();

        $stores = [];
        $currencies_to_insert = [];
        $languages_to_insert = [];

        for ($i = 0; $i < 50; $i++) {
            $store = new Store();
            $store->name = $faker->company;
            $store->is_multi_currency = rand(0, 1);
            $store->is_multi_lingual = rand(0, 1);
            $store->is_multi_vendor = rand(0, 1);
            $store->is_default =  0;
            $store->taxonomy_id_store_status = $status ? $status->id : null;
            $store->status_notes = 'store Status';
            $store->is_active = 1;
            $store->slug = Str::slug($store->name . '-' . Str::random(5));
            $store->allowed_ips = array_map(fn () => $faker->ipv4, range(1, 5));
            $store->save();

            if (count($currencies) > 1) {
                $random_currencies = array_rand($currencies, min(2, count($currencies)));
                $default_currency_index = $random_currencies[array_rand($random_currencies)]; // Pick one as default
                foreach ((array) $random_currencies as $index) {
                    $currencies_to_insert[] = [
                        'vh_st_store_id' => $store->id,
                        'name' => $currencies[$index]['name'],
                        'is_active' => 1,
                        'code' => $currencies[$index]['code'],
                        'symbol' => $currencies[$index]['symbol'],
                        'is_default' => ($index === $default_currency_index) ? 1 : 0,
                    ];
                }
            }

            if (count($languages) > 1) {
                $random_languages = array_rand($languages, min(2, count($languages)));
                $default_language_index = $random_languages[array_rand($random_languages)];
                foreach ((array) $random_languages as $index) {
                    $languages_to_insert[] = [
                        'vh_st_store_id' => $store->id,
                        'name' => $languages[$index]['name'],
                        'is_active' => 1,
                        'is_default' => ($index === $default_language_index) ? 1 : 0,
                    ];
                }
            }
        }

        // Insert generated store data
        if (!empty($currencies_to_insert)) {
            Currency::insert($currencies_to_insert);
        }

        if (!empty($languages_to_insert)) {
            Lingual::insert($languages_to_insert);
        }

        // === Create Default Store ===
        $default_store = new Store();
        $default_store->name = 'Default Store';
        $default_store->is_multi_currency = 1;
        $default_store->is_multi_lingual = 1;
        $default_store->is_multi_vendor = 1;
        $default_store->is_default = 1;
        $default_store->taxonomy_id_store_status = $status ? $status->id : null;
        $default_store->status_notes = 'Default store Status';
        $default_store->is_active = 1;
        $default_store->slug = Str::slug('Default');
        $default_store->save();

        // Insert default store currencies
        $filtered_currencies = array_values(array_filter($currencies, function ($currency) {
            return in_array($currency['name'], ['US Dollar', 'Indian Rupee']);
        }));

        if ($default_store->is_multi_currency && count($filtered_currencies) > 0) {
            $default_currencies_to_insert = [];

            foreach ($filtered_currencies as $currency) {
                $default_currencies_to_insert[] = [
                    'vh_st_store_id' => $default_store->id,
                    'name' => $currency['name'],
                    'is_active' => 1,
                    'code' => $currency['code'],
                    'symbol' => $currency['symbol'],
                    'is_default' => ($currency['name'] === 'US Dollar') ? 1 : 0, // Set US Dollar as default
                ];
            }

            if (!empty($default_currencies_to_insert)) {
                Currency::insert($default_currencies_to_insert);
            }
            // Insert default store languages (Random language default)
            if (count($languages) > 1) {
                $default_languages_to_insert = [];
                $random_languages = array_rand($languages, min(2, count($languages))); // Pick two random languages

                // Ensure $random_languages is always an array
                $random_languages = is_array($random_languages) ? $random_languages : [$random_languages];

                $default_language_index = $random_languages[array_rand($random_languages)]; // Pick one as default

                foreach ($random_languages as $index) {
                    $default_languages_to_insert[] = [
                        'vh_st_store_id' => $default_store->id,
                        'name' => $languages[$index]['name'],
                        'code' => $languages[$index]['code'],
                        'is_active' => 1,
                        'is_default' => ($index === $default_language_index) ? 1 : 0, // One is default
                    ];
                }

                if (!empty($default_languages_to_insert)) {
                    Lingual::insert($default_languages_to_insert);
                }
            }

        }
    }

    //---------------------------------------------------------------

    public function getListFromJson($json_file_name)
    {
        $json_file = __DIR__."/json/".$json_file_name;
        $jsonString = file_get_contents($json_file);
        $list = json_decode($jsonString, true);
        return $list;
    }
    //---------------------------------------------------------------

    public function seedAttributes()
    {
        $attributes = $this->getListFromJson("attributes.json");

        foreach ($attributes as $attribute) {
            $existing_attribute = Attribute::where('slug', $attribute['slug'])->first();

            $new_attribute = $existing_attribute ?? new Attribute();

            $new_attribute->fill([
                'name' => $attribute['name'],
                'slug' => $attribute['slug'],
                'type' => $attribute['type'],
                'description' => $attribute['description'],
                'is_active' => 1,
            ]);

            $new_attribute->save();

            $this->seedAttributeValues($new_attribute->id, $attribute['values']);
        }
    }
    //---------------------------------------------------------------

    private function seedAttributeValues($attribute_id, $values)
    {
        $existing_values = AttributeValue::where('vh_st_attribute_id', $attribute_id)
            ->pluck('value')
            ->toArray();

        $attribute_values = [];

        foreach ($values as $value) {
            if (!in_array($value, $existing_values)) {
                $attribute_values[] = [
                    'value' => $value,
                    'vh_st_attribute_id' => $attribute_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if (!empty($attribute_values)) {
            AttributeValue::insert($attribute_values);
        }
    }
    //---------------------------------------------------------------

    public function seedAttributeGroups()
    {
        $attribute_groups = $this->getListFromJson("attribute_groups.json");

        foreach ($attribute_groups as $group) {
            $existing_group = AttributeGroup::where('slug', $group['slug'])->first();
            $new_group = $existing_group ?? new AttributeGroup();

            $new_group->fill([
                'name' => $group['name'],
                'slug' => $group['slug'],
                'description' => $group['description'],
                'is_active' => 1,
            ]);
            $new_group->save();

            $this->linkAttributesToGroup($new_group, $group['attributes']);
        }
    }
    //---------------------------------------------------------------

    private function linkAttributesToGroup($attribute_group, $attribute_slugs)
    {
        $attributes = Attribute::whereIn('slug', $attribute_slugs)->get();

        $group_items = [];
        foreach ($attributes as $attribute) {
            $group_items[] = [
                'vh_st_attribute_id' => $attribute->id,
                'vh_st_attribute_group_id' => $attribute_group->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($group_items)) {
            AttributeGroupItem::insert($group_items);
        }
    }
    //---------------------------------------------------------------

    public function seedWarehouses(){
        $vendor_ids = Vendor::pluck('id')->toArray();
        $faker = Factory::create();
        $statuses = Taxonomy::getTaxonomyByType('warehouse-status')->pluck('id')->toArray();
        $active_user = auth()->user();
        $countries = array_column(VaahCountry::getList(), 'name');
        for ($i = 0; $i < 50; $i++) {
            $warehouse = new Warehouse();
            $warehouse->name =$faker->country;
            $warehouse->vh_st_vendor_id = $vendor_ids ? $vendor_ids[array_rand($vendor_ids)] : null;
            $warehouse->country = $countries[array_rand($countries)];
            $warehouse->state = $faker->city;
            $warehouse->city = $faker->city;
            $warehouse->postal_code= $faker->randomNumber(6);
            $warehouse->address_1 = $faker->address;
            $warehouse->address_2 = $faker->secondaryAddress;

            $warehouse->taxonomy_id_warehouse_status = $statuses ? $statuses[array_rand($statuses)] : null;
            $warehouse->status_notes = $faker->sentence;
            $warehouse->is_active = 1;
            $slug = Str::slug($warehouse->name);
            if (Warehouse::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::random(5);
            }
            $warehouse->slug = $slug;
            $warehouse->save();
        }

    }

    //---------------------------------------------------------------

    public function seedBrands()
    {
        $brands = $this->getListFromJson("brands.json");
        $statuses = Taxonomy::getTaxonomyByType('brand-status')->pluck('id')->toArray();
        $active_user = auth()->user();
        $brand_status = Taxonomy::where('slug', 'approved')
            ->whereHas('type', function ($query) {
                $query->where('slug', 'brand-status');
            })
            ->first();
        foreach ($brands as $brand_data) {
            $brand = new Brand;
            $existing_brand = Brand::where('slug', $brand_data['slug'])->first();
            $brand = $existing_brand ?? new Brand;
            $brand->fill([
                'name' => $brand_data['name'],
                'is_default' => ($brand_data['name'] === 'Brand A') ? 1 : 0,
                'registered_by' => $active_user->id,
                'approved_by' => $active_user->id,
                'taxonomy_id_brand_status' => $brand_status['id'],
                'status_notes' => $brand_data['status_notes'],
                'is_active' => $brand_data['is_active'] ? 1 : 0,
                'slug' => Str::slug($brand_data['slug']),
            ]);

            // Save the brand
            $brand->save();
        }
    }
    //---------------------------------------------------------------

    public function seedCategories()
    {
        $categories = $this->getListFromJson("categories.json");

        foreach ($categories as $category_data) {
            // Save the parent category first
            $category = Category::updateOrCreate(
                ['slug' => $category_data['slug']],
                [
                    'name' => $category_data['name'],
                    'is_active' => $category_data['is_active'],
                    'parent_id' => null
                ]
            );

            // If the category has children, save them with parent_id
            if (isset($category_data['children']) && is_array($category_data['children'])) {
                $this->saveChildCategories($category_data['children'], $category->id);
            }
        }
    }
    //---------------------------------------------------------------

//  function to save child categories
    private function saveChildCategories(array $children, int $parent_id)
    {
        foreach ($children as $child_data) {
            $child_category = Category::updateOrCreate(
                ['slug' => $child_data['slug']],
                [
                    'name' => $child_data['name'],
                    'is_active' => $child_data['is_active'],
                    'parent_id' => $parent_id
                ]
            );

            // If this child category also has children, save them recursively
            if (isset($child_data['children']) && is_array($child_data['children'])) {
                $this->saveChildCategories($child_data['children'], $child_category->id);
            }
        }
    }
    //---------------------------------------------------------------

    public function seedCustomers()
    {
        $faker = Factory::create();
        $country_list = VaahCountry::getList();
        $countries = array_column($country_list, 'name');
        $name_titles = vh_name_titles();
        $timezones = vh_get_timezones();

        $status_options = [
            ['label' => 'Active', 'value' => 'active'],
            ['label' => 'Inactive', 'value' => 'inactive'],
            ['label' => 'Blocked', 'value' => 'blocked'],
            ['label' => 'Banned', 'value' => 'banned'],
        ];

        $users = [];
        $roles = Role::where('slug', 'customer')->first();

        for ($i = 0; $i < 50; $i++) {
            $random_country = $countries[array_rand($countries)];
            $selected_country = collect($country_list)->where('name', $random_country)->first();
            $random_title = collect($name_titles)->random()['name'];
            $random_zone = collect($timezones)->random()['slug'];

            $random_status = $status_options[array_rand($status_options)]['value'];
            $random_created_at = $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i:s');


            $users[] = [
                'first_name' => $faker->firstName,
                'username' => $faker->userName,
                'display_name' => $faker->name,
                'email' => $faker->email,
                'country' => $random_country,
                'country_calling_code' => $selected_country ? $selected_country['calling_code'] : null,
                'is_active' => 1,
                'title' => $random_title,
                'timezone' => $random_zone,
                'phone' => $faker->numerify('##########'),
                'birth' => $faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
                'status' => $random_status,
                'created_at' => $random_created_at,
                'updated_at' => $random_created_at,
            ];
        }

        User::insert($users);

        $user_emails = array_column($users, 'email');
        $user_ids = User::whereIn('email', $user_emails)->pluck('id');

        if ($roles && $user_ids->isNotEmpty()) {
            foreach ($user_ids as $user_id) {
                $roles->users()->attach($user_id, ['is_active' => 1]);
            }
        }
    }
    //---------------------------------------------------------------

    public function seedProducts()
    {
        $json_file = __DIR__ . DIRECTORY_SEPARATOR . "./json/products.json";
        $jsonString = file_get_contents($json_file);
        $products = json_decode($jsonString, true);
        $image_path= null;

        $brand_status = Taxonomy::where('slug', 'approved')
            ->whereHas('type', function ($query) {
                $query->where('slug', 'brand-status');
            })
            ->first();
        $variation_status = Taxonomy::where('slug', 'approved')
            ->whereHas('type', function ($query) {
                $query->where('slug', 'product-variation-status');
            })
            ->first();
        foreach ($products as $product) {

            $existing_product = Product::where('name', $product['name'])->first();

            if ($existing_product) {
                continue; // Skip to the next product if it already exists
            }

            $inputs = [];
            $inputs['name'] = $product['name'];
            $inputs['slug'] = Str::slug($inputs['name']);
            $inputs['summary'] = $product['info'];

            // Assign a random active store
            $stores = Store::where('is_active', 1)->pluck('id')->toArray();
            $default_store = Store::where('is_default', 1)->value('id');

            if (!empty($stores) && $default_store) {
                // 90% chance to pick the default store
                $inputs['vh_st_store_id'] = (rand(1, 100) <= 90) ? $default_store : $stores[array_rand($stores)];
            }


            //Assign or create a brand
            $image_url = $product['brand']['src'] ?? $product['defaultImage']['secureSrc'] ?? null;
            $brand_image = self::uploadBrandImage($product['brand']['name'], $image_url);

            $brand = Brand::firstOrCreate(
                ['name' => $product['brand']['name']],
                [
                    'slug' => Str::slug($product['brand']['name']),
                    'is_active' => 1,
                    'taxonomy_id_brand_status' => $brand_status['id'],
                    'image' => $brand_image ?? 'default.png',
                ]
            );
            $category = Category::firstOrCreate(
                ['name' => $product['analytics']['masterCategory']],
                [
                    'slug' => Str::slug($product['analytics']['masterCategory']),
                    'is_active' => 1,
                    'parent_id' => null
                ]
            );

            $inputs['vh_st_brand_id'] = $brand->id;
            // Assign a random product status
            $taxonomy_status = Taxonomy::where('name', 'Approved')
                ->whereHas('type', function ($query) {
                    $query->where('name', 'Product Status');
                })
                ->first();

            if (!empty($taxonomy_status)) {
                $inputs['taxonomy_id_product_status'] = $taxonomy_status->id;
            }

            // Assign a random product type
            $taxonomy = Taxonomy::firstOrCreate(
                ['name' => $product['articleType']],
                [
                    'slug' => Str::slug($product['articleType']),
                    'vh_taxonomy_type_id' => TaxonomyType::firstOrCreate(['name' => 'Product Types'])->id
                ]
            );

            $inputs['taxonomy_id_product_type'] = $taxonomy->id;
            $inputs['is_active'] = 1;

            if (!empty($product['defaultImage']['src'])) {
                $image_url = $product['defaultImage']['src'];
                $image_name = $inputs['slug'] . '.jpg'; // Generate a unique image name
                $image_path = 'media/' . $image_name; // Define storage path

                try {
                    $image_contents = file_get_contents($image_url);
                    Storage::disk('public')->put($image_path, $image_contents); // Save the image in storage


                } catch (\Exception $e) {
                    \Log::error("Failed to download image: " . $e->getMessage());
                }
            }

            // Save the product
            $product = Product::create($inputs);
            $random_categories = Category::where('id', '!=', $category->id)
                ->whereNull('parent_id')
                ->where('is_active', 1)
                ->inRandomOrder()
                ->limit(1)
                ->pluck('id');

            if ($random_categories){
                $category_ids = array_merge([$category->id], $random_categories->toArray());
                $product->productCategories()->attach($category_ids, ['vh_st_product_id' => $product->id]);
            }
            $json_file_variants = __DIR__ . DIRECTORY_SEPARATOR . "./json/attributes.json";
            $jsonString = file_get_contents($json_file_variants);
            $attributes = json_decode($jsonString, true);

            // Create variations
            self::createProductVariations($product, $attributes,$variation_status['id']);


            $Product_media = new ProductMedia();
            $Product_media->vh_st_product_id = $product->id;
            $Product_media_taxonomy_status = Taxonomy::where('name', 'Approved')
                ->whereHas('type', function ($query) {
                    $query->where('slug', 'Product-medias-status');
                })
                ->first();
            if (!empty($Product_media_taxonomy_status)) {
                $Product_media->taxonomy_id_product_media_status = $Product_media_taxonomy_status->id;
            }

            $Product_media->name = $inputs['slug'] . '.jpg';
            $Product_media->type = 'image';
            $Product_media->is_active = 1;
            $Product_media->save();


            self::saveProductImages($Product_media, $inputs, $image_path);
        }
    }

    public static function uploadBrandImage($brand_name, $image_url)
    {
        if (!empty($image_url)) {
            $directory = public_path('image/uploads/brands');
            $file_name = Str::slug($brand_name) . '.jpg'; // Static filename per brand (avoiding timestamp)

            // Check if the brand image already exists
            if (File::exists($directory . '/' . $file_name)) {
                return $file_name; // Return existing image path
            }

            // Ensure the directory exists
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0777, true, true);
            }

            // Download the image and save it
            $image_contents = Http::get($image_url)->body();
            File::put($directory . '/' . $file_name, $image_contents);

            return $file_name; // Save relative path in DB
        }

        return null;
    }
    //---------------------------------------------------------------

    public static function createProductVariations($product, $attributes,$status_id)
    {
        $faker = Factory::create();

        $variation_attributes = ['color', 'gender'];

        $filtered_attributes = array_filter($attributes, function($key) use ($variation_attributes) {
            return in_array($key, $variation_attributes);
        }, ARRAY_FILTER_USE_KEY);

        // Generate combinations of selected attributes and their values
        $attribute_combinations = [];

        foreach ($filtered_attributes as $attribute_key => $attribute) {
            foreach ($attribute['values'] as $value) {
                $attribute_combinations[$attribute_key][] = $value;
            }
        }

        // Generate variations by combining values from each attribute
        $combinations = self::combineAttributes($attribute_combinations);

        // Create product variations for each combination
        foreach ($combinations as $combination) {
            $variation_name = $product->name . ' - ' . implode('/', $combination);
            $variation_slug = Str::slug($product->name . ' ' . implode(' ', $combination));

            ProductVariation::firstOrCreate([
                'vh_st_product_id' => $product->id,
                'name' => $variation_name,
                'slug' => $variation_slug,
                'quantity' => 0,
                'taxonomy_id_variation_status' => $status_id,
                'price' => $faker->randomFloat(2, 20, 35),
                'is_active' => 1,
            ]);
        }
    }
    //---------------------------------------------------------------

    public static function saveProductImages($Product_media = null ,$inputs = null,$image_path=null){

        if (empty($Product_media) || empty($inputs) || empty($image_path)) {
            return false;
        }

        $Product_media_image = new ProductMediaImage();
        $Product_media_image->vh_st_product_media_id = $Product_media->id;
        $Product_media_image->name = $inputs['slug'] . '.jpg';
        $Product_media_image->slug = Str::slug($inputs['slug'] . '.jpg');
        $Product_media_image->url = 'storage/' . $image_path;;
        $Product_media_image->path = 'storage/app/public/' . $image_path;
        $Product_media_image->url_thumbnail = 'storage/app/public/' . $image_path;
        $Product_media_image->save();

    }
    //---------------------------------------------------------------

    public static function combineAttributes($attributes)
    {
        $result = [[]];

        foreach ($attributes as $attribute_values) {
            $new_result = [];
            foreach ($result as $combination) {
                foreach ($attribute_values as $value) {
                    $new_result[] = array_merge($combination, [$value]);
                }
            }
            $result = $new_result;
        }

        return $result;
    }
    //---------------------------------------------------------------

    public function seedVendorProducts()
    {


        for ($i = 0; $i < 100; $i++) {
            // Fetch a random store that has active products
            $store = Store::where('is_default', 1)
                ->first();

//            dd($store);
            if (!$store) {
                // If no store is found, continue to the next iteration
                continue;
            }

            // Fetch random active product IDs from the store
//            $product_ids = $store->products()->where('is_active', 1)->pluck('id')->toArray();
            $product_ids = Product::where('vh_st_store_id', $store->id)
                ->where('is_active', 1)
                ->pluck('id')
                ->toArray();
//            dd($product_ids);
            if (empty($product_ids)) {
                continue; // If no products, skip this iteration
            }

            // Fetch all vendor IDs and status IDs
            $vendor_ids = Vendor::pluck('id')->toArray();
            $status_ids = Taxonomy::getTaxonomyByType('product-vendor-status')->pluck('id')->toArray();

            if (empty($vendor_ids) || empty($status_ids)) {
                continue; // Skip if no vendors or statuses
            }

            // Get the currently authenticated user
            $active_user = optional(auth()->user());

            // Create a new ProductVendor record
            $vendor_product = new ProductVendor();
            $vendor_product->vh_st_vendor_id = $vendor_ids[array_rand($vendor_ids)];
            $vendor_product->vh_st_product_id = $product_ids[array_rand($product_ids)];
            $vendor_product->taxonomy_id_product_vendor_status = $status_ids[array_rand($status_ids)];
            $vendor_product->added_by = $active_user->id ?? null;
            $vendor_product->is_active = 1;
            $vendor_product->created_at = now();
            $vendor_product->updated_at = now();
            $vendor_product->save();

            // Attach the store to the vendor product
            if ($vendor_product->vh_st_vendor_id && $store->id) {
                $vendor_product->storeVendorProduct()->attach($store->id);
            }
        }

    }
    //---------------------------------------------------------------

    public function seedVendors()
    {
        $faker = Faker::create();
        $default_store_id = Store::where('is_default', 1)->value('id');

        // Get all store IDs (excluding default store for the 10% cases)
        $store_ids = Store::where('is_active', 1)->pluck('id')->toArray();
        $active_user = auth()->user();
        $statuses = Taxonomy::getTaxonomyByType('vendor-status')->pluck('id')->toArray();

        for ($i = 0; $i < 50; $i++) {
            $item = new Vendor;
            $item->name =$faker->name;
            if (rand(1, 100) <= 90) {
                $item->vh_st_store_id = $default_store_id;
            } else {
                // 10% chance, assign to a random store
                $item->vh_st_store_id = $store_ids[array_rand($store_ids)];
            }

            $item->owned_by = $active_user->id;
            $item->registered_at = null;
            $item->auto_approve_products = 0;
            $item->approved_by = $active_user->id;
            $item->approved_at = null; // Set the approval date if needed
            $item->taxonomy_id_vendor_status = $statuses ? $statuses[array_rand($statuses)] : null;
            $item->status_notes = 'Default Vendor Status';
            $item->is_active = 1;
            $item->slug = Str::slug('Default Vendor ' . $i);
            $item->save();
        }
    }
    //---------------------------------------------------------------



    public function seedOrders()
    {
        $user_ids = StoreUser::pluck('id')->toArray();
        $payment_method_ids = PaymentMethod::pluck('id')->toArray();
        $order_payment_status_ids = Taxonomy::whereHas('type', function ($query) {
            $query->where('slug', 'order-payment-status');
        })->pluck('id')->toArray();

        $start_date = Carbon::now()->subMonths(1)->startOfMonth(); // Start from beginning of last month
        $end_date = Carbon::now()->endOfMonth(); // End of current month

        $date_range = CarbonPeriod::create($start_date, $end_date); // Use CarbonPeriod for iteration

        foreach ($date_range as $date) {
            $date = Carbon::parse($date); // Ensure it is Carbon instance
            $orders_on_date = rand(2, 10); // Randomly create 2-10 orders per day

            for ($i = 0; $i < $orders_on_date; $i++) {
                $inputs = [];

                $inputs['vh_user_id'] = $user_ids[array_rand($user_ids)];
                $inputs['vh_st_payment_method_id'] = $payment_method_ids[array_rand($payment_method_ids)];

                $is_completed = rand(0, 1); // 50% chance of being "Completed"
                $inputs['order_status'] = $is_completed ? 'Completed' : 'Placed';

                if (!empty($order_payment_status_ids)) {
                    $inputs['taxonomy_id_payment_status'] = $order_payment_status_ids[array_rand($order_payment_status_ids)];
                }

                $inputs['order_shipment_status'] = 'Pending';
                $inputs['delivery_fee'] = 0;
                $inputs['taxes'] = 0;
                $inputs['discount'] = 0;
                $inputs['paid'] = $is_completed ? rand(50, 500) : 0;
                $inputs['is_paid'] = $is_completed ? 1 : 0;
                $inputs['is_active'] = 1;

                // Ensure created_at is within the current day in the loop
                $created_at = $date->copy()->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                $inputs['created_at'] = $created_at;

                // Create the order
                $order = Order::create($inputs);

                // Set completed date (some completed earlier, some later)
                if ($is_completed) {
                    $completed_at = (clone $created_at)->addDays(rand(0, 5))->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                    $order->update(['updated_at' => $completed_at]);
                }

                // Create the order items for the created order
                $this->createOrderItem($order);
            }
        }}

    //---------------------------------------------------------------



    public static function createOrderItem(Order $order)
    {
        $order_items_types = Taxonomy::inRandomOrder()
            ->whereHas('type', function ($query) {
                $query->where('slug', 'order-items-types');
            })
            ->first();

        $order_items_status = Taxonomy::inRandomOrder()
            ->whereHas('type', function ($query) {
                $query->where('slug', 'order-items-status');
            })
            ->first();


        $valid_products = Product::whereHas('productVendors')
            ->with('productVariations', 'productVendors')
            ->get()
            ->shuffle()
            ->take(rand(1, 10));


        $user_addresses = StoreUser::where('id', $order->vh_user_id)
            ->with('addresses')
            ->whereHas('addresses', function ($query) {
                $query->whereHas('addressType', function ($query) {
                    $query->where('slug', 'shipping')
                        ->orWhere('slug', 'billing');
                });
            })
            ->first();

        foreach ($valid_products as $product) {
            $product_id = $product['id'];
            $vendor_id = $product->productVendors->random()->vh_st_vendor_id;

            $random_variation_id = $product->productVariations->pluck('id')->random();
            $price = $product->productVariations->where('id', $random_variation_id)->first()->price;

            $order_item = new OrderItem();
            $order_item['vh_st_order_id'] = $order->id;
            $order_item['vh_user_id'] = $order->vh_user_id;
            $order_item['vh_st_customer_group_id'] = null;

            if (!empty($order_items_types)) {
                $order_item['taxonomy_id_order_items_types'] = $order_items_types->id;
            }

            if (!empty($order_items_status)) {
                $order_item['taxonomy_id_order_items_status'] = $order_items_status->id;
            }

            $order_item['vh_st_product_id'] = $product_id;
            $order_item['vh_st_vendor_id'] = $vendor_id;
            $order_item['vh_st_product_variation_id'] = $random_variation_id;

            if ($user_addresses && $user_addresses->addresses->count() > 0) {
                $random_address = $user_addresses->addresses->random();

                // Assign random shipping and billing address IDs
                $order_item['vh_shipping_address_id'] = $random_address->id;
                $order_item['vh_billing_address_id'] = $random_address->id;  // Same address for billing
            } else {
                // Handle the case where no addresses are available for the user
                // Example: Set default values or handle the error
                $order_item['vh_shipping_address_id'] = null;
                $order_item['vh_billing_address_id'] = null;
            }

            $order_item['quantity'] = rand(1,17);
            $order_item['price'] = $price;
            $order_item['is_invoice_available'] = '';
            $order_item['invoice_url'] = '';
            $order_item['tracking'] = '';
            $order_item['is_active'] = 1;
            $created_at = Carbon::now()->subMonths(1)->addDays(rand(0, 30))->format('Y-m-d H:i:s');
            $order_item['created_at'] = $created_at;

            $order_item['status_notes'] = '';

            // Save the order item
            $order_item->save();

            $total_price = OrderItem::where('vh_st_order_id', $order->id)
                ->get()
                ->sum(function ($order_item) {
                    return $order_item->price * $order_item->quantity;
                });

            DB::update('UPDATE vh_st_orders
            SET amount = ?, payable = ?
            WHERE id = ?',
                [$total_price, $total_price, $order->id]);

            if ($order->order_status === 'Completed') {
                DB::update('UPDATE vh_st_orders
                SET updated_at = ?,paid = payable
                WHERE id = ?',
                    [Carbon::now()->subMonths(1)->addDays(rand(0, 30))->format('Y-m-d H:i:s'), $order->id]);
            }

        }
    }

    //---------------------------------------------------------------

    public function  seedAddresses(){

        $faker = Factory::create();
        for ($i = 0; $i < 50 ; $i++) {
            $inputs['address_line_1'] = $faker->address;
            $inputs['address_line_2'] = $faker->streetAddress;
            $inputs['city'] = $faker->city;
            $inputs['state'] = $faker->streetSuffix;
            $inputs['country'] = $faker->country;
            $inputs['phone'] = $faker->phoneNumber;
            $taxonomy_status = Taxonomy::getTaxonomyByType('address-status');
            $status_ids = $taxonomy_status->pluck('id')->toArray();
            $status_id = $status_ids[array_rand($status_ids)];
            $inputs['taxonomy_id_address_status'] = $status_id;

            $user_ids = User::where('is_active', 1)->pluck('id')->toArray();
            $user_id = $user_ids[array_rand($user_ids)];
            $inputs['vh_user_id'] = $user_id;

            $address_types = Taxonomy::getTaxonomyByType('address-types');
            $address_ids = $address_types->pluck('id')->toArray();
            $address_id = $address_ids[array_rand($address_ids)];
            $address_type = $address_types->where('id', $address_id)->first();
            $inputs['taxonomy_id_address_types'] = $address_id;
            $inputs['is_default'] = 0;

            $item = new Address();
            $item->fill($inputs);
            $item->save();
        }
    }

    public function seedShipmentsItems(){

        $faker = Factory::create();
        for ($i = 0; $i < 100 ; $i++) {
            $phone = $faker->numerify('##########');
            $order_id = rand(1000, 9999);

            $taxonomy_status = Taxonomy::getTaxonomyByType('shipment-status');
            $status_ids = $taxonomy_status->pluck('id')->toArray();
            $status_id = $status_ids[array_rand($status_ids)];
            $inputs['taxonomy_id_shipment_status'] = $status_id;
            $inputs['name'] = $faker->name;
            $inputs['tracking_url'] = $faker->url;
            $inputs['created_at'] = Carbon::now()->subMonths(2)->addDays(rand(0, 60))->format('Y-m-d H:i:s');


            if (rand(0, 1)) {
                $inputs['tracking_key'] = 'phone';
                $inputs['tracking_value'] = $phone;
            } else {
                $inputs['tracking_key'] = 'order_id';
                $inputs['tracking_value'] = $order_id;
            }

            $inputs['is_trackable'] = rand(0, 1);


            $search_orders_request = new Request([
                'query' => $inputs['query'] ?? null
            ]);

            $orders_response = Shipment::searchOrders($search_orders_request);

            if ($orders_response['success'] && $orders_response['data'] instanceof \Illuminate\Support\Collection) {
                $orders = $orders_response['data'];
                if ($orders->isNotEmpty()) {
                    $shipment['orders'][] = $orders->random();
                }
            } else {
                $shipment['orders'][] = null;
            }
            $item = new Shipment();
            $item->fill($inputs);
            $item->save();

            if (isset($shipment['orders']) && is_array($shipment['orders'])) {
                $selected_order = collect($shipment['orders'])->random(); // Pick only one order

                $order_id = $selected_order['id'];
                $order_items = $selected_order['items'] ?? [];

                // Pick only one random order item
                $order_item = collect($order_items)->random();

                $item_id = $order_item['id'];
                $item_quantity_to_be_ship = $order_item['quantity'];

                $item->orders()->attach($order_id, [
                    'vh_st_order_item_id' => $item_id,
                    'quantity' => rand(1,2),
                    'pending' => 0,
                    'created_at' => Carbon::now()->subMonths(2)->addDays(rand(0, 60))->format('Y-m-d H:i:s')
                ]);

                self::updateOrderStatusForShipment($inputs['taxonomy_id_shipment_status'], $order_id);
            }


        }
    }






    /*public function seedShipmentsItems()
    {
        $faker = Factory::create();
        $orders = Order::inRandomOrder()->limit(150)->get();
        $taxonomy_status = Taxonomy::getTaxonomyByType('shipment-status')->pluck('id');

        foreach ($orders as $order) {
            $createdAt = Carbon::now()->subMonths(2)->addDays(rand(0, 60))->format('Y-m-d H:i:s');
            $status_id = $taxonomy_status->random();

            $inputs = [
                'taxonomy_id_shipment_status' => $status_id,
                'name' => $faker->name,
                'tracking_url' => $faker->url,
                'created_at' => $createdAt,
                'tracking_key' => rand(0, 1) ? 'phone' : 'order_id',
                'tracking_value' => rand(0, 1) ? $faker->numerify('##########') : $order->id,
                'is_trackable' => rand(0, 1),
            ];

            $shipment = Shipment::create($inputs);

            foreach ($order->items as $order_item) {
                if ($order_item->quantity > 1) { // Ensure at least 1 remains in order
                    $shippedQuantity = rand(1, $order_item->quantity - 1);
                    $shipment->orders()->attach($order->id, [
                        'vh_st_order_item_id' => $order_item->id,
                        'quantity' => $order_item->quantity,
                        'pending' => 0,
                        'created_at' => $createdAt
                    ]);
                }
            }

//            self::updateOrderStatusForShipment($status_id, $order->id);
        }
    }*/

    private static function updateOrderStatusForShipment($taxonomy_id_shipment_status, $order_id)
    {
        $shipped_order_quantity = ShipmentItem::where('vh_st_order_id', $order_id)->sum('quantity');
        $shipment_status_name = Taxonomy::where('id', $taxonomy_id_shipment_status)->value('name');
        $order = Order::with('items', 'orderPaymentStatus')->findOrFail($order_id);
        $total_order_quantity = $order->items()->sum('quantity');
        $order_payment_status_slug = $order->orderPaymentStatus->slug;
        self::updateOrderStatus($order, $order_payment_status_slug, $shipment_status_name, $shipped_order_quantity, $total_order_quantity);
    }

    public static function updateOrderStatus($order, $payment_status_slug, $shipment_status_name, $shipped_order_quantity, $total_order_quantity)
    {
        $all_delivered = OrderStatusHelper::areAllShipmentsDelivered($order->id);
        $statuses =OrderStatusHelper::getOrderStatusBasedOnShipment(
            $payment_status_slug,
            $shipment_status_name,
            $shipped_order_quantity,
            $total_order_quantity,
            $all_delivered
        );

        // Update the order with the statuses
        $order->order_status = $statuses['order_status'];
        $order->order_shipment_status = $statuses['order_shipment_status'];
        $order->save();
    }


    public function seedCarts(){
        Cart::seedCarts(100);
    }
    //---------------------------------------------------------------

    public static function seedProductStocks()
    {
        $status_ids = Taxonomy::getTaxonomyByType('product-stock-status')->pluck('id')->toArray();
        $faker = Factory::create();
        $product_stocks = [];

        for ($i = 0; $i < 50; $i++) {
            $random_created_at = $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i:s');

            // Fetch a vendor with products and variations
            $vendor = Vendor::whereHas('productVendors.product.productVariations')
                ->where('is_active', 1)
                ->with('productVendors.product.productVariations')
                ->inRandomOrder()
                ->first();

            if (!$vendor) {
                continue;
            }

            $valid_product_vendors = $vendor->productVendors->filter(fn($productVendor) =>
                $productVendor->product && $productVendor->product->productVariations->isNotEmpty()
            );

            if ($valid_product_vendors->isEmpty()) {
                continue;
            }

            $product_vendor = $valid_product_vendors->random();
            $product = $product_vendor->product;
            $product_variation = $product->productVariations->random();

            $warehouse = Warehouse::where('is_active', 1)
                ->where('vh_st_vendor_id', $vendor->id)
                ->inRandomOrder()
                ->first();

            if (!$warehouse) {
                $warehouse = Warehouse::where('is_active', 1)
                    ->inRandomOrder()
                    ->first();
            }

            if (!$warehouse) {
                continue;
            }

            // Create product stock
            $stock = ProductStock::create([
                'vh_st_vendor_id' => $vendor->id,
                'vh_st_product_id' => $product->id,
                'vh_st_product_variation_id' => $product_variation->id,
                'vh_st_warehouse_id' => $warehouse->id,
                'taxonomy_id_product_stock_status' => $status_ids ? $status_ids[array_rand($status_ids)] : null,
                'quantity' => rand(1, 200),
                'is_active' => 1,
                'created_at' => $random_created_at,
                'updated_at' => $random_created_at,
            ]);

            // **Update Variation Quantity**
            $product_variation = ProductVariation::where('id', $stock->vh_st_product_variation_id)
                ->withTrashed()
                ->first();

            if ($product_variation) {
                $product_variation->increment('quantity', $stock->quantity);

                // **Update Product Quantity (Sum of Variations)**
                $product = Product::where('id', $stock->vh_st_product_id)
                    ->withTrashed()
                    ->first();

                if ($product) {
                    $product->quantity = $product->productVariations()->withTrashed()->sum('quantity');
                    $product->save();
                }
            }
        }
    }


    //---------------------------------------------------------------
    public function seedPaymentMethods()
    {
        $payment_methods = [
            ['name' => 'COD', 'slug' => 'cod', 'is_active' => 1],
            ['name' => 'Credit Card', 'slug' => 'credit-card', 'is_active' => 1],
            ['name' => 'Debit Card', 'slug' => 'debit-card', 'is_active' => 1],
            ['name' => 'UPI', 'slug' => 'upi', 'is_active' => 1],
            ['name' => 'Net Banking', 'slug' => 'net-banking', 'is_active' => 1],
            ['name' => 'Wallet', 'slug' => 'wallet', 'is_active' => 1],
        ];

        foreach ($payment_methods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }
    }

    public function seedPayment()
    {
        $faker = Factory::create();

        for ($i = 0; $i < 50; $i++) {
            // Fetch active orders where amount > paid
            $orders = Order::with('user:id,username')

                ->whereColumn('amount', '>', 'paid') // Correct column comparison
                ->limit(10)
                ->get();

            $random_payment_method = PaymentMethod::where('is_active', 1)
                ->inRandomOrder()
                ->select('id')
                ->first();
            $taxonomy_status = Taxonomy::getTaxonomyByType('payment-status');
            $status_ids = $taxonomy_status->pluck('id')->toArray();
            $status_id = $status_ids[array_rand($status_ids)];

            if ($orders->isNotEmpty()) {
                $inputs['vh_st_payment_method_id'] = $random_payment_method['id'];
                $inputs['transaction_id'] = $faker->uuid;
                $inputs['taxonomy_id_payment_status'] = $status_id;


                    $random_order = $orders->random();
                    $payment['orders'] = [$random_order];

                    foreach ($orders as &$order) {
                        if ($order->user) {
                            $order->user_name = $order->user->username;
                            $order->payable_amount = $order->amount - $order->paid;
                            $order->pay_amount = rand(20, 35);

                            $inputs['amount'] = $order->pay_amount;

                            unset($order->user);
                        }

                }

                $inputs['status_notes'] = $faker->text;
                $inputs['notes'] = $faker->text;
                $inputs['payment_gate_response'] = '';
                $inputs['payment_gate_status'] = '';
                $inputs['is_active'] = 1;
                $inputs['created_at'] = Carbon::now()->subMonths(2)->addDays(rand(0, 60))->format('Y-m-d H:i:s');

                $inputs['date'] = Carbon::now()->subMonths(2)->addDays(rand(0, 60))->format('Y-m-d H:i:s');


                $item = new OrderPayment();
                $item->fill($inputs);
                $random_date = Carbon::now()->subMonths(2)->addDays(rand(0, 60))->format('Y-m-d H:i:s');


                $item->date = $random_date;
                $item->save();
                if (isset($payment['orders']) && is_array($payment['orders']) && count($payment['orders']) > 0) {
                    foreach ($payment['orders'] as $key => $order) {

                        $payable_amount = $order->payable;
                        if ($payable_amount > 0) {
                            $pay_amount = rand(0, $payable_amount);
                            $remaining_payable_amount = $payable_amount - $pay_amount;

                            $order_data = [
                                'payable_amount' => $payable_amount,
                                'pay_amount' => $pay_amount,
                            ];
                            $order_payment_detail = [
                                'payable_amount' => $order_data['payable_amount'],
                                'payment_amount' => $order_data['pay_amount'],
                                'remaining_payable_amount' => $remaining_payable_amount,
                                'created_at' => $random_date,
                            ];
                            $item->orders()->attach($order['id'], $order_payment_detail);

                        }
                    }
                }
            }
        }
    }


}
