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
