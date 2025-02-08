<?php
namespace VaahCms\Modules\Store\Database\Seeds;


use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeValue;
use VaahCms\Modules\Store\Models\Currency;
use VaahCms\Modules\Store\Models\Lingual;
use VaahCms\Modules\Store\Models\Store;
use WebReinvent\VaahCms\Entities\Taxonomy;

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
    }

    public function seedStores()
    {
        $faker = Faker::create();


        $statuses = Taxonomy::getTaxonomyByType('store-status')->pluck('id')->toArray();
        $currencies = vh_st_get_country_currencies();
        $languages = vh_st_get_country_languages();

        $stores = [];
        $currencies_to_insert = [];
        $languages_to_insert = [];

        for ($i = 0; $i < 200; $i++) {
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

    public function getListFromJson($json_file_name)
    {
        $json_file = __DIR__."/json/".$json_file_name;
        $jsonString = file_get_contents($json_file);
        $list = json_decode($jsonString, true);
        return $list;
    }

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

    private function seedAttributeValues($attribute_id, $values)
    {
        $attribute_values = [];
        foreach ($values as $value) {
            $attribute_values[] = [
                'value' => $value,
                'vh_st_attribute_id' => $attribute_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        AttributeValue::insert($attribute_values);
    }
}
