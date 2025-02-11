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
use VaahCms\Modules\Store\Models\User as StoreUser;

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
        'vh_user_id','order_status',
        'vh_st_payment_method_id','taxonomy_id_payment_status',
        'delivery_fee','taxes','discount',
        'paid','is_paid',
        'order_status',
        'status_notes',
        'is_active',
        'created_at',
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
    public function scopeFindByIdOrUuid($query, $value)
    {
        return $query->where('id', $value)->orWhere('uuid', $value);
    }

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

        $item = self::with(['createdByUser', 'updatedByUser', 'deletedByUser', 'user', 'status', 'paymentMethod', 'orderPaymentStatus',
            'payments.createdByUser'
        ])
            ->withCount('items')
            ->withTrashed()
            ->findByIdOrUuid($id)
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

        $item = self::withTrashed()->findByIdOrUuid($id)->first();
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
        $item = self::withTrashed()->findByIdOrUuid($id)->first();
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
        $item = self::withTrashed()->findByIdOrUuid($id)->first();
        if (!$item) {
            $response['success'] = false;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        switch ($type) {
            case 'activate':
                $item->update(['is_active' => 1]);
                break;
            case 'deactivate':
                $item->update(['is_active' => null]);
                break;
            case 'trash':
                $item->delete();
                $item->deleted_by = auth()->user()->id;
                $item->save();
                break;
            case 'restore':
                $item->restore();
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

        while ($i < 2) {
            $inputs = self::fillItem(false);

            $item = new self();

            $item->fill($inputs);
            $item->save();

            ///////////////////////////////// order items

            $order_items_types = Taxonomy::inRandomOrder()
                ->whereHas('type', function ($query) {
                    $query->where('slug', 'Order-items-types');
                })
                ->first();


            $order_items_status = Taxonomy::inRandomOrder()
                ->whereHas('type', function ($query) {
                    $query->where('slug', 'Order-items-status');
                })
                ->first();


            $valid_products = Product::whereHas('productVendors')
                ->with('productVariations', 'productVendors')
                ->take(rand(1, 10))
                ->get();


            $user_addresses = StoreUser::where('id', $inputs['vh_user_id'])
                ->with('addresses')
                ->whereHas('addresses', function ($query) {
                    $query->whereHas('addressType', function ($query) {
                        $query->where('slug', 'shipping')
                            ->orWhere('slug', 'billing');
                    });
                })
                ->first();

            foreach ($valid_products as $product) {

                $product_id = $product['id'];
                $vendor_id = $product->productVendors->random()->vh_st_vendor_id;

                $random_variation_id = $product->productVariations->pluck('id')->random();
                $price = $product->productVariations->where('id', $random_variation_id)->first()->price;

                    $order_item = new OrderItem();
                    $order_item['vh_st_order_id'] = $item->id;
                    $order_item['vh_user_id'] = $item->vh_user_id;
                    $order_item['vh_st_customer_group_id'] = null;

                    if (!empty($order_items_types)) {
                        $order_item['taxonomy_id_order_items_types'] = $order_items_types->id;
                    }

                    if (!empty($order_items_status)) {
                        $order_item['taxonomy_id_order_items_status'] = $order_items_status->id;
                    }

                    $order_item['vh_st_product_id'] = $product_id;
                    $order_item['vh_st_vendor_id'] = $vendor_id;

                    $order_item['vh_st_product_variation_id'] = $random_variation_id;
                    $order_item['vh_shipping_address_id'] = $user_addresses->addresses->random()->id;
                    $order_item['vh_billing_address_id'] = $order_item['vh_shipping_address_id'];

                    $order_item['quantity'] = rand(1,17);
                    $order_item['price'] = $price;
                    $order_item['is_invoice_available'] = '';
                    $order_item['invoice_url'] = '';
                    $order_item['tracking'] = '';
                    $order_item['is_active'] = 1;
                    $order_item['created_at'] = Carbon::now()->subYear()->addDays(rand(0, 365))->format('Y-m-d H:i:s');
                    $order_item['status_notes'] = '';
                    $order_item->save();

            }
            $total_price = OrderItem::where('vh_st_order_id', $item->id)
                ->get()
                ->sum(function ($order_item) {
                    return $order_item->price * $order_item->quantity;
                });
            $item->amount = $total_price;
            $item->payable = $total_price;
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

        $inputs['vh_user_id'] = StoreUser::whereHas('addresses', function ($query) {
            $query->whereHas('addressType', function ($query) {
                $query->where('slug', 'shipping')
                    ->orWhere('slug', 'billing');
            });
        })->inRandomOrder()->value('id');


        $inputs['order_status'] = 'Placed';
        $inputs['vh_st_payment_method_id'] = PaymentMethod::inRandomOrder()->value('id');

        $order_payment_status= Taxonomy::inRandomOrder()
            ->whereHas('type', function ($query) {
                $query->where('slug', 'order-payment-status');
            })
            ->first();
        if (!empty($order_payment_status)) {
            $inputs['taxonomy_id_payment_status'] = $order_payment_status->id;
        }

        $inputs['order_shipment_status'] = 'Pending';
        $inputs['delivery_fee'] = 0;
        $inputs['taxes'] = 0;
        $inputs['discount'] = 0;
        $inputs['paid'] = '';
        $inputs['is_paid'] = 0;
        $inputs['is_active'] = 1;
        $inputs['created_at'] = Carbon::now()->subYear()->addDays(rand(0, 365))->format('Y-m-d H:i:s');


        if (!$is_response_return) {
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-----------------validation for product price--------------------------------



    //-------------------------------------------------

    public static function getShippedOrderItems($uuid)
    {
        $order = Order::findByIdOrUuid($uuid)->first();

        if (!$order) {
            $response['success'] = false;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        $order_items = OrderItem::where('vh_st_order_id', $order->id)
            ->with('ProductVariation','product','vendor')
            ->get();

        $total_quantities = [];

        $shipment_items = ShipmentItem::where('vh_st_order_id', $order->id)
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


    public static function fetchOrdersChartData(Request $request)
    {
        $inputs = $request->all();
        $start_date = isset($inputs['start_date']) ? Carbon::parse($inputs['start_date'])->startOfDay() : null;
        $end_date = isset($inputs['end_date']) ? Carbon::parse($inputs['end_date'])->endOfDay() : null;

        $orders_statuses_count = self::select('order_status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('order_status');

        if ($start_date && $end_date) {
            $orders_statuses_count = $orders_statuses_count->whereBetween('updated_at', [$start_date, $end_date]);
        }

        $order_status_counts_pie_chart_data = $orders_statuses_count->pluck('count', 'order_status')->toArray();

        return [
            'data' => [
                'chart_series' => [
                    'orders_statuses_pie_chart' => array_values($order_status_counts_pie_chart_data),
                ],
                'chart_options' => [
                    'labels' => array_keys($order_status_counts_pie_chart_data),
                ],
            ],
        ];
    }






    //-------------------------------------------------






    public static function fetchSalesChartData($request)
    {
        $inputs = $request->all();
        $start_date = isset($inputs['start_date']) ? Carbon::parse($inputs['start_date'])->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($inputs['end_date']) ? Carbon::parse($inputs['end_date'])->endOfDay() : Carbon::now()->endOfDay();

        $period = new \DatePeriod($start_date, new \DateInterval('P1D'), $end_date);
        $labels = [];

        foreach ($period as $date) {
            $labels[] = $date->format('Y-m-d');
        }


        $query = OrderItem::query();

        $sales_data = $query
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(quantity * price) as total_sales');


        if ($inputs['start_date'] && $inputs['end_date']) {
            $sales_data = $sales_data->whereBetween('created_at', [$start_date, $end_date]);
        }
        $sales_data = $sales_data->groupBy('date')
            ->orderBy('date')
            ->get();
        $total_sales_chart_data = [];
        foreach ($sales_data as $item) {
            $date = Carbon::parse($item->date);

            $total_sales_chart_data[] = ['x' => $item->date, 'y' => (int)$item->total_sales];
        }

        $overall_total_sales = $sales_data->sum('total_sales');
        $first_sale =  reset($total_sales_chart_data)['y'] ?? 0;
        $last_sale = end($total_sales_chart_data)['y'] ?? 0;


        $growth_rate = 0;

        if ($first_sale > 0) {
            $growth_rate = (($last_sale - $first_sale) / $first_sale) * 100;
        } elseif ($first_sale === 0 && $last_sale > 0) {
            $growth_rate = 100;
        }

        return [
            'data' => [
                'chart_series' => [
                    'orders_sales_chart_data' => [
                        [
                            'name' => 'Total Sale',
                            'data' => $total_sales_chart_data,
                        ]
                    ],
                    'overall_total_sales' => $overall_total_sales,
                    'growth_rate' => $growth_rate,
                ],
                'chart_options' => [
                    'xaxis' => [
                        'type' => 'datetime',
                        'categories' => $labels
                    ],

                ],
            ],
        ];
    }


    //-------------------------------------------------



    public static function fetchOrdersCountChartData($request)
    {
        $inputs = $request->all();

        $start_date = Carbon::parse($inputs['start_date'] ?? Carbon::now())->startOfDay();
        $end_date = Carbon::parse($inputs['end_date'] ?? Carbon::now())->endOfDay();

        $labels = [];
        foreach (new \DatePeriod($start_date, new \DateInterval('P1D'), $end_date->copy()->addDay()) as $date) {
            $labels[] = $date->format('Y-m-d');
        }

        $date_column = 'created_at';
        $count = 'COUNT';
        $group_by_column = 'DATE(created_at)';

        $list = Order::query();

        $filtered_data = self::appliedFilters($list, $request);

        $order_data_query = $filtered_data->selectRaw("$group_by_column as order_date")
        ->selectRaw("$count($date_column) as total_count")
        ->selectRaw("SUM(CASE WHEN order_status = 'Completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("SUM(CASE WHEN order_status != 'Completed' THEN 1 ELSE 0 END) as pending_count");

        if ($inputs['start_date'] && $inputs['end_date']) {
            $order_data_query = $order_data_query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $chart_data = $order_data_query->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        $total_orders = [];
        $completed_orders = [];

        foreach ($chart_data as $item) {
            $order_date = Carbon::parse($item->order_date);

            $total_orders[] = ['x' => $item->order_date, 'y' => (int)$item->total_count];
            $completed_orders[] = ['x' => $item->order_date, 'y' => (int)$item->completed_count];
        }



        return [
            'data' => [
                'chart_series' => [
                    'orders_count_bar_chart' => [
                        [
                            'name' => 'Created',
                            'data' => $total_orders,
                        ],
                        [
                            'name' => 'Completed',
                            'data' => $completed_orders,
                        ],
                    ],
                ],
                'chart_options' => [
                    'xaxis' => [
                        'type' => 'datetime',
                        'categories' => $labels,
                    ],
                ],
            ],
        ];
    }

    //-------------------------------------------------

    public static function fetchOrderPaymentsData($request) {
        $inputs = $request->all();
        $start_date = isset($inputs['start_date']) ? Carbon::parse($inputs['start_date'])->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($inputs['end_date']) ? Carbon::parse($inputs['end_date'])->endOfDay() : Carbon::now()->endOfDay();

        $period = new \DatePeriod($start_date, new \DateInterval('P1D'), $end_date);
        $labels = [];

        foreach ($period as $date) {
            $labels[] = $date->format('Y-m-d');
        }
        $query = OrderPayment::query();

        $orders_income = $query
            ->selectRaw('DATE(created_at) as created_date')
            ->selectRaw('SUM(payment_amount) as total_income');
        if ($inputs['start_date'] && $inputs['end_date']) {
            $orders_income = $orders_income->whereBetween('created_at', [$start_date, $end_date]);
        }
        $orders_income = $orders_income->groupBy('created_date')
            ->orderBy('created_date')
            ->get();

        $time_series_data_income = [];
        foreach ($orders_income as $item) {
            $created_date = Carbon::parse($item->created_date);

            $time_series_data_income[] = ['x' => $item->created_date, 'y' => $item->total_income];
        }


        $overall_income = round($orders_income->sum('total_income'), 2);


        $first_income = reset($time_series_data_income)['y'] ?? 0;
        $last_income = end($time_series_data_income)['y'] ?? 0;

        $growth_rate = 0;

        if ($first_income > 0) {
            $growth_rate = (($last_income - $first_income) / $first_income) * 100;
        } elseif ($first_income === 0 && $last_income > 0) {
            $growth_rate = 100;
        }

        return [
            'data' => [
                'order_payments_chart_series' => [
                    'orders_payment_income_chart_data' => [
                        [
                            'name' => 'Payment',
                            'data' => $time_series_data_income,
                        ]
                    ],
                    'overall_income' => $overall_income,
                    'income_growth_rate' => round($growth_rate, 2),
                ],
                'chart_options' => [
                    'xaxis' => [
                        'type' => 'datetime',
                        'categories' => $labels
                    ],
                ],
            ],
        ];
    }
    //-------------------------------------------------

    private static function appliedFilters($list, $request)
    {
        if (isset($request->filter)) {
            $list = $list->paymentStatusFilter($request->filter);
        }
        return $list;
    }

}
