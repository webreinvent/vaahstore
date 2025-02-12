<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Faker\Factory;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeGroup;
use VaahCms\Modules\Store\Models\AttributeGroupItem;
use VaahCms\Modules\Store\Models\AttributeValue;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Cart;
use VaahCms\Modules\Store\Models\Category;
use VaahCms\Modules\Store\Models\Currency;
use VaahCms\Modules\Store\Models\Lingual;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductMediaImage;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\User;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\Warehouse;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\TaxonomyType;
use WebReinvent\VaahExtend\Facades\VaahCountry;
use Illuminate\Support\Facades\Storage;

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
        $this->seedAttributes();
        $this->seedAttributeGroups();
        $this->seedWarehouses();
        $this->seedBrands();
        $this->seedCategories();
        $this->seedCustomers();
        $this->seedCarts();
        $this->seedProducts();
    }
    //---------------------------------------------------------------

    public function seedStores()
    {
        $faker = Faker::create();


        $statuses = Taxonomy::getTaxonomyByType('store-status')->pluck('id')->toArray();
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
            $store->is_default = ($i === 0) ? 1 : 0;
            $store->taxonomy_id_store_status = $statuses ? $statuses[array_rand($statuses)] : null;
            $store->status_notes = 'store Status';
            $store->is_active = rand(0, 1);
            $store->slug = Str::slug($store->name . '-' . Str::random(5));
            $store->allowed_ips = array_map(fn () => $faker->ipv4, range(1, 5));
            $store->save();

            if ($store->is_multi_currency && count($currencies) > 1) {
                $random_currencies = array_rand($currencies, min(2, count($currencies)));
                foreach ((array) $random_currencies as $index) {
                    $currencies_to_insert[] = [
                        'vh_st_store_id' => $store->id,
                        'name' => $currencies[$index]['name'],
                        'is_active' => 1,
                    ];
                }
            }

            if ($store->is_multi_lingual && count($languages) > 1) {
                $random_languages = array_rand($languages, min(2, count($languages)));
                foreach ((array) $random_languages as $index) {
                    $languages_to_insert[] = [
                        'vh_st_store_id' => $store->id,
                        'name' => $languages[$index]['name'],
                        'is_active' => 1,
                    ];
                }
            }
        }

        if (!empty($currencies_to_insert)) {
            Currency::insert($currencies_to_insert);
        }

        if (!empty($languages_to_insert)) {
            Lingual::insert($languages_to_insert);
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


    public function seedBrands()
    {
        $brands = $this->getListFromJson("brands.json");
        $statuses = Taxonomy::getTaxonomyByType('brand-status')->pluck('id')->toArray();
        $active_user = auth()->user();

        foreach ($brands as $brand_data) {
            $brand = new Brand;
            $existing_brand = Brand::where('slug', $brand_data['slug'])->first();
            $brand = $existing_brand ?? new Brand;
            $brand->fill([
                'name' => $brand_data['name'],
                'is_default' => ($brand_data['name'] === 'Brand A') ? 1 : 0,
                'registered_by' => $active_user->id,
                'approved_by' => $active_user->id,
                'taxonomy_id_brand_status' => $statuses ? $statuses[array_rand($statuses)] : null,
                'status_notes' => $brand_data['status_notes'],
                'is_active' => $brand_data['is_active'] ? 1 : 0,
                'slug' => Str::slug($brand_data['slug']),
            ]);

            // Save the brand
            $brand->save();
        }
    }

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

        for ($i = 0; $i < 500; $i++) {
            $random_country = $countries[array_rand($countries)];
            $selected_country = collect($country_list)->where('name', $random_country)->first();
            $random_title = collect($name_titles)->random()['name'];
            $random_zone = collect($timezones)->random()['slug'];

            $random_status = $status_options[array_rand($status_options)]['value'];
            $random_created_at = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');

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

    public function seedProducts()
    {
        $json_file = __DIR__ . DIRECTORY_SEPARATOR . "./json/products.json";
        $jsonString = file_get_contents($json_file);
        $products = json_decode($jsonString, true);
        $image_path= null;


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
            if (!empty($stores)) {
                $inputs['vh_st_store_id'] = $stores[array_rand($stores)];
            }

            //Assign or create a brand
            $brand = Brand::firstOrCreate(
                ['name' => $product['brand']['name']],
                ['slug' => Str::slug($product['brand']['name'])]
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

            $json_file_variants = __DIR__ . DIRECTORY_SEPARATOR . "./json/attributes.json";
            $jsonString = file_get_contents($json_file_variants);
            $attributes = json_decode($jsonString, true);

            // Create variations
            self::createProductVariations($product, $attributes);


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

    public static function createProductVariations($product, $attributes)
    {
        $faker = Factory::create();

        $variation_attributes = ['color', 'size', 'gender'];

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
                'price' => $faker->randomFloat(2, 10, 500),
                'is_active' => 1,
            ]);
        }
    }

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

    public function seedCarts(){
        Cart::seedCarts();
    }
}
