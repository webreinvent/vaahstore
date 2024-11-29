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

            $suppliers = Store::all();
            $data['suppliers'] = [];
            if (count($suppliers) > 0)
            {
                $data['suppliers'] = $suppliers;
            }
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
    //----------------------------------------------------------
//    public function importData(Request $request){
//
//        $rules = array(
//            'list' => 'required',
//            'is_override' => 'nullable|boolean',
//            'list.headers' => 'required|array',
//            'list.columns' => 'required|array',
//            'list.records' => 'required|array',
//        );
//
//        \Validator::validate($request->all(), $rules);
//
//        $headers = $request->list['headers'];
//        $columns = $request->list['columns'];
//        $records = $request->list['records'];
//        $import_type = $request->import_type;
//
//        //pass a new label to the column array
//        array_push($columns, 'reason');
//
//        //create a copy of columns array and create labels for every columns
//        $labels = $columns;
//
//        //add some custom labels for invalid records to show in csv file
//
////        $modified_labels = [
////            'part_number'=> 'Part Number', 'oem_number'=> 'OEM Number', 'description'=> 'Description',
////            'sm_supplier_id'=> 'Supplier Name', 'parts_link_id'=> 'Parts Link', 'taxonomy_id_make'=> 'Make',
////            'taxonomy_id_model'=> 'Model', 'taxonomy_id_year'=> 'Year', 'taxonomy_id_body_style'=> 'Body Style',
////            'taxonomy_id_first_year'=> 'First Year', 'taxonomy_id_last_year'=> 'Last year',
////            'taxonomy_id_section'=> 'Section', 'taxonomy_id_part_type'=> 'Part Type', 'taxonomy_id_class'=> 'Class',
////            'cubic_feet'=> 'Cubic Feet', 'taxonomy_id_location'=> 'Location', 'quantity'=> 'Quantity in stock',
////            'list_price'=> 'List Price', 'sales_price'=> 'Sales Price', 'reorder_quantity'=> 'Reorder Quantity',
////            'reorder_point'=> 'Reorder Point', 'taxonomy_id_status'=> 'Status', 'is_active'=> 'Is Active','reason' => 'Reason',
////        ];
////
////        foreach ($modified_labels as $old_label => $new_label) {
////            $index = array_search($old_label, $columns);
////            if ($index !== false) {
////                $labels[$index] = $new_label;
////            }
////        }
//
//        $content = implode(',', array_values($labels)) . "\n";
//        $is_override = $request->is_override ?? false;
//
//
//        $result = [];
//        $result['invalid_records_count'] = 0;
//        $result['override_records_count'] = 0;
//        $result['imported_records'] = 0;
//        $invalid_records = [];
//        foreach ($records as $record){
//
//
//            if (is_numeric($record['quantity'])) {
//                $record['quantity'] = (int)$record['quantity'];
//            }
//
//
//
//            $rules = validator($record, [
//
//                'name' => 'required|max:150',
//                'slug' => 'required|max:150',
//                'summary' => 'max:100',
//                'vh_st_store_id'=> 'required',
//                'taxonomy_id_product_type'=> 'required',
//                'details' => 'max:250',
//                'seo_title' => 'max:100',
//                'seo_meta_description' => 'max:250',
//                'seo_meta_keyword' => 'max:20',
//                'seo_meta_keyword.*' => 'max:50',
//                'taxonomy_id_product_status'=> 'required',
//                'status_notes' => 'max:250',
//                'quantity' => '',
//                'launch_at' => 'required_without_all:quantity,available_at,0',
//                'available_at' => 'required_without_all:quantity,launch_at,0',
//            ],
//                [    'name.required' => 'The Name field is required',
//                    'name.max' => 'The Name field may not be greater than :max characters',
//                    'slug.required' => 'The Slug field is required',
//                    'slug.max' => 'The Slug field may not be greater than :max characters',
//                    'summary.max' => 'The Summary field may not be greater than :max characters',
//                    'details.max' => 'The Details field may not be greater than :max characters',
//                    'seo_title.max' => 'The Seo title field may not be greater than :max characters',
//                    'seo_meta_description.max' => 'The Seo Description field may not be greater than :max characters',
//                    'seo_meta_keyword.max' => 'The Seo Keywords field may not have greater than :max keywords',
//                    'seo_meta_keyword.*' => 'The Seo Keyword field may not be greater than :max characters',
//                    'taxonomy_id_product_status.required' => 'The Status field is required',
//                    'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
//                    'vh_st_store_id.required' => 'The Store field is required',
//                    'taxonomy_id_product_type.required' => 'The Type field is required',
//                    'status_notes.*' => 'The Status notes field is required for "Rejected" Status',
//                    'quantity.digits_between' => 'The Quantity field must not be greater than 9 digits',
//                    'quantity.required' => 'The Product Quantity is required',
//                    'quantity.min' => 'The Product Quantity is required',
//                ]
//            );
//
//            if ( $rules->fails() ) {
//
//                $result['invalid_records_count']++;
//
//                $invalid_record = $record;
//                $invalid_record['reason'] = $rules->errors()->all();
//                $supplier_id = $invalid_record['sm_supplier_id'];
//                $make_id = $invalid_record['taxonomy_id_make'];
//                $model_id = $invalid_record['taxonomy_id_model'];
//                $year_id = $invalid_record['taxonomy_id_year'];
//                $body_style_id = $invalid_record['taxonomy_id_body_style'];
//                $first_year_id = $invalid_record['taxonomy_id_first_year'];
//                $last_year_id = $invalid_record['taxonomy_id_last_year'];
//                $section_id = $invalid_record['taxonomy_id_section'];
//                $part_type_id = $invalid_record['taxonomy_id_part_type'];
//                $class_id = $invalid_record['taxonomy_id_class'];
//                $location_id = $invalid_record['taxonomy_id_location'];
//                $status_id = $invalid_record['taxonomy_id_status'];
//                $invalid_record['taxonomy_id_make'] = self::getTaxonomyNameById('make',$make_id);
//                $invalid_record['taxonomy_id_model'] = self::getTaxonomyNameById('model',$model_id);
//                $invalid_record['taxonomy_id_year'] = self::getTaxonomyNameById('year',$year_id);
//                $invalid_record['taxonomy_id_body_style'] = self::getTaxonomyNameById('body-style',$body_style_id);
//                $invalid_record['taxonomy_id_first_year'] = self::getTaxonomyNameById('first-year',$first_year_id);
//                $invalid_record['taxonomy_id_last_year'] = self::getTaxonomyNameById('last-year',$last_year_id);
//                $invalid_record['taxonomy_id_section'] = self::getTaxonomyNameById('section',$section_id);
//                $invalid_record['taxonomy_id_part_type'] = self::getTaxonomyNameById('part-type',$part_type_id);
//                $invalid_record['taxonomy_id_class'] = self::getTaxonomyNameById('class',$class_id);
//                $invalid_record['taxonomy_id_location'] = self::getTaxonomyNameById('location',$location_id);
//                $invalid_record['taxonomy_id_status'] = self::getTaxonomyNameById('status',$status_id);
//                $suppliers = Product::where('is_active',1)->get();
//
//
//                foreach ($suppliers as $supplier) {
//
//                    if ($supplier['id'] == $supplier_id) {
//                        $invalid_record['sm_supplier_id'] = $supplier['name'];
//                        break;
//                    }
//                }
//
//                $invalid_records[] = $invalid_record;
//
//                continue;
//            }
//
//            $inventory = Product::where('part_number',$record['part_number'])
//                ->where('oem_number',$record['oem_number'])
//                ->first();
//            dd($request);
//            if($is_override && $inventory)
//            {
//
//                $result['override_records_count']++;
//                $this->updateInventory($inventory,$record);
//                continue;
//            }
//            else if(!$is_override && $inventory)
//            {
//                $result['invalid_records_count']++;
//                $invalid_record = $record;
//                $supplier_id = $invalid_record['sm_supplier_id'];
//                $make_id = $invalid_record['taxonomy_id_make'];
//                $invalid_record['taxonomy_id_make'] = self::getTaxonomyNameById('make',$make_id);
//                $model_id = $invalid_record['taxonomy_id_model'];
//                $invalid_record['taxonomy_id_model'] = self::getTaxonomyNameById('model',$model_id);
//                $year_id = $invalid_record['taxonomy_id_year'];
//                $invalid_record['taxonomy_id_year'] = self::getTaxonomyNameById('year',$year_id);
//                $body_style_id = $invalid_record['taxonomy_id_body_style'];
//                $invalid_record['taxonomy_id_body_style'] = self::getTaxonomyNameById('body-style',$body_style_id);
//                $first_year_id = $invalid_record['taxonomy_id_first_year'];
//                $invalid_record['taxonomy_id_first_year'] = self::getTaxonomyNameById('first-year',$first_year_id);
//                $last_year_id = $invalid_record['taxonomy_id_last_year'];
//                $invalid_record['taxonomy_id_last_year'] = self::getTaxonomyNameById('last-year',$last_year_id);
//                $section_id = $invalid_record['taxonomy_id_section'];
//                $invalid_record['taxonomy_id_section'] = self::getTaxonomyNameById('section',$section_id);
//                $part_type_id = $invalid_record['taxonomy_id_part_type'];
//                $invalid_record['taxonomy_id_part_type'] = self::getTaxonomyNameById('part-type',$part_type_id);
//                $class_id = $invalid_record['taxonomy_id_class'];
//                $invalid_record['taxonomy_id_class'] = self::getTaxonomyNameById('class',$class_id);
//                $location_id = $invalid_record['taxonomy_id_location'];
//                $invalid_record['taxonomy_id_location'] = self::getTaxonomyNameById('location',$location_id);
//                $status_id = $invalid_record['taxonomy_id_status'];
//                $invalid_record['taxonomy_id_status'] = self::getTaxonomyNameById('status',$status_id);
//
//                $suppliers = Supplier::where('is_active',1)->get();
//
//                foreach ($suppliers as $supplier) {
//
//                    if ($supplier['id'] == $supplier_id) {
//                        $invalid_record['sm_supplier_id'] = $supplier['name'];
//                        break;
//                    }
//                }
//
//                $invalid_record['reason'] = ['Inventory already exists for the provided part number and Oem number'];
//                $invalid_records[] = $invalid_record;
//                continue;
//            }
//            else
//            {
//                $data = [];
//                foreach ($record as $key => $value){
//                    $data['uuid'] = \Str::uuid();
//                    $data[$key] = $value;
//
//                }
//
//                $inventory = Product::create($data);
//                if($inventory){
//                    $result['imported_records']++;
//                }
//            }
//        }
//
//
//         //code to add invalid record to content variable for csv file generation
//
//        if($invalid_records)
//        {
//            foreach ($invalid_records as $invalid_record) {
//
//                $reasons = $invalid_record['reason'];
//                unset($invalid_record['reason']);
//                $values = array_values($invalid_record);
//
//                $reason_text = '';
//                foreach ($reasons as $key => $reason) {
//                    $reason_text .= ($key + 1) . '. ' . $reason . ' ';
//                }
//
//                $reason_text = rtrim($reason_text);
//
//                // added the reason to the values array
//                $values[] = '"' . str_replace("\n", '\n', $reason_text) . '"';
//
//                // Implode the values and add a newline character
//                $content .= implode(',', $values) . "\n";
//            }
//        }
//
//
//
//        $headers = [
//            'Content-Type' => 'text/csv',
//            'Content-Disposition' => 'attachment; filename="invalid_records.csv"',
//        ];
//
//        $result['total_records'] = count($records);
//        $result['imported_records'] =  $result['imported_records'] + $result['override_records_count'];
//        $result['invalid_records'] = $invalid_records;
//        $result['csv_content'] = [
//            'headers' => $headers,
//            'content' => $content,
//        ];
//
//
//        $response['success'] = true;
//        $response['data'] = $result;
//        $response['messages'][] = 'Imported successfully.';
//        return $response;
//    }
    public function importData(Request $request)
    {
        $rules = [
            'list' => 'required|array',
            'list.headers' => 'required|array',
            'list.columns' => 'required|array',
            'list.records' => 'required|array',
            'is_override' => 'nullable|boolean',
//            'import_type' => 'required|string',
        ];

        \Validator::validate($request->all(), $rules);

        $headers = $request->list['headers'];
        $columns = $request->list['columns'];
        $records = $request->list['records'];
        $is_override = $request->is_override ?? false;

        // Add "reason" column for invalid records
        array_push($columns, 'reason');
        $labels = $columns;

        $content = implode(',', array_values($labels)) . "\n";
        $result = [
            'total_records' => count($records),
            'imported_records' => 0,
            'override_records_count' => 0,
            'invalid_records_count' => 0,
            'invalid_records' => [],
        ];

        foreach ($records as $record) {
            // Normalize quantity field
            if (is_numeric($record['quantity'])) {
                $record['quantity'] = (int)$record['quantity'];
            }

            // Validate individual record
            $validator = validator($record, [
                'name' => 'required|max:150',
                'slug' => 'required|max:150',
                'vh_st_store_id' => 'required',
                'taxonomy_id_product_type' => 'required',
                'taxonomy_id_product_status' => 'required',
                'quantity' => 'nullable|integer|min:0',
                'launch_at' => 'required_without_all:quantity,available_at',
                'available_at' => 'required_without_all:quantity,launch_at',
            ], [
                'name.required' => 'The Name field is required.',
                'slug.required' => 'The Slug field is required.',
                'vh_st_store_id.required' => 'The Store field is required.',
                'taxonomy_id_product_type.required' => 'The Product Type field is required.',
                'taxonomy_id_product_status.required' => 'The Product Status field is required.',
            ]);

            if ($validator->fails()) {
                $result['invalid_records_count']++;
                $record['reason'] = $validator->errors()->all();
                $record = $this->mapTaxonomyFields($record);
                $result['invalid_records'][] = $record;
                continue;
            }

            // Check for existing inventory
            $existingProduct = Product::where('id', $record['id'])
                ->where('id', $record['id'])
                ->first();

            if ($existingProduct) {
                if ($is_override) {
                    $this->updateProducts($existingProduct, $record);
                    $result['override_records_count']++;
                } else {
                    $result['invalid_records_count']++;
                    $record['reason'] = ['Duplicate product with the same part number and OEM number exists.'];
                    $record = $this->mapTaxonomyFields($record);
                    $result['invalid_records'][] = $record;
                }
                continue;
            }

            // Create new product
            $record['uuid'] = \Str::uuid();
            $product = Product::create($record);
            if ($product) {
                $result['imported_records']++;
            }
        }

        // Prepare CSV for invalid records
        foreach ($result['invalid_records'] as $invalidRecord) {
            $reasons = implode('; ', $invalidRecord['reason']);
            unset($invalidRecord['reason']);
            $invalidRecord['reason'] = $reasons;
            $content .= implode(',', array_values($invalidRecord)) . "\n";
        }

        // Return response with CSV
        $response = [
            'success' => true,
            'data' => $result,
            'messages' => ['Imported successfully.'],
            'csv_content' => [
                'headers' => [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="invalid_records.csv"',
                ],
                'content' => $content,
            ],
        ];

        return response()->json($response);
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

    public function createSupplier(Request $request)
    {
        $inputs = $request->all();



        //check if supplier name already exist
        $item = Supplier::where([
            'name' => $inputs['name'],
        ])->withTrashed()->first();

        if ($item) {
            $response['data'] = Supplier::find($item->id);
            $response['success'] = true;
            return $response;
        }

        //check if supplier name already exist

        $item = Supplier::where([
            'name' => $inputs['slug'],
        ])->withTrashed()->first();

        if ($item) {
            $response['data'] = Supplier::find($item->id);
            $response['success'] = true;
            return $response;
        }

       $supplier = New Supplier();

        $supplier->name = $inputs['name'];
        $supplier->slug = $inputs['slug'];
        $supplier->is_active = $inputs['is_active'];
        $supplier->save();
        $response = [];
        if($supplier->id)
        {
            $response['data'] = Supplier::find($supplier->id);
            $response['success'] = true;
            $response['messages'][] = trans("vaahcms-general.saved_successfully");

        }
        else{

            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.something_went_wrong");
        }
        return $response;

    }

    //------------------------------

    public function getSampleFile(Request $request)
    {

        // get all the column names from the database
        $all_columns = (new Product())->getTableColumns();

        // list of columns that we want to exclude
        $excluded_columns = ['id','uuid','parts_link_id','meta','created_by','updated_by','deleted_by','created_at','updated_at','deleted_at'];

        // columns for which we will generate data
        $columns = array_diff($all_columns, $excluded_columns);


        // modified headers
        $custom_headers = [
            'sm_supplier_id' => 'Supplier',
            'taxonomy_id_make' => 'Make',
            'taxonomy_id_model' => 'Model',
            'taxonomy_id_year' => 'Year',
            'taxonomy_id_body_style' => 'Body Style',
            'taxonomy_id_first_year' => 'First Year',
            'taxonomy_id_last_year' => 'Last Year',
            'taxonomy_id_section' => 'Section',
            'taxonomy_id_part_type' => 'Part Type',
            'taxonomy_id_class' => 'Class',
            'taxonomy_id_location' => 'Location',
            'taxonomy_id_status' => 'Status',
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
        for($i=0;$i< $number_of_records;$i++){
            $data=[];
            foreach ($columns as $column) {
                switch ($column) {

                    case 'part_number':
                    case 'oem_number':
                        $data[$column] = mt_rand(10000, 99999) . chr(mt_rand(65, 90)) . mt_rand(1000, 9999);
                        break;
                    case 'description':
                        $max_chars = rand(5,200);
                        $data[$column] =  $faker->text($max_chars);
                        break;
                    case 'sm_supplier_id':
                        $suppliers = Supplier::where('is_active',1);
                        $supplier_names= $suppliers->pluck('name')->toArray();
                        if($supplier_names)
                        {
                            $supplier = $supplier_names[array_rand($supplier_names)];
                            $data['sm_supplier_id'] = $supplier;
                        }
                        else{
                            $number_of_characters = rand(5,10);
                            $data['sm_supplier_id']=$faker->text($number_of_characters);
                        }
                        break;

                    case 'taxonomy_id_make':
                        $makes = Taxonomy::getTaxonomyByType('make');
                        $make_names = $makes->pluck('name')->toArray();
                        $data['taxonomy_id_make'] = null;
                        if($make_names)
                        {
                            $make = $make_names[array_rand($make_names)];
                            $data['taxonomy_id_make'] = $make;

                        }

                        break;

                    case 'taxonomy_id_model':
                        $models = Taxonomy::getTaxonomyByType('model');
                        $model_names = $models->pluck('name')->toArray();
                        $data['taxonomy_id_model'] = null;
                        if($model_names)
                        {
                            $model = $model_names[array_rand($model_names)];
                            $data['taxonomy_id_model'] = $model;
                        }

                        break;

                    case 'taxonomy_id_year':
                        $years = Taxonomy::getTaxonomyByType('year');
                        $year_names = $years->pluck('name')->toArray();
                        $data['taxonomy_id_year'] = null;
                        if($year_names)
                        {
                            $year = $year_names[array_rand($year_names)];
                            $data['taxonomy_id_year'] = $year;
                        }

                        break;

                    case 'taxonomy_id_body_style':
                        $body_styles = Taxonomy::getTaxonomyByType('body-style');
                        $body_style_names = $body_styles->pluck('name')->toArray();
                        $data['taxonomy_id_body_style'] = null;
                        if($body_style_names)
                        {
                            $body_style = $body_style_names[array_rand($body_style_names)];
                            $data['taxonomy_id_body_style'] = $body_style;
                        }

                        break;

                    case 'taxonomy_id_first_year':
                        $first_years = Taxonomy::getTaxonomyByType('first-year');
                        $first_year_names = $first_years->pluck('name')->toArray();
                        $data['taxonomy_id_first_year'] = null;
                        if($first_year_names)
                        {
                            $first_year = $first_year_names[array_rand($first_year_names)];
                            $data['taxonomy_id_first_year'] = $first_year;
                        }

                        break;
                    case 'taxonomy_id_last_year':
                        $last_years = Taxonomy::getTaxonomyByType('last-year');
                        $last_year_names = $last_years->pluck('name')->toArray();
                        $data['taxonomy_id_last_year'] = null;
                        if($last_year_names)
                        {
                            $last_year = $last_year_names[array_rand($last_year_names)];
                            $data['taxonomy_id_last_year'] = $last_year;
                        }

                        break;

                    case 'taxonomy_id_section':
                        $sections = Taxonomy::getTaxonomyByType('section');
                        $section_names = $sections->pluck('name')->toArray();
                        $data['taxonomy_id_section'] = null;
                        if($section_names)
                        {
                            $section = $section_names[array_rand($section_names)];
                            $data['taxonomy_id_section'] = $section;
                        }

                        break;

                    case 'taxonomy_id_part_type':
                        $part_types = Taxonomy::getTaxonomyByType('part-type');
                        $part_type_names = $part_types->pluck('name')->toArray();
                        $data['taxonomy_id_part_type'] = null;
                        if($part_type_names)
                        {
                            $part_type = $part_type_names[array_rand($part_type_names)];
                            $data['taxonomy_id_part_type'] = $part_type;
                        }

                        break;


                    case 'taxonomy_id_class':
                        $classes = Taxonomy::getTaxonomyByType('class');
                        $class_names = $classes->pluck('name')->toArray();
                        $data['taxonomy_id_class'] = null;
                        if($class_names)
                        {
                            $class = $class_names[array_rand($class_names)];
                            $data['taxonomy_id_class'] = $class;
                        }

                        break;

                    case 'taxonomy_id_location':
                        $locations = Taxonomy::getTaxonomyByType('location');
                        $location_names = $locations->pluck('name')->toArray();
                        $data['taxonomy_id_location'] = null;
                        if($location_names)
                        {
                            $location = $location_names[array_rand($location_names)];
                            $data['taxonomy_id_location'] = $location;
                        }

                        break;

                    case 'quantity':
                    case 'reorder_quantity':
                    case 'reorder_point':
                        $data[$column] = rand(100,99999);
                        break;

                    case 'list_price':
                    case 'sales_price':
                    case 'cubic_feet':
                        $data[$column] = $faker->randomFloat($nbMaxDecimals = 2, $min = 1, $max = 999999);
                        break;

                    case 'taxonomy_id_status':
                        $data[$column] = 'New';
                        break;

                    case 'is_active':
                        $data[$column] = rand(0,1);
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
            'Content-Disposition' => 'attachment; filename="sample-inventories.csv"',
        ];

        return new Response($content, 200, $response_headers);

    }


}
