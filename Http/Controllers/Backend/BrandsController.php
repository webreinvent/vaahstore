<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Brand;


class BrandsController extends Controller
{


    //----------------------------------------------------------
    public function __construct()
    {

    }

    //----------------------------------------------------------

    public function getAssets(Request $request)
    {

        $data = [];

        $data['permission'] = [];

        $data['fillable']['except'] = [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];

        $model = new Brand();
        $fillable = $model->getFillable();
        $data['fillable']['columns'] = array_diff(
            $fillable, $data['fillable']['except']
        );

        foreach ($data['fillable']['columns'] as $column)
        {
            $data['empty_item'][$column] = null;
        }

        $data['actions'] = [];

        $response['success'] = true;
        $response['data'] = $data;

        return $response;
    }

    //----------------------------------------------------------
    public function getList(Request $request)
    {
        return Brand::getList($request);
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        return Brand::updateList($request);
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        return Brand::deleteList($request);
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        return Brand::createItem($request);
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        return Brand::getItem($id);
    }

    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        return Brand::updateItem($request,$id);
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        return Brand::deleteItem($request,$id);
    }
    //----------------------------------------------------------
    //----------------------------------------------------------


}
