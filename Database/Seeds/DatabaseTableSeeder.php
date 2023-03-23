<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\Store;
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
            $item->name = 'Default';
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

}
