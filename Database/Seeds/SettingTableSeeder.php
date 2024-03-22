<?php
namespace VaahCms\Modules\Store\Database\Seeds;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
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

        $list = $this->getListFromJson("settings.json");

        $category = 'global';

        foreach($list as $item)
        {

            if(!isset($item['category'])
            || (isset($item['category']) && !$item['category'])){
             $item['category'] = $category;
            }


            $exist = DB::table( 'vh_settings' )
                ->where( 'category', $item['category'] )
                ->where( 'key', $item['key'] )
                ->first();



            if (!$exist){

                if(isset($item['type']) && $item['type']=='json')
                {
                    $item['value']=json_encode($item['value']);
                }

                DB::table( 'vh_settings' )->insert( $item );
            }
        }


    }



    //---------------------------------------------------------------------
    public function getListFromJson($json_file_name)
    {
        $json_file = __DIR__."/json/".$json_file_name;
        $jsonString = file_get_contents($json_file);
        $list = json_decode($jsonString, true);
        return $list;
    }


}
