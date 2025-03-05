<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use VaahCms\Modules\Store\Models\Store;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
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
            $data['stores'] = self::getStores();


            $response['success'] = true;
            $response['data'] = $data;

        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response;
    }

    public function getStores(){
        $stores = Store::where('is_active',1)->get(['id','name', 'slug', 'is_default']);
        if ($stores){
            return [
                'active_stores' =>$stores
            ];
        }else{
            return [
                'active_stores' => null
            ];
        }
    }

}
