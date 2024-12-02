<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\Store;
use WebReinvent\VaahCms\Models\Taxonomy;
use Faker\Factory;
use WebReinvent\VaahCms\Models\TaxonomyType;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use Illuminate\Support\Str;


class ImportController extends Controller {

    //----------------------------------------------------------
    public function __construct()
    {

    }
    //----------------------------------------------------------

    public function getAssets(Request $request)
    {

        try{

            $data = [];

            $data['types'] = [
                    "name" => "Products",
                    "fields" => $this->getFillableFieldsOfModel(new Product())
            ];


            $stores = Store::all();
            $data['stores'] = [];
            if (count($stores) > 0)
            {
                $data['stores'] = $stores;
            }
            $brands = Brand::all();
            $data['brands'] = [];
            if (count($brands) > 0)
            {
                $data['brands'] = $brands;
            }
            $data['taxonomy']['status'] =Taxonomy::getTaxonomyByType('product-status');
            $data['taxonomy']['types'] = Taxonomy::getTaxonomyByType('product-types');

            $data['actions'] = [];


            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return $response;
    }

    //----------------------------------------------------------
    public function getFillableFieldsOfModel($model){
        $fields = [];
        $fields_execpt =  [
            'uuid',
            'meta',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
        foreach($model->getFillable() as $fillable){
            if(in_array($fillable, $fields_execpt)) continue;

            $fields[] = [
                "label" => ucwords(str_replace('_', ' ', $fillable)),
                "column" => $fillable,
            ];
        }

        return $fields;
    }

    public function importData(Request $request)
    {
        // Validate input request
        $rules = [
            'list' => 'required|array',
            'list.headers' => 'required|array',
            'list.columns' => 'required|array',
            'list.records' => 'required|array',
            'is_override' => 'nullable|boolean',
        ];
        \Validator::validate($request->all(), $rules);

        $headers = $request->list['headers'];
        $columns = $request->list['columns'];
        $records = $request->list['records'];
        $is_override = $request->is_override ?? false;

        // Add "reason" column for invalid records
        $columns[] = 'reason';
        $csv_labels = $columns;

        $csv_content = implode(',', $csv_labels) . "\n";
        $result = [
            'total_records' => count($records),
            'imported_records' => 0,
            'override_records_count' => 0,
            'invalid_records_count' => 0,
            'invalid_records' => [],
        ];

        // Validation rules for individual records
        $record_rules = [
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
            'vh_st_store_id' => 'required',
            'taxonomy_id_product_type' => 'required',
            'taxonomy_id_product_status' => 'required',
            'launch_at' => 'required_without_all:quantity,available_at',
            'available_at' => 'required_without_all:quantity,launch_at',
        ];
        $custom_messages = [
            'name.required' => 'The Name field is required.',
            'slug.required' => 'The Slug field is required.',
            'vh_st_store_id.required' => 'The Store field is required.',
            'taxonomy_id_product_type.required' => 'The Product Type field is required.',
            'taxonomy_id_product_status.required' => 'The Product Status field is required.',
        ];

        foreach ($records as $record) {
            // Validate the record
            $validator = \Validator::make($record, $record_rules, $custom_messages);
            if ($validator->fails()) {
                $result['invalid_records_count']++;
                $record['reason'] = $validator->errors()->all();
                $result['invalid_records'][] = $this->mapTaxonomyFields($record);
                continue;
            }

            // Handle override or duplicate logic
            $existing_product = $this->findExistingProduct($record, $is_override);
            if ($existing_product) {
                if ($is_override) {
                    $this->updateProducts($existing_product, $record);
                    $result['override_records_count']++;
                } else {
                    $result['invalid_records_count']++;
                    $record['reason'] = ['Duplicate product with the same ID, name, or slug exists.'];
                    $result['invalid_records'][] = $this->mapTaxonomyFields($record);
                }
                continue;
            }

            // Create a new product
            $record['uuid'] = \Str::uuid();
            if (Product::create($record)) {
                $result['imported_records']++;
            }
        }

        // Prepare CSV for invalid records
        foreach ($result['invalid_records'] as $invalid_record) {
            $reasons = implode('; ', $invalid_record['reason']);
            unset($invalid_record['reason']);
            $invalid_record['reason'] = $reasons;
            $csv_content .= implode(',', array_values($invalid_record)) . "\n";
        }

        // Prepare response
        return response()->json([
            'success' => true,
            'data' => $result,
            'messages' => ['Imported successfully.'],
            'csv_content' => [
                'headers' => [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="invalid_records.csv"',
                ],
                'content' => $csv_content,
            ],
        ]);
    }

    /**
     * Find existing product by ID or unique fields.
     */
    private function findExistingProduct(array $record, bool $is_override)
    {
        $query = Product::query();
        if ($is_override && isset($record['id'])) {
            $query->where('id', $record['id']);
        } else {
            $query->where('name', $record['name'])->where('slug', $record['slug']);
        }
        return $query->first();
    }

    //----------------------------------------------------------
    private function mapTaxonomyFields(array $record)
    {
        $taxonomyFields = [
            'taxonomy_id_product_type' => 'taxonomy_id_product_type',
            'taxonomy_id_product_status' => 'taxonomy_id_product_status',
            'is_featured_on_home_page' => 'year',
            'is_featured_on_category_page' => 'body-style',

        ];

        foreach ($taxonomyFields as $field => $taxonomy) {
            if (isset($record[$field])) {
                $record[$field] = self::getTaxonomyNameById($taxonomy, $record[$field]);
            }
        }

        return $record;
    }

    public function getTaxonomyNameById($taxonomy_type,$taxonomy_id)
    {
        $taxonomies = Taxonomy::getTaxonomyByType($taxonomy_type);
        if($taxonomies->isEmpty())
        {
            return null;
        }
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy['id'] == $taxonomy_id) {
                return $taxonomy['name'];
            }
        }
        return null;
    }

