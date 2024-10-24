<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use VaahCms\Modules\Store\Models\PaymentMethod;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Order extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_orders';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'order_shipment_status',
        'order_status',
        'status_notes',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    //-------------------------------------------------
    protected $fill_except = [

    ];

    //-------------------------------------------------
    protected $appends = [
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
    }

    //-------------------------------------------------
    public static function getUnFillableColumns()
    {
        return [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }

    //-------------------------------------------------

    public static function getFillableColumns()
    {
        $model = new self();
        $except = $model->fill_except;
        $fillable_columns = $model->getFillable();
        $fillable_columns = array_diff(
            $fillable_columns, $except
        );
        return $fillable_columns;
    }

    //-------------------------------------------------
    public static function getEmptyItem()
    {
        $model = new self();
        $fillable = $model->getFillable();
        $empty_item = [];
        foreach ($fillable as $column) {
            $empty_item[$column] = null;
        }
        $empty_item['is_active'] = null;
        $empty_item['is_active_order_item'] = null;
        $empty_item['is_paid'] = null;
        $empty_item['paid'] = 0;
        $empty_item['amount'] = 0;
        $empty_item['paid'] = 0;
        $empty_item['delivery_fee'] = 0;
        $empty_item['taxes'] = 0;
        $empty_item['discount'] = 0;
        $empty_item['paid'] = 0;
        $empty_item['payable'] = 0;
        $empty_item['user'] = null;
        $empty_item['types'] = null;
        $empty_item['product'] = null;
        $empty_item['product_variation'] = null;
        $empty_item['vendor'] = null;
        $empty_item['customer_group'] = null;
        $empty_item['status_order_items'] = null;
        $empty_item['status_notes_order_item'] = null;
        $empty_item['taxonomy_id_order_items_types'] = null;
        $empty_item['vh_st_product_id'] = null;
        $empty_item['vh_st_product_variation_id'] = null;
        $empty_item['vh_st_vendor_id'] = null;
        $empty_item['vh_st_customer_group_id'] = null;
        $empty_item['taxonomy_id_order_items_status'] = null;
        $empty_item['is_invoice_available'] = null;
        $empty_item['invoice_url'] = null;
        $empty_item['tracking'] = null;

        return $empty_item;
    }

    //-------------------------------------------------

    public function createdByUser()
    {
        return $this->belongsTo(User::class,
            'created_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function updatedByUser()
    {
        return $this->belongsTo(User::class,
            'updated_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, 'id', 'vh_st_payment_method_id')->select('id', 'name', 'slug');
    }

    //-------------------------------------------------
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'vh_st_order_id', 'id');
    }

    //-------------------------------------------------

    public function status()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_order_status')->select('id', 'name', 'slug');
    }

    public function orderPaymentStatus()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_payment_status')->select('id', 'name', 'slug');
    }

    //-------------------------------------------------
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'vh_user_id')->select('id', 'first_name', 'email', 'phone', 'display_name');
    }

    //-------------------------------------------------
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'vh_st_order_payments', 'vh_st_order_id', 'vh_st_payment_id')
            ->withPivot('payment_amount', 'payable_amount', 'remaining_payable_amount', 'created_at');
    }

    //-------------------------------------------------
    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class, 'vh_st_order_id');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class,
            'deleted_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------
    public function scopeBetweenDates($query, $from, $to)
    {

        if ($from) {
            $from = \Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($to) {
            $to = \Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at', [$from, $to]);
    }

    //-------------------------------------------------


    //-------------------------------------------------

    public function scopeGetSorted($query, $filter)
    {

        if (!isset($filter['sort'])) {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if (!$direction) {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }

    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if (!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        ) {
            return $query;
        }
        $is_active = $filter['is_active'];

        if ($is_active === 'true' || $is_active === true) {
            return $query->where('is_active', 1);
        } else {
            return $query->where(function ($q) {
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }

    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if (!isset($filter['trashed'])) {
            return $query;
        }
        $trashed = $filter['trashed'];

        if ($trashed === 'include') {
            return $query->withTrashed();
        } else if ($trashed === 'only') {
            return $query->onlyTrashed();
        }

    }

    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if (!isset($filter['q'])) {
            return $query;
        }
        $search = $filter['q'];
        $query->where(function ($q) use ($search) {
            $q->where('id', 'LIKE', '%' . $search . '%')
                ->orwhereHas('user', function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', '%' . $search . '%');
                });
        });

    }

    //-------------------------------------------------
    public function scopePaymentStatusFilter($query, $filter)
    {

        if (!isset($filter['payment_status'])
            || is_null($filter['payment_status'])
            || $filter['payment_status'] === 'null'
        ) {
            return $query;
        }

        $status = $filter['payment_status'];

        $query->whereHas('orderPaymentStatus', function ($query) use ($status) {
            $query->whereIn('slug', $status);
        });

    }

    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status', 'paymentMethod', 'user', 'orderPaymentStatus')->withCount('items');
        $list->isActiveFilter($request->filter);
        $list->paymentStatusFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);

        $rows = config('vaahcms.per_page');

        if ($request->has('rows')) {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);

        $response['success'] = true;
        $response['data'] = $list;

        return $response;


    }

    //-------------------------------------------------
    public static function updateList($request)
    {

        $inputs = $request->all();
        $rules = array(
            'type' => 'required',
        );

        $messages = array(
            'type.required' => 'Action type is required',
        );


        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        if (isset($inputs['items'])) {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();
        }


        $items = self::whereIn('id', $items_id)
            ->withTrashed();

        switch ($inputs['type']) {
            case 'deactivate':
                $items->update(['is_active' => null]);
                break;
            case 'activate':
                $items->update(['is_active' => 1]);
                break;
            case 'trash':
                self::whereIn('id', $items_id)->delete();
                $user_id = auth()->user()->id;
                $items->update(['deleted_by' => $user_id]);
                break;
            case 'restore':
                self::whereIn('id', $items_id)->restore();
                $items->update(['deleted_by' => null]);
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = 'Action was successful.';

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => 'Action type is required',
            'items.required' => 'Select items',
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::with('items')->whereIn('id', $items_id)->each(function ($item) {
            $item->items()->forceDelete();
        });
        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = 'Action was successful.';

        return $response;
    }

    //-------------------------------------------------
    public static function listAction($request, $type): array
    {
        $inputs = $request->all();

        if (isset($inputs['items'])) {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();

            $items = self::whereIn('id', $items_id)
                ->withTrashed();
        }

        $list = self::query();

        if ($request->has('filter')) {
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {
            case 'deactivate':
                if ($items->count() > 0) {
                    $items->update(['is_active' => null]);
                }
                break;
            case 'activate':
                if ($items->count() > 0) {
                    $items->update(['is_active' => 1]);
                }
                break;
            case 'trash':
                if (isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->delete();
                    $items->update(['deleted_by' => auth()->user()->id]);
                }

                break;
            case 'restore':
                if (isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                    $items->update(['deleted_by' => null]);
                }
                break;
            case 'delete':
                if (isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->forceDelete();
                    OrderItem::deleteOrder($items_id);
                }
                break;
            case 'activate-all':
                $list->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->update(['is_active' => null]);
                break;
            case 'trash-all':
                $user_id = auth()->user()->id;
                $list->update(['deleted_by' => $user_id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->get()
                    ->each->restore();
                break;
            case 'delete-all':
                $items = self::withTrashed()->get();
                foreach ($items as $item) {
                    $item->items()->forceDelete();
                }
                $list->forceDelete();
                break;

            case 'create-100-records':
            case 'create-1000-records':
            case 'create-5000-records':
            case 'create-10000-records':

                if (!config('store.is_dev')) {
                    $response['success'] = false;
                    $response['errors'][] = 'User is not in the development environment.';

                    return $response;
                }

                preg_match('/-(.*?)-/', $type, $matches);

                if (count($matches) !== 2) {
                    break;
                }

                self::seedSampleItems($matches[1]);
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = 'Action was successful.';

        return $response;
    }

    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'status', 'paymentMethod', 'user', 'orderPaymentStatus', 'payments.createdByUser'])->withCount('items')
            ->withTrashed()
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: ' . $id;
            return $response;
        }


        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }

    //-------------------------------------------------
    public static function updateItem($request, $id)
    {

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->is_paid = $inputs['paid'] > 0 ? 1 : 0;
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------

    public static function deleteItem($request, $id)
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        $item->items()->forceDelete();
        $item->forceDelete();


        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been deleted';

        return $response;
    }

    //-------------------------------------------------

    public static function itemAction($request, $id, $type): array
    {

        switch ($type) {
            case 'activate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => 1]);
                break;
            case 'deactivate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => null]);
                break;
            case 'trash':
                self::where('id', $id)
                    ->withTrashed()
                    ->delete();
                $item = self::where('id', $id)->withTrashed()->first();
                $item->deleted_by = auth()->user()->id;
                $item->save();
                break;
            case 'restore':
                self::where('id', $id)
                    ->withTrashed()
                    ->restore();
                $item = self::where('id', $id)->withTrashed()->first();
                $item->deleted_by = null;
                $item->save();
                break;
        }

        return self::getItem($id);
    }

    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = validator($inputs, [

            'status_notes' => [
                'required',
                'max:100'
            ],
        ],
            [
                'status_notes.required' => 'The order  note field is required',
            ]
        );

        if ($rules->fails()) {
            return [
                'success' => false,
                'errors' => $rules->errors()->all()
            ];
        }
        $rules = $rules->validated();

        return [
            'success' => true,
            'data' => $rules
        ];

    }

    //-------------------------------------------------
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)
            ->withTrashed()
            ->first();
        return $item;
    }

    //-------------------------------------------------

    public static function seedSampleItems($records = 100)
    {

        $i = 0;

        while ($i < $records) {
            $inputs = self::fillItem(false);

            $item = new self();
            $item->fill($inputs);
            $item->save();

            $i++;

        }

    }

    //-------------------------------------------------

    public static function fillItem($is_response_return = true)
    {
        $request = new Request([
            'model_namespace' => self::class,
            'except' => self::getUnFillableColumns()
        ]);
        $fillable = VaahSeeder::fill($request);
        if (!$fillable['success']) {
            return $fillable;
        }
        $inputs = $fillable['data']['fill'];
        $faker = Factory::create();

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */


        // fill the user field with any random user here


        if (!$is_response_return) {
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-----------------validation for product price--------------------------------



    //-------------------------------------------------

    public static function getShippedOrderItems($id)
    {
        $order_items = OrderItem::where('vh_st_order_id', $id)
            ->with('ProductVariation','product','vendor')
            ->get();

        $total_quantities = [];

        $shipment_items = ShipmentItem::where('vh_st_order_id', $id)
            ->get()
            ->groupBy('vh_st_order_item_id');

        foreach ($shipment_items as $item_id => $items) {
            $total_quantities[$item_id] = $items->sum('quantity');
        }

        foreach ($order_items as $order_item) {
            $order_item->shipped_quantity = $total_quantities[$order_item->id] ?? 0;
        }
        return [
            'success' => true,
            'data' => $order_items
        ];
    }

    //-------------------------------------------------

    public static function fetchOrdersCountChartData(Request $request)
    {
        $date_column = 'created_at';
        $count = 'COUNT';
        $group_by_column = 'DATE_FORMAT(created_at, "%m")';

        // Query Order model
        $list = Order::query();

        // Apply filters to the list
        $filtered_data = self::appliedFilters($list, $request);

        // Query for chart data
        $order_data_query = $filtered_data->selectRaw("$group_by_column as month")
            ->selectRaw("$count($date_column) as total_count") // Total orders count
            ->selectRaw("SUM(CASE WHEN order_status = 'Completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("SUM(CASE WHEN order_status != 'Completed' THEN 1 ELSE 0 END) as pending_count");

        $chart_data = $order_data_query->groupBy('month')->orderBy('month')->get();

        $total_orders = $chart_data->sum('total_count');

        $data = [
            ['name' => 'Total Orders', 'data' => array_fill(0, 12, 0)],
            ['name' => 'Completed Orders', 'data' => array_fill(0, 12, 0)],
        ];

        $labels = [];
        for ($month = 1; $month <= 12; $month++) {
            $labels[] = date('F', strtotime("2024-$month-01"));
        }

        foreach ($chart_data as $item) {
            $month_index = (int)$item->month - 1;
            $data[0]['data'][$month_index] = $item->total_count;
            $data[1]['data'][$month_index] = $item->completed_count;
        }

        return [
            'data' => [
                'chart_series' => $data,
            ],
            'chart_options' => [
                'chart' => [
                    'id' => 'dynamic-chart',
                    'background' => '#fff',
                    'toolbar' => ['show' => true],
                    'zoom' => ['enabled' => false],
                ],
                'stroke' => [
                    'curve' => 'smooth',
                ],
                'xaxis' => [
                    'type' => 'category',
                    'categories' => $labels,

                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'Orders Count',
                        'color' => '#008FFB',
                        'rotate' => -90,
                        'style' => [
                            'fontFamily' => 'Arial, sans-serif',
                            'fontWeight' => 'bold',
                        ],
                    ],
                ],
                'title' => [
                    'text' => 'Monthly Orders',
                    'align' => 'center',
                ],
                'subtitle' => [
                    'text' => 'Total Orders ' .  $total_orders,
                    'align' => 'center',
                    'style' => [
                        'fontSize' => '12px',
                        'color' => '#666',
                        'fontFamily' => 'Arial, sans-serif',
                    ],
                ],
                'legend' => [
                    'position' => 'top',
                    'horizontalAlign' => 'center',
                    'onItemClick' => [
                        'toggleDataSeries' => true,
                    ],
                ],
                'grid' => [
                    'borderColor' => '#e0e0e0',
                    'strokeDashArray' => 0,
                    'position' => 'back',
                    'xaxis' => [
                        'lines' => [
                            'show' => false,
                        ],
                    ],
                    'yaxis' => [
                        'lines' => [
                            'show' => false,
                        ],
                    ],
                    'padding' => [
                        'top' => 0,
                        'right' => 0,
                        'bottom' => 0,
                        'left' => 0,
                    ],
                ],
            ],
        ];
    }

    //-------------------------------------------------

    public static function fetchOrdersStatusCountData(Request $request)
    {

        $query = self::select('order_status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('order_status');

        $query = self::appliedFilters($query, $request);

        $orderStatusCounts = $query->pluck('count', 'order_status')->toArray();

        return [
            'data' => [
                'chart_series' => array_values($orderStatusCounts),
                'chart_options' => [
                    'labels' => array_keys($orderStatusCounts),
                    'chart' => [
                        'id' => 'pie-chart',
                        'background' => '#fff',
                        'toolbar' => ['show' => true],
                        'zoom' => ['enabled' => false],
                    ],

                    'dataLabels' => [
                        'enabled' => true,
                        'style' => [
                            'fontSize' => '08px',
                        ],
                    ],
                ],
            ],
        ];

    }



// Function to apply filters to the query
    private static function appliedFilters($list, $request)
    {
        if (isset($request->filter)) {
            $list = $list->paymentStatusFilter($request->filter);
        }
        return $list;
    }

}
