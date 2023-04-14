<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Attribute;
use VaahCms\Modules\Store\Models\AttributeGroup;
use VaahCms\Modules\Store\Models\AttributeValue;
use VaahCms\Modules\Store\Models\Brand;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\Store;
use WebReinvent\VaahCms\Entities\Taxonomy;


class ProductsController extends Controller
{


    //----------------------------------------------------------
    public function __construct()
    {

    }

    //----------------------------------------------------------

    public function getAssets(Request $request)
    {

        try{

            $data = [];

            $data['permission'] = [];
            $data['rows'] = config('vaahcms.per_page');

            $data['fillable']['except'] = [
                'uuid',
                'created_by',
                'updated_by',
                'deleted_by',
            ];

            $model = new Product();
            $fillable = $model->getFillable();
            $data['fillable']['columns'] = array_diff(
                $fillable, $data['fillable']['except']
            );

            foreach ($fillable as $column)
            {
                $data['empty_item'][$column] = null;
            }
            $data['empty_item']['in_stock'] = 0;
            $data['empty_item']['quantity'] = 0;
            $data['empty_item']['is_active'] = 1;
            $data['empty_item']['product_variation'] = null;
            $data['empty_item']['all_variation'] = [];

            $data['actions'] = [];
            $get_data = self::getData();
            $data = array_merge($data, $get_data);
            $default_store = [];
            foreach($data['store'] as $k=>$arr)
            {
                if($arr['is_default']==1)
                {
                    $default_store['id'] = $arr->id;
                    $default_store['name'] = $arr->name;
                    $default_store['is_default'] = $arr->is_default;
                }
            }
            $data['empty_item']['vh_st_store_id'] = $default_store;
            $default_brand = [];
            foreach($data['brand'] as $l=>$brand)
            {
                if($brand['is_default']==1)
                {
                    $default_brand['id'] = $brand->id;
                    $default_brand['name'] = $brand->name;
                    $default_brand['is_default'] = $brand->is_default;
                }
            }
            $data['empty_item']['vh_st_brand_id'] = $default_brand;

            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return $response;
    }
    //------------------------Get data for dropdown----------------------------------
    public function getData(){
        try{
            $data['brand']=Brand::select('id','name','slug', 'is_default')->where('is_active',1)->get();
            $data['store']=Store::where('is_active',1)->get();

            $data['status'] = Taxonomy::getTaxonomyByType('product-status');
            $data['type'] = Taxonomy::getTaxonomyByType('product-types');

            return $data;
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }

    //----------------------------------------------------------
    public function getAttributeList(Request $request){
        $input = $request->all();
        switch ($input['attribute_type']){
            case 'attribute':
                $item = Attribute::get(['name', 'id', 'type']);
                break;
            case 'attribute_group':
                if ($input['get_attribute_from_group']){
                    $record = AttributeGroup::where('id', $input['selected_attribute']['id'])->with(['attributesList'])->get();
                    if ($record) {
                        $item = $record->toArray()[0]['attributes_list'];
                    }
                }else{
                    $item = AttributeGroup::get(['name', 'id']);
                }
                break;
        }

        return [
            'data' => $item
        ];
    }

    //----------------------------------------------------------
    public function getAttributeValue(Request $request){
        $input = $request->all();

        $result = [];

        foreach ($input['attribute'] as $key=>$value){

            $att = Attribute::find($value['id']);

            $item = AttributeValue::where('vh_st_attribute_id', $value['id'])->get(['id', 'vh_st_attribute_id', 'value']);

            if ($item){
                $record = $item->toArray();

                foreach ($record as $k=>$v){
                    $result[$value['name']][$k] = $v;
                }
            }

        }

        $product_detail = Product::find($input['product_id']);

        if ($input['method'] == 'generate'){
            $combination = [];

            // making all possible combination of attribute values
            foreach ($result as $k => $v){
                $temp_result = [];
                if(count($combination) == 0){
                    foreach ($v as $key => $value){
                        $temp = [];
                        $temp[$k]['value'] = $value['value'];
                        $temp[$k]['vh_st_attribute_id'] = $value['vh_st_attribute_id'];
                        $temp[$k]['vh_st_attribute_values_id'] = $value['id'];
                        array_push($temp_result, $temp);
                    }
                    $combination = $temp_result;
                }else{
                    foreach ($v as $key => $value){
                        foreach ($combination as $n_key => $n_value){
                            $temp = [];
                            $temp[$k]['value'] = $value['value'];
                            $temp[$k]['vh_st_attribute_id'] = $value['vh_st_attribute_id'];
                            $temp[$k]['vh_st_attribute_values_id'] = $value['id'];
                            foreach ($n_value as $c_key => $c_value){
                                $temp[$c_key] = $c_value;
                            }

                            array_push($temp_result, $temp);
                        }
                    }
                    $combination = $temp_result;
                }
            }

            $structured_variation = [];
            $all_attribute_name = [];
            // adding additional structure for variation table
            foreach ($combination as $k => $v){
                if ($k == 0){
                    $all_attribute_name = array_keys($v);
                }
//                $v['variation_name'] = 'variation name '.$k;
                $value_name = [];
                foreach ($all_attribute_name as $key=>$value){
                    array_push($value_name, $v[$value]['value']);
                }

                $v['variation_name'] = $product_detail->name.'-'.implode('-', $value_name);
                $v['is_selected'] = false;
                $v['media'] = 1;
                array_push($structured_variation, $v);
            }

            return [
                'data' => [
//                    'combination' => $combination,
                    'structured_variation' => $structured_variation,
                    'all_attribute_name' => $all_attribute_name
                ]
            ];
        } else{
            return [
                'data' => [
                    'all_attribute_name' => array_keys($result),
                    'create_attribute_values' => $result
                ]
            ];
        }
    }

    //----------------------------------------------------------
    public function getList(Request $request)
    {
        try{
            return Product::getList($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        try{
            return Product::updateList($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function listAction(Request $request, $type)
    {


        try{
            return Product::listAction($request, $type);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        try{
            return Product::deleteList($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        try{
            return Product::createItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function createVariation(Request $request)
    {
        try{
            return Product::createVariation($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        try{
            return Product::getItem($id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        try{
            return Product::updateItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        try{
            return Product::deleteItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------
    public function itemAction(Request $request,$id,$action)
    {
        try{
            return Product::itemAction($request,$id,$action);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
                return $response;
            }
        }
    }
    //----------------------------------------------------------


}
