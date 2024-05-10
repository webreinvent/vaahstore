<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

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
        $this->seedDefaultStore();
        $this->seedDefaultVendor();
        $this->seedDefaultBrand();
        $this->seedDefaultPrdouct();
        $this->seedRoles();
        $this->seedLanguages();
        $this->seedLanguageCategories();
        $this->seedLanguageStrings();


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
    public function seedDefaultStore()
    {
        $item = Store::where('is_default', 1)->first();
        if(!$item){
            $status = Taxonomy::getTaxonomyByType('store-status')->first();
            $item = new Store;
            $item->name = 'Default Store';
            $item->is_multi_currency  = 1;
            $item->is_multi_lingual  = 1;
            $item->is_multi_vendor  = 1;
            $item->is_default = 1;
            $item->taxonomy_id_store_status = $status->id;
            $item->status_notes = 'Default store Status';
            $item->is_active = 1;
            $item->slug = Str::slug('Default');
            $item->save();
        }

    }
    //---------------------------------------------------------------
    public function seedDefaultVendor()
    {
        $item = Vendor::where('is_default', 1)->first();
        if(!$item){
            $itemStore = Store::where('is_default', 1)->first();
            $active_user = auth()->user();
            $status = Taxonomy::getTaxonomyByType('vendor-status')->first();
            $item = new Vendor;
            $item->name = 'Default Vendor';
            $item->vh_st_store_id  = $itemStore->id;
            $item->is_default = 1;
            $item->owned_by = $active_user->id;
            $item->registered_at = null;
            $item->auto_approve_products = 0;
            $item->approved_by = $active_user->id;
            $item->approved_at = null;
            $item->taxonomy_id_vendor_status = $status->id;
            $item->status_notes = 'Default Vendor Status';
            $item->is_active = 1;
            $item->slug = Str::slug('Default');
            $item->save();
        }
    }
    //---------------------------------------------------------------
    public function seedDefaultBrand()
    {
        $item = Brand::where('is_default', 1)->first();
        if(!$item){
            $active_user = auth()->user();
            $status = Taxonomy::getTaxonomyByType('brand-status')->first();
            $item = new Brand;
            $item->name = 'Default Brand';
            $item->is_default = 1;
            $item->registered_by = $active_user->id;
            $item->approved_by = $active_user->id;
            $item->taxonomy_id_brand_status = $status->id;
            $item->status_notes = 'Default Brand Status';
            $item->is_active = 1;
            $item->slug = Str::slug('Default');
            $item->save();
        }
    }
    //---------------------------------------------------------------
    public function seedDefaultPrdouct()
    {
        $item = Product::where('is_default', 1)->first();
        if(!$item){
            $itemStore = Store::where('is_default', 1)->first();
            $itemBrand = Brand::where('is_default', 1)->first();
            $status = Taxonomy::getTaxonomyByType('product-status')->first();
            $type = Taxonomy::getTaxonomyByType('product-types')->first();
            $item = new Product;
            $item->name = 'Default Product';
            $item->vh_st_store_id  = $itemStore->id;
            $item->is_default = 1;
            $item->quantity = 1;
            $item->in_stock = 1;
            $item->vh_st_store_id = $itemStore->id;
            $item->vh_st_brand_id = $itemBrand->id;
            $item->taxonomy_id_product_status = $status->id;
            $item->taxonomy_id_product_type = $type->id;
            $item->status_notes = 'Default Product Status';
            $item->is_active = 1;
            $item->slug = Str::slug('Default');
            $item->save();
        }
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

}
