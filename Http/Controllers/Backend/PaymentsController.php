<?php namespace VaahCms\Modules\Store\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Store\Models\Payment;
use VaahCms\Modules\Store\Models\PaymentMethod;
use VaahCms\Modules\Store\Models\Store;


class PaymentsController extends Controller
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

            $data['fillable']['columns'] = Payment::getFillableColumns();
            $data['fillable']['except'] = Payment::getUnFillableColumns();
            $data['empty_item'] = Payment::getEmptyItem();
            $data['payment_methods'] = self::getPaymentMethods();
            $currency_symbol = Store::where('is_default', 1)
                ->with('defaultCurrency')
                ->first()
                ?->defaultCurrency?->symbol;

            $data['store_default_currency'] = $currency_symbol;

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
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response;
    }

    //----------------------------------------------------------
    public static function getPaymentMethods(){
        $payment_methods = PaymentMethod::where(['is_active'=>1,'deleted_at'=>null])->get();
        return $payment_methods ?? null;
    }
    //----------------------------------------------------------
    public function getList(Request $request)
    {
        try{
            return Payment::getList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateList(Request $request)
    {
        try{
            return Payment::updateList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");

            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function listAction(Request $request, $type)
    {


        try{
            return Payment::listAction($request, $type);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;

        }
    }
    //----------------------------------------------------------
    public function deleteList(Request $request)
    {
        try{
            return Payment::deleteList($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function fillItem(Request $request)
    {
        try{
            return Payment::fillItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function createItem(Request $request)
    {
        try{
            return Payment::createItem($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getItem(Request $request, $id)
    {
        try{
            return Payment::getItem($id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function updateItem(Request $request,$id)
    {
        try{
            return Payment::updateItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function deleteItem(Request $request,$id)
    {
        try{
            return Payment::deleteItem($request,$id);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function itemAction(Request $request,$id,$action)
    {
        try{
            return Payment::itemAction($request,$id,$action);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function searchOrders(Request $request)
    {
        try{
            return Payment::searchOrders($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getOrdersForFilter(Request $request)
    {
        try{
            return Payment::getOrdersForFilter($request);
        }catch (\Exception $e){
            $response = [];
            $response['success'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
            return $response;
        }
    }
    //----------------------------------------------------------
    public function getOrdersByName(Request $request)
    {
        try{
            return Payment::getOrdersByName($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //----------------------------------------------------------

    public function paymentMethodsPieChartData(Request $request)
    {
        try{
            return Payment::paymentMethodsPieChartData($request);
        }catch (\Exception $e){
            $response = [];
            $response['status'] = 'failed';
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
                return $response;
            }
        }
    }
    //----------------------------------------------------------


}
