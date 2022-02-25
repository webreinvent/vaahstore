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
    public function createItem(Request $request)
    {
        return Store::createItem($request);
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        return Store::getItem($id);
    }

    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        return Store::updateItem($request,$id);
    }
    //----------------------------------------------------------
    public function postActions(Request $request, $action)
    {
        $rules = array(
            'inputs' => 'required',
        );

        $validator = \Validator::make( $request->all(), $rules);
        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['status'] = 'failed';
            $response['errors'] = $errors;
            return $response;
        }

        $response = [];

        $response['success'] = true;

        $inputs = $request->all();

        switch ($action)
        {

            //------------------------------------
            case 'bulk-change-status':

                $response = Store::bulkStatusChange($request);

                break;
            //------------------------------------
            case 'bulk-trash':

                $response = Store::bulkTrash($request);

                break;
            //------------------------------------
            case 'bulk-restore':

                $response = Store::bulkRestore($request);

                break;

            //------------------------------------
            case 'bulk-delete':

                $response = Store::bulkDelete($request);

                break;
            //------------------------------------
            //------------------------------------

        }

        return $response;

    }
    //----------------------------------------------------------
    //----------------------------------------------------------


}
