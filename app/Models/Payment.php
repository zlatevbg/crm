<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'paid_at',
        'sale_id',
        'user_id',
        'status_id',
        'payment_method_id',
        'amount',
        'description',
    ];

    protected $dates = [
        'paid_at',
        'deleted_at',
    ];

    public $statuses = [
        19 => 'deposit',
        21 => 'promissory-payment',
        20 => 'final-balance',
        33 => 'final-balance',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Payments') && !Auth::user()->can('Edit: Payments') && !Auth::user()->can('Delete: Payments'));
    }

    public function getPaidAtAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y');
    }

    public function setPaidAtAttribute($value)
    {
        $this->attributes['paid_at'] = Carbon::parse($value);
    }

    public function selectStatus()
    {
        return Status::select('name', 'id', 'default')->where('parent', 3)->orderBy('order')->get();
    }

    public function selectPaymentMethods()
    {
        return PaymentMethod::pluck('name', 'id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function createRules($request, $api)
    {
        return [
            'paid_at' => 'required|date_format:"d.m.Y"|before_or_equal:' . date('d.m.Y'),
            'payment_method_id' => 'required|numeric|exists:payment_methods,id',
            'status_id' => 'required|numeric|exists:statuses,id',
            'amount' => 'required|numeric|between:0,9999999.99',
            'description' => 'present',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['sale_id'] = $api->model->_parent->id;
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    public function postStore($api, $request)
    {
        if (array_key_exists($request->input('status_id'), $this->statuses)) {
            $action = $this->statuses[$request->input('status_id')];

            $status = Status::where('parent', 1)->where('action', $action)->value('id');
            $apartment = Apartment::findOrFail($api->model->_parent->apartment_id);
            $ids = $apartment->statuses()->whereNull('apartment_status.deleted_at')->pluck('apartment_status.status_id')->all();
            if (!in_array($status, $ids)) {
                // ApartmentStatus::where('apartment_id', $apartment->id)->delete();
                ApartmentStatus::where('apartment_id', $apartment->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $apartment->statuses()->attach($status, ['user_id' => Auth::user()->id]);
            }
        }
    }

    public function postUpdate($api, $request)
    {
        if (array_key_exists($request->input('status_id'), $this->statuses)) {
            $action = $this->statuses[$request->input('status_id')];

            $status = Status::where('parent', 1)->where('action', $action)->value('id');
            $apartment = Apartment::findOrFail($api->model->_parent->apartment_id);
            $ids = $apartment->statuses()->whereNull('apartment_status.deleted_at')->pluck('apartment_status.status_id')->all();

            if (!in_array($status, $ids)) {
                // ApartmentStatus::where('apartment_id', $apartment->id)->delete();
                ApartmentStatus::where('apartment_id', $apartment->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $apartment->statuses()->attach($status, ['user_id' => Auth::user()->id]);
            }
        }
    }

    public function preDestroy($ids)
    {
        return $this->select('sale_id', 'status_id')->find($ids);
    }

    public function postDestroy($api, $ids, $rows)
    {
        foreach ($rows as $row) {
            $count = $api->model->where('sale_id', $row->sale_id)->where('status_id', $row->status_id)->count();
            if (!$count) {
                $model = Sale::find($row->sale_id);
                /*$status = Status::where('parent', 1)->where('action', $this->statuses[$row->status_id])->value('id');
                ApartmentStatus::where('apartment_id', $model->apartment_id)->where('status_id', $status)->whereNull('deleted_at')->update([
                    'deleted_at' => Carbon::now(),
                ]);*/

                $apartmentStatus = Status::where('parent', 1)->where('action', 'reserve')->value('id');
                $apartment = Apartment::find($model->apartment_id);
                // ApartmentStatus::where('apartment_id', $model->apartment_id)->delete();
                ApartmentStatus::where('apartment_id', $model->apartment_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $apartment->statuses()->attach($apartmentStatus, ['user_id' => Auth::user()->id]);
            }
        }
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'paid_at', 'paid');
        $data = Datatable::render($data, 'paid', ['sort' => ['paid_at' => 'timestamp']]);
        $data = Datatable::price($data, 'amount');

        $data->first()->user = User::selectRaw('CONCAT(first_name, " ", last_name) as user')->where('id', $data->first()->user_id)->value('user');
        $data->first()->status = Status::where('id', $data->first()->status_id)->value('name');
        $data->first()->method = PaymentMethod::where('id', $data->first()->payment_method_id)->value('name');

        $data = Datatable::popover($data, 'status');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function doptions($api)
    {
        return [
            'dom' => 'tr',
        ];
    }

    public function dColumns()
    {
        return [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
            [
                'id' => 'paid',
                'name' => trans('labels.paidAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'user',
                'name' => trans('labels.modifiedBy'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'method',
                'name' => trans('labels.paymentMethod'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'amount',
                'name' => trans('labels.amount'),
                'class' => 'text-right vertical-center',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'vertical-center popovers',
            ],
        ];
    }

    public function dButtons($api)
    {
        return [
            'create' => [
                'url' => Helper::route('api.create', $api->path),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Payments'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Payments'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Payments'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 0 : 1, 'desc'],
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $data = $this->selectRaw($table . '.id, ' . $table . '.paid_at, ' . $table . '.amount, ' . $table . '.description, statuses.name as status, payment_methods.name as method, CONCAT(users.first_name, " ", users.last_name) as user')
        ->where($table . '.sale_id', $api->model->_parent->id)
        ->leftJoin('statuses', 'statuses.id', '=', $table . '.status_id')
        ->leftJoin('payment_methods', 'payment_methods.id', '=', $table . '.payment_method_id')
        ->leftJoin('users', $table . '.user_id', '=', 'users.id')
        ->orderBy($table . '.paid_at', 'desc')
        ->get();

        $data = Datatable::format($data, 'date', 'd.m.Y', 'paid_at', 'paid');
        $data = Datatable::render($data, 'paid', ['sort' => ['paid_at' => 'timestamp']]);
        $data = Datatable::price($data, 'amount');
        $data = Datatable::popover($data, 'status');

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
