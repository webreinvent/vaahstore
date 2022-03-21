<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Store;


class StoresController extends Controller
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

        $model = new Store();
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
        return Store::getList($request);
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        return Store::updateList($request);
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        return Store::deleteList($request);
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        return Store::createItem($request);
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id): array
    {
        return Store::getItem($id);
    }

    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        return Store::updateItem($request,$id);
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        return Store::deleteItem($request,$id);
    }
    //----------------------------------------------------------
    //----------------------------------------------------------


}
