<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Booking extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'project_id',
        'arrive_at',
        'depart_at',
        'description',
        'user_id',
        'guest_id',
    ];

    protected $dates = [
        'arrive_at',
        'depart_at',
        'deleted_at',
    ];

    protected $softCascade = [
        'apartmentsSoftDelete',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Bookings') && !Auth::user()->can('Edit: Bookings') && !Auth::user()->can('Delete: Bookings'));
    }

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getArriveAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setArriveAtAttribute($value)
    {
        $this->attributes['arrive_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getDepartAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setDepartAtAttribute($value)
    {
        $this->attributes['depart_at'] = $value ? Carbon::parse($value) : null;
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectGuests($api = null)
    {
        return Guest::select('id', 'first_name', 'last_name')->whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->orderBy('first_name')->orderBy('last_name')->get();
    }

    public function selectApartments($api = null)
    {
        return Apartment::whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->pluck('unit', 'id');
    }

    public function apartmentsSoftDelete()
    {
        return $this->hasMany(ApartmentBooking::class);
    }

    public function createRules($request, $api)
    {
        return [
            'guest_id' => 'required|numeric',
            'project_id' => 'required|numeric|exists:projects,id',
            'apartments' => 'required|array',
            'arrive_at' => 'required|date_format:"d.m.Y"|before:depart_at', // |before_or_equal:' . date('d.m.Y')
            'depart_at' => 'required|date_format:"d.m.Y"',
            'description' => 'present|nullable',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
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
        $this->apartments()->attach($request->input('apartments'));
    }

    public function postUpdate($api, $request, $data)
    {
        $apartments = ApartmentBooking::where('booking_id', $this->id)->pluck('apartment_id');
        $apartmentsRemove = $apartments->diff($request->input('apartments'));
        $apartmentsAdd = collect($request->input('apartments'))->diff($apartments);

        if ($apartmentsRemove) {
            $this->apartments()->detach($apartmentsRemove);
        }

        if ($apartmentsAdd) {
            $this->apartments()->attach($apartmentsAdd);
        }
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'arrive_at', 'arrive');
        $data = Datatable::render($data, 'arrive', ['sort' => ['arrive_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'depart_at', 'depart');
        $data = Datatable::render($data, 'depart', ['sort' => ['depart_at' => 'timestamp']]);
        $data = Datatable::relationship($data, 'guest_name', 'guest');

        $data->first()->ids = $data->first()->id;

        $data->first()->projectName = $this->project->name;

        $data = Datatable::popover($data, 'ids', 'description', 'after');

        $data->first()->units = Apartment::selectRaw('GROUP_CONCAT(DISTINCT apartments.unit SEPARATOR ", ") AS units')
            ->leftJoin('apartment_booking', 'apartment_booking.apartment_id', '=', 'apartments.id')
            ->where('apartment_booking.booking_id', $data->first()->id)
            ->value('units');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
    }

    public function dColumns($api)
    {
        $columns = [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
            [
                'id' => 'ids',
                'name' => trans('labels.id'),
                'class' => 'vertical-center popovers',
            ],
        ];

        if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'booking')) {
            array_push($columns, [
                'id' => 'guest_name',
                'name' => trans('labels.guest'),
                'class' => 'vertical-center',
            ]);
        }

        array_push($columns, [
            'id' => 'arrive',
            'name' => trans('labels.arriveAt'),
            'render' =>  ['sort'],
            'class' => 'vertical-center',
        ]);

        array_push($columns, [
            'id' => 'depart',
            'name' => trans('labels.departAt'),
            'render' =>  ['sort'],
            'class' => 'vertical-center',
        ]);

        if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'guest')) {
            array_push($columns, [
                'id' => 'units',
                'name' => trans('labels.apartments'),
                'class' => 'vertical-center cell-max-15',
            ]);
        }

        if (!session('project')) {
            array_push($columns, [
                'id' => 'projectName',
                'name' => trans('labels.project'),
                'class' => 'vertical-center',
            ]);
        }

        return $columns;
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
                'visible' => Auth::user()->can('Create: Bookings'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Bookings'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Bookings'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [!$api->model->_parent ? ($this->isNotInteractable() ? 2 : 3) : ($this->isNotInteractable() ? 1 : 2), 'desc'],
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $bookings = $this->with('guest')->selectRaw($table . '.id, ' . $table . '.id AS ids, ' . $table . '.description, ' . $table . '.arrive_at, ' . $table . '.depart_at, ' . $table . '.guest_id, TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) AS projectName, GROUP_CONCAT(DISTINCT apartments.unit ORDER BY CAST(apartments.unit AS UNSIGNED), apartments.unit SEPARATOR ", ") as units')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->leftJoin('guests', 'guests.id', '=', $table . '.guest_id')
            ->leftJoin('apartment_booking', 'apartment_booking.booking_id', '=', $table . '.id')
            ->leftJoin('apartments', 'apartments.id', '=', 'apartment_booking.apartment_id')
            ->whereIn($table . '.project_id', Helper::project());

        if ($api->model->_parent) {
            if ($api->meta->_parent->model == 'guest') {
                $bookings = $bookings->where($table . '.guest_id', $api->model->_parent->id);
            }
        }

        $bookings = $bookings->groupBy($table . '.id')
            ->orderBy($table . '.arrive_at', 'desc')
            ->get();

        $data = Datatable::format($bookings, 'date', 'd.m.Y', 'arrive_at', 'arrive');
        $data = Datatable::render($data, 'arrive', ['sort' => ['arrive_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'depart_at', 'depart');
        $data = Datatable::render($data, 'depart', ['sort' => ['depart_at' => 'timestamp']]);
        $data = Datatable::relationship($data, 'guest_name', 'guest');
        $data = Datatable::popover($data, 'ids', 'description', 'after');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
