<?php  namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ExtendController extends Controller
{

    public static $link;
    //----------------------------------------------------------
    public function __construct()
    {
        $base_url = route('vh.backend.store')."#/";
        $link = $base_url;
        self::$link = $link;
    }
    //----------------------------------------------------------
    public static function topLeftMenu()
    {
        $links = [];

        $response['status'] = 'success';
        $response['data'] = $links;

        return $response;

    }
    //----------------------------------------------------------
    public static function topRightUserMenu()
    {
        $links = [];

        $response['status'] = 'success';
        $response['data'] = $links;

        return $response;
    }
    //----------------------------------------------------------
    public static function sidebarMenu()
    {
        $links = [];

        $links[0] = [
            'link' => route('vh.backend.store'),
            'icon'=> 'table',
            'label'=> 'Store'
        ];

        $response['status'] = 'success';
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------

}
