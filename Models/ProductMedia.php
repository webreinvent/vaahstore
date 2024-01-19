<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Http\Controllers\MediaController;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Models\TaxonomyType;

class ProductMedia extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_product_medias';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_st_product_id',
        'vh_st_product_variation_id',
        'taxonomy_id_product_media_status',
        'status_notes',
        'name',
        'slug',
        'url',
        'path',
        'size',
        'type',
        'extension',
        'mime_type',
        'url_thumbnail',
        'thumbnail_size',
        'meta',
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
        foreach ($fillable as $column)
        {
            $empty_item[$column] = null;
        }

        $empty_item['product_variation_store']=[];

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
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_product_media_status')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function product()
    {
        return $this->hasOne(Product::class,'id','vh_st_product_id')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function productVariation()
    {
        return $this->hasOne(ProductVariation::class,'id','vh_st_product_variation_id')->select('id','name','slug','is_default');
    }
    //-------------------------------------------------
    public function images()
    {
        return $this->hasMany(ProductMediaImage::class, 'vh_st_product_media_id','id');
    }
    //-------------------------------------------------
//    public function productVariationMedia()
//    {
//        return $this->belongsToMany(ProductVariation::class, 'vh_st_prod_variation_media', 'vh_st_product_media_id', 'vh_st_product_variation_id');
//    }
    public function productVariationMedia()
    {
        return $this->belongsToMany(ProductVariation::class, 'vh_st_prod_variation_media', 'vh_st_product_media_id', 'vh_st_product_variation_id')
            ->withPivot('vh_st_product_id');
    }
//    public function variationList()
//    {
//        return $this->belongsToMany(ProductVariation::class, 'vh_st_prod_variation_media', 'vh_st_product_media_id', 'vh_st_attribute_id')
//
//            ;
//    }
    //-------------------------------------------------
    public function updatedByUser()
    {
        return $this->belongsTo(User::class,
            'updated_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }
    //-------------------------------------------------
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
    public static function createItem($request)
    {
        $inputs = $request->all();
        $product_variation = $inputs['product_variation'];

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

//        if (!isset($inputs['images']) || empty($inputs['images'])) {
//            $response['success'] = false;
//            $response['messages'][] = "The image field is required.";
//            return $response;
//        }

        // check if exist
        $item = self::where('vh_st_product_id', $inputs['vh_st_product_id'])->withTrashed()->first();
        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This Product is already exist.";
            return $response;
        }
            $item = new self();
            $item->fill($inputs);
            $item->save();
//        }

        foreach ($inputs['images'] as $image_details) {
            $image = new ProductMediaImage;
            $image->vh_st_product_media_id = $item->id;
            $image->name = $image_details['name'];
            $image->slug = $image_details['slug'];
            $image->url = $image_details['url'];
            $image->path = $image_details['path'];
            $image->size  = $image_details['size'];
            $image->type = $image_details['type'];
            $image->extension = $image_details['extension'];
            $image->mime_type = $image_details['mime_type'];
            $image->url_thumbnail = $image_details['url_thumbnail'];
            $image->thumbnail_size  = $image_details['thumbnail_size'];
            $image->save();
        }


        if (isset($product_variation) && is_array($product_variation)) {
//            $item->productVariationMedia()->detach();

            foreach ($product_variation as $variation) {
                $item->productVariationMedia()->attach($variation['id']);
            }
        }

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';

        return $response;
    }
    //-------------------------------------------------
    public function scopeGetSorted($query, $filter)
    {

        if(!isset($filter['sort']))
        {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if(!$direction)
        {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }
    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if(!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        )
        {
            return $query;
        }
        $is_active = $filter['is_active'];

        if($is_active === 'true' || $is_active === true)
        {
            return $query->where('is_active', 1);
        } else{
            return $query->where(function ($q){
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }
    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if(!isset($filter['trashed']))
        {
            return $query;
        }
        $trashed = $filter['trashed'];

        if($trashed === 'include')
        {
            return $query->withTrashed();
        } else if($trashed === 'only'){
            return $query->onlyTrashed();
        }

    }
    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $keywords = explode(' ',$filter['q']);
        foreach($keywords as $search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('slug', 'LIKE', '%' . $search . '%');
                    });
            });
        }

    }
    //-------------------------------------------------
    public function scopeStatusFilter($query, $filter)
    {

        if(!isset($filter['status']))
        {
            return $query;
        }
        $search = $filter['status'];
        $query->whereHas('status' , function ($q) use ($search){
            $q->whereIn('slug' ,$search);
        });
    }
    //-------------------------------------------------
    public function scopeProductVariationFilter($query, $filter)
    {

        if(!isset($filter['product_variation']))
        {
            return $query;
        }
        $search = $filter['product_variation'];
        $query->whereHas('productVariationMedia',function ($q) use ($search) {
            $q->whereIn('slug',$search);
        });

    }
    public function scopeMediaTypeFilter($query, $filter)
    {
        if (!isset($filter['type']) || empty($filter['type'])) {
            return $query;
        }

        $search = $filter['type'];

        return $query->where(function ($query) use ($search) {
            foreach ($search as $type) {
                $query->orWhere('type', $type);
            }
        });
    }
    //-------------------------------------------------

    public function scopeProductFilter($query, $filter)
    {

        if(!isset($filter['product'])
            || is_null($filter['product'])
            || $filter['product'] === 'null'
        )
        {
            return $query;
        }

        $product = $filter['product'];

        $query->whereHas('product', function ($query) use ($product) {
            $query->where('slug', $product);

        });

    }

    //-------------------------------------------------
    public function scopeDateFilter($query, $filter)
    {
        if(!isset($filter['date'])
            || is_null($filter['date'])
        )
        {
            return $query;
        }

        $dates = $filter['date'];
        $from = \Carbon::parse($dates[0])
            ->startOfDay()
            ->toDateTimeString();

        $to = \Carbon::parse($dates[1])
            ->endOfDay()
            ->toDateTimeString();

        return $query->whereBetween('created_at', [$from, $to]);

    }
    //-------------------------------------------------


    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status','product','productVariation','productVariationMedia','images');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->productVariationFilter($request->filter);
        $list->productFilter($request->filter);
        $list->dateFilter($request->filter);
        $list->mediaTypeFilter($request->filter);
        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
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

        if(isset($inputs['items']))
        {
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
                $items->update(['deleted_by' => auth()->user()->id]);
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

        // delete value from pivot table
        $item_id = collect($inputs['items'])->pluck('id')->toArray();
        ProductMediaImage::whereIn('vh_st_product_media_id', $item_id)->forceDelete();

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
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

        if(isset($inputs['items']))
        {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();

            $items = self::whereIn('id', $items_id)
                ->withTrashed();
        }

        $list = self::query();

        if($request->has('filter')){
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {
            case 'deactivate':
                if($items->count() > 0) {
                    $items->update(['is_active' => null]);
                }
                break;
            case 'activate':
                $items->update(['is_active' => 1, ]);
                if($items->count() > 0) {
                    $items->update(['is_active' => 1]);
                }
                break;
            case 'trash':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->delete();
                    $items->update(['deleted_by' => auth()->user()->id]);
                }
                break;
            case 'restore':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                    $items->update(['deleted_by' => null]);
                }
                break;
            case 'delete':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->forceDelete();
                }
                break;
            case 'activate-all':
                $list->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->update(['deleted_by'  => auth()->user()->id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->get()->each(function ($record) {
                    $record->update(['deleted_by' => null]);
                });
                $list->restore();
                break;
            case 'delete-all':
                $items_id = self::all()->pluck('id')->toArray();
                self::withTrashed()->forceDelete();
                ProductMediaImage::deleteImages($items_id);
                $list->forceDelete();
                break;
            case 'create-100-records':
            case 'create-1000-records':
            case 'create-5000-records':
            case 'create-10000-records':

            if(!config('store.is_dev')){
                $response['success'] = false;
                $response['errors'][] = 'User is not in the development environment.';

                return $response;
            }

            preg_match('/-(.*?)-/', $type, $matches);

            if(count($matches) !== 2){
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','product','images'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        if (!empty($item->productVariationMedia)){
            $item->listed_variation = [];
            $variation = [];
            foreach ($item->productVariationMedia->toArray() as $k=>$a){
                $variation[$k]['id'] = $a['id'];
                $variation[$k]['name'] = $a['name'];
                $variation[$k]['slug'] = $a['slug'];
            }
            $item->listed_variation = $variation;
        }
        $item->base_path = url('');
        $item->images = [];
        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function saveUploadImage($request)
    {

        $allowed_file_upload_size = config('vaahcms.allowed_file_upload_size');

        $input_file_name = null;
        $rules = array(
            'folder_path' => 'required',
            'file' => 'max:'.$allowed_file_upload_size,
        );

        if($request->has('file_input_name'))
        {
            $rules[$request->file_input_name] = 'required';
            $input_file_name = $request->file_input_name;
        } else{
            $rules['file'] = 'required';
            $input_file_name = 'file';
        }



        $validator = \Validator::make( $request->all(), $rules);
        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['status'] = 'failed';
            $response['errors'] = $errors;
            return response()->json($response);
        }

        try{

            //add year and month folder

            $data['extension'] = $request->file->extension();
            $data['original_name'] = $request->file->getClientOriginalName();
            $data['mime_type'] = $request->file->getClientMimeType();
            $type = explode('/',$data['mime_type']);
            $data['type'] = $type[0];
            $data['size'] = $request->file->getSize();

            if($request->file_name && !is_null($request->file_name)
                && $request->file_name != 'null')
            {
                $upload_file_name = Str::slug($request->file_name).'.'.$data['extension'];

                $upload_file_path = 'storage/app/'.$request->folder_path.'/'.$upload_file_name;

                $full_upload_file_path = base_path($upload_file_path);

                //if file already exist then prefix if with microtime
                if(File::exists($full_upload_file_path))
                {
                    $time_stamp = \Carbon\Carbon::now()->timestamp;
                    $upload_file_name = Str::slug($request->file_name).'-'.$time_stamp.'.'.$data['extension'];
                }
                $path = $request->file
                    ->storeAs($request->folder_path, $upload_file_name);

                $data['name'] = $request->file_name;
                $data['uploaded_file_name'] = $data['name'].'.'.$data['extension'];

            } else{
                $path = $request->file->store($request->folder_path);

                $data['name'] = $data['original_name'];
                $data['uploaded_file_name'] = $data['name'];
            }

            $data['slug'] = Str::slug($data['name']);
            //$data['extension'] = $name_details['extension'];

            $data['path'] = 'storage/app/'.$path;
            $data['full_path'] = base_path($data['path']);

            $data['url'] = $path;

            if (substr($path, 0, 6) =='public') {
                $data['url'] = 'storage'.substr($path, 6);
            }

            $data['full_url'] = asset($data['url']);

            //create thumbnail if image
            if($data['type'] == 'image')
            {
                $image = \Image::make($data['full_path'])->fit(180, 101, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $name_details = pathinfo($data['full_path']);
                $thumbnail_name = $name_details['filename'].'-thumbnail.'.$name_details['extension'];
                $thumbnail_path = $request->folder_path.'/'.$thumbnail_name;
                $temp = \Storage::put($thumbnail_path, (string) $image->encode());
                $data['thumbnail_name'] = $thumbnail_name;
                $data['thumbnail_size'] = Storage::size($thumbnail_path);

                if (substr($thumbnail_path, 0, 6) =='public') {
                    $data['url_thumbnail'] = 'storage'.substr($thumbnail_path, 6);
                }

            }

            $response['status'] = true;
            $response['data'] = $data;

        }catch(\Exception $e)
        {
            $response = [];
            $response['status'] = false;
            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else{
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return $response;
    }
     //-------------------------------------------------


    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        $product_variation = $inputs['product_variation'];

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        if (!isset($inputs['images']) || empty($inputs['images'])) {
            $response['success'] = false;
            $response['messages'][] = "The image field is required.";
            return $response;
        }

        // Find the item by ID
        $item = self::findOrFail($id);
        if ($item->vh_st_product_id !== $inputs['vh_st_product_id'] && self::where('vh_st_product_id', $inputs['vh_st_product_id'])->exists()) {
            $response['success'] = false;
            $response['messages'][] = "The Product is already exist.";
            return $response;
        }
        // Update the item with the new inputs
        $item->fill($inputs);
        $item->save();

        // Delete existing images
        $item->images()->delete();

        // Save new images
        foreach ($inputs['images'] as $image_details) {
            $image = new ProductMediaImage;
            $image->vh_st_product_media_id = $item->id;
            $image->name = $image_details['name'];
            $image->slug = $image_details['slug'];
            $image->url = $image_details['url'];
            $image->path = $image_details['path'];
            $image->size  = $image_details['size'];
            $image->type = $image_details['type'];
            $image->extension = $image_details['extension'];
            $image->mime_type = $image_details['mime_type'];
            $image->url_thumbnail = $image_details['url_thumbnail'];
            $image->thumbnail_size  = $image_details['thumbnail_size'];
            $image->save();
        }


        if (isset($product_variation) && is_array($product_variation)) {
            $item->productVariationMedia()->detach();

            foreach ($product_variation as $variation) {
                $item->productVariationMedia()->attach($variation['id']);
            }
        }

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';

        return $response;
    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record does not exist.';
            return $response;
        }

        $ids = $item->images->pluck('id')->toArray();
        ProductMediaImage::whereIn('id', $ids)->forceDelete();
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been deleted';

        return $response;
    }
    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        switch($type)
        {
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
                $item = self::where('id',$id)->withTrashed()->first();
                if($item->delete()) {
                    $item->deleted_by = auth()->user()->id;
                    $item->save();
                }
                break;
            case 'restore':
                self::where('id', $id)
                    ->withTrashed()
                    ->restore();
                $item = self::where('id',$id)->withTrashed()->first();
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
            'product'=> 'required',
            'product_variation'=> 'required',
//            'vh_st_product_id'=> 'required',
            'images'=> 'required',
            'taxonomy_id_product_media_status'=> 'required',

            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:100'
            ],
        ],
        [
            'product.required' => 'The Product field is required.',
            'product_variation.required' => 'The Product Variations field is required.',
            'taxonomy_id_product_media_status.required' => 'The Status field is required',

            'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
            'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
            'images' => 'The Media field is required.',
        ]
        );
        if($rules->fails()){
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
    public static function deleteProducts($items_id){
        if($items_id){
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }
    //-------------------------------------------------

    public static function deleteProduct($items_id){

        if($items_id){
            self::where('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }
    //-------------------------------------------------

    public static function deleteProductVariations($items_id){

        $response=[];

        if ($items_id) {
            $items_exist = self::whereIn('vh_st_product_variation_id', $items_id)->get();

            if ($items_exist) {
                self::whereIn('vh_st_product_variation_id', $items_id)->forceDelete();
                $response['success'] = true;
            }
        }

        $response['success'] = false;

        return $response;


    }
    //-------------------------------------------------
    public static function seedSampleItems($records=100)
    {

        $i = 0;

        while($i < $records)
        {
            $inputs = self::fillItem(false);

            $item =  new self();
            $item->fill($inputs);
            $item->save();
//            $variations = ProductVariation::where('vh_st_product_id', $item->vh_st_product_id)->get();
//            $item->productVariationMedia()->attach($variations);

            $variations = ProductVariation::where('vh_st_product_id', $item->vh_st_product_id)->limit(1)->get();
            if ($variations->count() >= 1) {
                $item->productVariationMedia()->attach($variations->pluck('id'));

                $item->images()->create([
                    'url' => $inputs['images']['url'],
                ]);
            }
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
        if(!$fillable['success']){
            return $fillable;
        }
        $inputs = $fillable['data']['fill'];

        $product = Product::where('is_active',1)->inRandomOrder()->first();

        if ($product) {
            $inputs['vh_st_product_id'] = $product->id;
            $inputs['product'] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ];
            $variations = ProductVariation::where('is_active', 1)
                ->where('vh_st_product_id', $product->id)
                ->get(['id', 'name', 'slug']);

            $inputs['listed_variation'] = $variations;
        } else {
            $response['success'] = false;
        }



        $taxonomy_status = Taxonomy::getTaxonomyByType('product-medias-status');
        $status_id = $taxonomy_status->pluck('id')->random();
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_product_media_status'] = $status_id;
        $inputs['status']=$status;


        $image_data = ProductMediaImage::inRandomOrder()->get()->toArray();
        $random_index = array_rand($image_data);
        $random_image = $image_data[$random_index];
        $inputs['images'] = $random_image;





        $faker = Factory::create();

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */

        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-------------------------------------------------
    public static function deleteProductVariation($item_id){

        $response=[];

        if ($item_id) {
            $item_exist = self::where('vh_st_product_variation_id', $item_id)->first();

            if ($item_exist) {

                self::where('vh_st_product_variation_id', $item_id)->forceDelete();
                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }

        return $response;

    }
    //-------------------------------------------------
    public static function searchProduct($request){
        $addedBy = Product::select('id', 'name','slug')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $addedBy->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $addedBy = $addedBy->limit(10)->get();

        $response['success'] = true;
        $response['data'] = $addedBy;
        return $response;

    }


    //-------------------------------------------------
    public static function searchProductVariation($request){
        $addedBy = ProductVariation::select('id', 'name','slug')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $addedBy->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $addedBy = $addedBy->limit(10)->get();

        $response['success'] = true;
        $response['data'] = $addedBy;
        return $response;

    }
    //-------------------------------------------------
    public static function searchStatus($request){
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('product-medias-status');
        } else {
            $status = TaxonomyType::getFirstOrCreate('product-medias-status');
            $item =array();

            if(!$status){
                return $item;
            }
            $item = Taxonomy::whereNotNull('is_active')
                ->where('vh_taxonomy_type_id',$status->id)
                ->where('name', 'LIKE', '%' . $query . '%')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $item;
        return $response;

    }

    public static function searchVariation($request)
    {
//        $query = $request['filter']['q']['query'];
        $query = $request->input('query');
        if($query === null)
        {
            $attribute_name = ProductVariation::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $attribute_name = ProductVariation::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $attribute_name;
        return $response;

    }

    //-------------------------------------------------
    public static function searchVariationsUsingUrlSlug($request)
    {
        $query = $request['filter']['product_variation'];

        $variations = ProductVariation::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $variations;
        return $response;
    }

    public static function searchStatusUsingUrlSlug($request)
    {
        $query = $request['filter']['status'];
        $status_type = TaxonomyType::getFirstOrCreate('product-medias-status');

        $item = Taxonomy::whereNotNull('is_active')
            ->where('vh_taxonomy_type_id',$status_type->id)
            ->whereIn('slug',$query)
            ->get();

        $response['success'] = true;
        $response['data'] = $item;
        return $response;
    }

    public static function searchMediaType($request)
    {
        $query = $request->input('query');
        if($query === null)
        {
            $media_type = self::select('id','name','slug','type')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $media_type = self::where('name', 'like', "%$query%")
                ->orWhere('type','like',"%$query%")
                ->select('id','name','slug','type')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $media_type;
        return $response;

    }
    public static function searchMediaUsingUrlType($request)
    {
        $query = $request['filter']['type'];
        $variations = self::whereIn('type',$query)

            ->select('id','name','slug','type')->get();

        $response['success'] = true;
        $response['data'] = $variations;
        return $response;
    }

    public static function searchVariationOfProduct($request)
    {
        $input = $request->all();
        $q = $input['q'];
        $id = $input['id'];
        $variation = ProductVariation::where('vh_st_product_id', $id)
            ->where(function ($query) use ($q) {
                $query->orWhere('slug', 'like', "%$q%")
                    ->orWhere('name', 'like', "%$q%");
            })
            ->select('id', 'name', 'slug', 'vh_st_product_id')
            ->get();

        $response['success'] = true;
        $response['data'] = $variation;

        return $response;


    }


}
