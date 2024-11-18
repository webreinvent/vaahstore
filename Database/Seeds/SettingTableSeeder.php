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

        foreach($list as $item)
        {
            // Check if the item exists in the database by 'key' only
            $exist = DB::table('vh_st_settings')
                ->where('key', $item['key'])
                ->first();

            if (!$exist) {
                // Encode 'value' as JSON if 'type' is 'json'
                if (isset($item['type']) && $item['type'] == 'json') {
                    $item['value'] = json_encode($item['value']);
                }

                // Insert the item into the database
                DB::table('vh_st_settings')->insert($item);
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