    //----------------------------------------------------------

    public function updateProducts($inventory, $record){
        $data = [];
        foreach ($record as $key => $value){
            $data[$key] = $value;
        }

        $inventory->update($data);
    }
    //----------------------------------------------------------

    public function toLabel($column)
    {

        $words = explode('_', $column);

        $capitalized_words = array_map('ucfirst', $words);

        $label = implode(' ', $capitalized_words);

        return $label;
    }

    //----------------------------------------------------------



    //------------------------------

    public function getSampleFile(Request $request)
    {

        // get all the column names from the database
        $all_columns = (new Product())->getTableColumns();

        // list of columns that we want to exclude
        $excluded_columns = ['id','uuid','meta','created_by','updated_by','deleted_by','created_at','updated_at','deleted_at'];

        // columns for which we will generate data
        $columns = array_diff($all_columns, $excluded_columns);


        // modified headers
        $custom_headers = [
            'taxonomy_id_product_status' => 'Status',
            'taxonomy_id_product_type' => 'Product Type',
            'vh_st_store_id' => 'Store',
            'vh_st_brand_id' => 'Brand',

        ];

        $headers = [];
        foreach ($columns as $column) {
            // Use custom header if mapping exists, otherwise use column name
            $header = isset($custom_headers[$column]) ? $custom_headers[$column] : $this->toLabel($column);
            $headers[] = $header;
        }


        if(empty($columns))
        {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }

        $faker = Factory::create();

        $records=[];
        $number_of_records = 10;
        for ($i = 0; $i < $number_of_records; $i++) {
            $data = [];
            foreach ($columns as $column) {
                switch ($column) {
                    case 'taxonomy_id_product_status':
                        $taxonomies = Taxonomy::getTaxonomyByType('product-status');
                        $taxonomy_names = $taxonomies->pluck('name')->toArray();
                        $data[$column] = $taxonomy_names ?
                            $taxonomy_names[array_rand($taxonomy_names)] : null;
                        break;

                    case 'taxonomy_id_product_type':
                        $product_types = Taxonomy::getTaxonomyByType('product-types');
                        $type_names = $product_types->pluck('name')->toArray();
                        $data[$column] = $type_names ? $type_names[array_rand($type_names)] : null;
                        break;

                    case 'vh_st_store_id':
                        $stores = Store::where('is_active',1);
                        $store_names= $stores->pluck('name')->toArray();
                        if($store_names)
                        {
                            $store = $store_names[array_rand($store_names)];
                            $data['vh_st_store_id'] = $store;
                        }
                        else{
                            $number_of_characters = rand(5,10);
                            $data['vh_st_store_id']=$faker->text($number_of_characters);
                        }
                        break;

                    case 'vh_st_brand_id':
                        $brands = Brand::where('is_active',1);
                        $brand_names= $brands->pluck('name')->toArray();
                        if($brand_names)
                        {
                            $brand = $brand_names[array_rand($brand_names)];
                            $data['vh_st_brand_id'] = $brand;
                        }
                        else{
                            $number_of_characters = rand(5,10);
                            $data['vh_st_brand_id']=$faker->text($number_of_characters);
                        }
                        break;
                    case 'is_featured_on_homepage':
                    case 'is_featured_on_category_page':
                    case 'is_active':
                        $data[$column] = rand(0, 1);
                        break;

                    default:
                        $data[$column] = $faker->word;
                        break;
                }
            }

            $records[] = $data;
        }

        $content = implode(',', $headers) . "\n";
        foreach ($records as $record) {
            $content .= implode(',', $record) . "\n";
        }
        $response_headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample-products.csv"',
        ];

        return new Response($content, 200, $response_headers);

    }


}
