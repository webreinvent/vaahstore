<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Store;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahExtend\Facades\VaahCountry;


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
            'is_multi_currency',
            'is_multi_lingual',
            'is_multi_vendor',
            'is_default',
            'is_active',
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

        $data['taxonomy']['status'] = Taxonomy::getTaxonomyByType('store-status');

        $data['currencies'] = $this->getCurrencies();
        $data['languages'] = $this->getLanguages();

        $data['empty_item']['is_multi_currency'] = 0;
        $data['empty_item']['is_multi_lingual'] = 0;
        $data['empty_item']['is_multi_vendor'] = 0;
        $data['empty_item']['is_default'] = 0;
        $data['empty_item']['is_active'] = 1;
        $data['actions'] = [];

        $response['success'] = true;
        $response['data'] = $data;

        return $response;
    }

    //----------------------------------------------------------
    public function getCurrencies()
    {
        if (method_exists('VaahCountry', 'getListWithCurrency')){
            return VaahCountry::getListWithCurrency();
        }else{
            return array(
                array("name" => "Australian Dollar", "code" => "AUD", "symbol" => "$"),
                array("name" => "Barbadian Dollar", "code" => "BBD", "symbol" => "Bds$"),
                array("name" => "Bitcoin", "code" => "BTC", "symbol" => "฿"),
                array("name" => "Cambodian Riel", "code" => "KHR", "symbol" => "KHR"),
                array("name" => "Danish Krone", "code" => "DKK", "symbol" => "Kr."),
                array("name" => "Indian Rupee", "code" => "INR", "symbol" => "₹")
            );
        }
    }

    //----------------------------------------------------------
    public function getLanguages()
    {
        if (method_exists('VaahCountry', 'getListWithLanguage')){
            return VaahCountry::getListWithCurrency();
        }else{
            return array(
                array("name" => "Acoli"),
                array("name" => "Adangme"),
                array("name" => "Adyghe; Adygei"),
                array("name" => "Blin; Bilin"),
                array("name" => "Western Frisian"),
                array("name" => "Gbaya"),
                array("name" => "Haida")
            );
        }
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
    public function itemAction(Request $request,$id,$action)
    {
        return Store::itemAction($request,$id,$action);
    }
    //----------------------------------------------------------


}
