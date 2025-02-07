<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Currency;
use VaahCms\Modules\Store\Models\Lingual;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use Faker\Factory as Faker;

class DatabaseTableSeeder extends Seeder
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
        $this->seedTaxonomyTypes();
        $this->seedTaxonomies();
        $this->seedRoles();
        $this->seedLanguages();
        $this->seedLanguageCategories();
        $this->seedLanguageStrings();
        $this->seedStores();


        $seeder = new SettingTableSeeder();
        $seeder->run();

    }

    //--------------------------------------------------------------

    public function getListFromJson($json_file_name)
    {
        $json_file = __DIR__."/json/".$json_file_name;
        $jsonString = file_get_contents($json_file);
        $list = json_decode($jsonString, true);
        return $list;
    }

    //---------------------------------------------------------------

    public function seedTaxonomies()
    {
        $json_file_path = __DIR__."/json/taxonomies.json";
        VaahSeeder::taxonomies($json_file_path);
    }
    //---------------------------------------------------------------
    public function seedTaxonomyTypes()
    {
        $json_file_path = __DIR__."/json/taxonomy_types.json";
        VaahSeeder::taxonomyTypes($json_file_path);
    }

    //---------------------------------------------------------------
    public function seedRoles()
    {
        $json_file_path = __DIR__."/json/vendor_roles.json";
        VaahSeeder::roles($json_file_path);
    }

    //----------------------------------------------------------------

    public function seedLanguageCategories()
    {
        $list = [

            ["name" => 'VaahStore Crud Action'],
        ];

        $this->storeSeeds('vh_lang_categories', $list);

    }

    public function storeSeeds($table, $list, $primary_key='slug', $create_slug=true, $create_slug_from='name')
    {
        foreach ($list as $item)
        {
            if($create_slug)
            {
                $item['slug'] = Str::slug($item[$create_slug_from]);
            }


            $record = DB::table($table)
                ->where($primary_key, $item[$primary_key])
                ->first();


            if(!$record)
            {
                DB::table($table)->insert($item);
            } else{
                DB::table($table)->where($primary_key, $item[$primary_key])
                    ->update($item);
            }
        }
    }

    public function seedLanguages()
    {

        $list = $this->getListFromJson("languages.json");

        foreach($list as $item)
        {
            $exist = DB::table( 'vh_lang_languages' )
                ->where( 'locale_code_iso_639', $item['locale_code_iso_639'] )
                ->first();

            if (!$exist){

                if($item['locale_code_iso_639'] == 'en')
                {
                    $item['default'] = 1;
                }

                DB::table( 'vh_lang_languages' )->insert( $item );
            }
        }

    }

    public function seedLanguageStrings()
    {

        $list = $this->getListFromJson("language_strings.json");

        foreach($list as $item)
        {

            $item['slug'] = Str::slug($item['name'],'_');

            $lang = DB::table( 'vh_lang_languages' )
                ->where( 'locale_code_iso_639', $item['locale_code_iso_639'] )
                ->first();

            $exist = DB::table( 'vh_lang_strings' )
                ->where('vh_lang_language_id', $lang->id)
                ->where( 'slug',  $item['slug'] )
                ->first();

            $cat = DB::table( 'vh_lang_categories' )
                ->where( 'slug', $item['category'] )
                ->first();


            if (!$exist && $lang && $cat){

                $item['vh_lang_language_id'] = $lang->id;

                $item['vh_lang_category_id'] = $cat->id;

                unset($item['category']);
                unset($item['locale_code_iso_639']);

                DB::table( 'vh_lang_strings' )->insert( $item );
            }
        }

    }
    public function seedStores()
    {
        $faker = Faker::create();
        $storeNames = [
            'Green Valley Market', 'Urban Goods', 'FreshMart', 'Fresh Picked Produce',
            'The Modern Shopper', 'Tech Haven', 'ElectroHub', 'The Book Nook',
            'Chic Boutique', 'Nature’s Bounty', 'The Home Emporium', 'Tasteful Delights',
            'Sports Gear Zone', 'Global Market', 'Style Loft', 'Vintage Finds',
            'Fit & Active', 'Gadget Galaxy', 'Daily Essentials', 'City Grocery',
            'Beauty Bliss', 'Luxury Living', 'Pets World', 'The Toy Chest',
            'Health & Harmony', 'Outdoor Adventures', 'Happy Feet Shoes', 'Gourmet Eats',
        ];

        $statuses = Taxonomy::getTaxonomyByType('store-status')->pluck('id')->toArray();
        $currencies = vh_st_get_country_currencies();
        $languages = vh_st_get_country_languages();

        $stores = [];
        $currenciesToInsert = [];
        $languagesToInsert = [];

        for ($i = 0; $i < 1000; $i++) {
            $store = new Store();
            $store->name = $storeNames[array_rand($storeNames)];
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

            // Multi-currency handling
            if ($store->is_multi_currency && count($currencies) > 1) {
                $randomCurrencies = array_rand($currencies, min(2, count($currencies)));
                foreach ((array) $randomCurrencies as $index) {
                    $currenciesToInsert[] = [
                        'vh_st_store_id' => $store->id,
                        'name' => $currencies[$index]['name'],
                        'is_active' => 1,
                    ];
                }
            }

            // Multi-lingual handling
            if ($store->is_multi_lingual && count($languages) > 1) {
                $randomLanguages = array_rand($languages, min(2, count($languages)));
                foreach ((array) $randomLanguages as $index) {
                    $languagesToInsert[] = [
                        'vh_st_store_id' => $store->id,
                        'name' => $languages[$index]['name'],
                        'is_active' => 1,
                    ];
                }
            }
        }

        // Bulk insert currencies
        if (!empty($currenciesToInsert)) {
            Currency::insert($currenciesToInsert);
        }

        // Bulk insert languages
        if (!empty($languagesToInsert)) {
            Lingual::insert($languagesToInsert);
        }
    }

}
