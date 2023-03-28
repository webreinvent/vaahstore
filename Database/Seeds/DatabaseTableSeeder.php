<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Illuminate\Database\Seeder;
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
//        $this->seedTaxonomyTypes();
//        $this->seedTaxonomies();
        $this->seedDefaultStore();
        $this->seedDefaultVendor();
        $this->seedDefaultBrand();
        $this->seedDefaultPrdouct();
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

}
