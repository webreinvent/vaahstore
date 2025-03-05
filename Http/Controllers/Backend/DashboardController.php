<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use VaahCms\Modules\Store\Models\Store;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use WebReinvent\VaahCms\Models\Permission;

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
//dd(12);
            $data = [];
            $data['active_permissions'] = Permission::getActiveItems();
            Permission::syncPermissionsWithRoles();
            $data['rows'] = config('vaahcms.per_page');
            $data['stores'] = $this->getStores();


            $response['success'] = true;
            $response['data']= $data;

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
        $stores = Store::where('is_active',1)
            ->orderByDesc('is_default')->get(['id','name', 'slug', 'is_default']);
        if ($stores){
            return [
                $stores
            ];
        }else{
            return [
                 null
            ];
        }
    }

}
