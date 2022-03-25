<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Store;
use VaahCms\Modules\Store\Models\Vendor;


class VendorsController extends Controller
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

        $model = new Vendor();
        $fillable = $model->getFillable();
        $data['fillable']['columns'] = array_diff(
            $fillable, $data['fillable']['except']
        );

        foreach ($data['fillable']['columns'] as $column)
        {
            $data['empty_item'][$column] = null;
        }

        $data['stores'] = Store::where('is_active', 1)->get();
        $data['actions'] = [];

        $response['success'] = true;
        $response['data'] = $data;

        return $response;
    }

    //----------------------------------------------------------
    public function getList(Request $request)
    {
        return Vendor::getList($request);
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        return Vendor::updateList($request);
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        return Vendor::deleteList($request);
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        return Vendor::createItem($request);
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        return Vendor::getItem($id);
    }

    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        return Vendor::updateItem($request,$id);
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        return Vendor::deleteItem($request,$id);
    }
    //----------------------------------------------------------
    //----------------------------------------------------------


}
