<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Viewing extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'project_id',
        'viewed_at',
        'description',
        'user_id',
        'client_id',
        'agent_id',
    ];

    protected $dates = [
        'viewed_at',
        'deleted_at',
    ];

    protected $softCascade = [
        'apartmentsSoftDelete',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Viewings') && !Auth::user()->can('Edit: Viewings') && !Auth::user()->can('Delete: Viewings'));
    }

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function statuses()
    {
        return $this->belongsToMany(Status::class)->where('statuses.parent', 4);
    }

    public function getViewedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setViewedAtAttribute($value)
    {
        $this->attributes['viewed_at'] = $value ? Carbon::parse($value) : null;
    }

    public function selectProjects($apartment)
    {
        return Project::when($apartment, function ($query) use ($apartment) {
            return $query->where('id', $apartment->project_id);
        })->selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectAgents($api = null)
    {
        return Agent::select('agents.id', 'agents.company AS agent')
            // ->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')
            // ->whereIn('agent_project.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
            ->orderBy('agent')
            ->pluck('agent', 'id');
    }

    public function selectClients($api = null)
    {
        return Client::select('clients.id', 'clients.first_name', 'clients.last_name', 'clients.agent_id')
            ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
            ->whereIn('client_project.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
            ->orderBy('clients.first_name')
            ->orderBy('clients.last_name')
            ->get();
    }

    public function selectApartments($api = null)
    {
        return Apartment::whereNotExists(function ($query) {
            $query->from('sales')->whereColumn('apartments.id', '=', 'sales.apartment_id')->whereNull('sales.deleted_at');
        })->whereIn('apartments.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
        ->pluck('apartments.unit', 'apartments.id');
    }

    public function futureStatus()
    {
        return Status::where('parent', 4)->where('action', 'future-viewing')->value('id');
    }

    public function selectStatus()
    {
        return Status::select('name', 'id')->where('parent', 4)->orderBy('name')->get()->pluck('name', 'id');
    }

    public function apartmentsSoftDelete()
    {
        return $this->hasMany(ApartmentViewing::class);
    }

    public function createRules($request, $api)
    {
        return [
            'client_id' => 'nullable|numeric|required_without:apartments',
            'project_id' => 'required|numeric|exists:projects,id',
            'agent_id' => 'nullable|numeric|exists:agents,id',
            'apartments' => 'nullable|array|required_without:client_id',
            'viewed_at' => 'required|date_format:"d.m.Y"', // |before_or_equal:' . date('d.m.Y')
            'status' => 'nullable|array|required_without:description',
            'description' => 'present|required_without:status',
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
        $data['old_client_id'] = $this->client_id;

        return $data;
    }

    public function postStore($api, $request)
    {
        $lead = Status::where('parent', 2)->where('action', 'viewing')->value('id');
        $sale = Status::where('parent', 2)->where('action', 'deposit')->value('id');
        $client = Client::with('statuses')->find($request->input('client_id'));
        if ($client) {
            $ids = $client->statuses()->whereNull('client_status.deleted_at')->pluck('client_status.status_id')->all();
            if (!in_array($lead, $ids) && !in_array($sale, $ids)) {
                // ClientStatus::where('client_id', $client->id)->delete();
                ClientStatus::where('client_id', $client->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                $active = $client->statuses()->whereNull('client_status.deleted_at')->count();
                if (!$active) {
                    $client->statuses()->attach($lead, ['user_id' => Auth::user()->id]);
                }
            }

            if (!$client->projects->pluck('id')->contains($request->input('project_id'))) {
                $client->projects()->attach($request->input('project_id'));
            }
        }

        $this->apartments()->attach($request->input('apartments'));

        $this->statuses()->attach($request->input('status'));
    }

    public function postUpdate($api, $request, $data)
    {
        $lead = Status::where('parent', 2)->where('action', 'viewing')->value('id');
        $sale = Status::where('parent', 2)->where('action', 'deposit')->value('id');
        if ($this->client_id != $data['old_client_id']) {
            $client = Viewing::find($data['old_client_id']);
            if (!$client) {
                ClientStatus::where('client_id', $data['old_client_id'])->where('status_id', $lead)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                // make it an enquiry
                $status = Status::where('parent', 2)->where('default', 1)->value('id');
                $client = Client::findOrFail($data['old_client_id']);
                $active = $client->statuses()->whereNull('client_status.deleted_at')->count();
                $viewings = Viewing::where('client_id', $data['old_client_id'])->count();
                if (!$viewings && !$active) {
                    $client->statuses()->attach($status, ['user_id' => Auth::user()->id]);
                }
            }
        }

        $client = Client::with('statuses')->find($this->client_id);
        if ($client) {
            $ids = $client->statuses()->whereNull('client_status.deleted_at')->pluck('client_status.status_id')->all();
            if (!in_array($lead, $ids) && !in_array($sale, $ids)) {
                // ClientStatus::where('client_id', $client->id)->delete();
                ClientStatus::where('client_id', $client->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $client->statuses()->attach($lead, ['user_id' => Auth::user()->id]);
            }

            if (!$client->projects->pluck('id')->contains($request->input('project_id'))) {
                $client->projects()->attach($request->input('project_id'));
            }
        }

        $apartments = ApartmentViewing::where('viewing_id', $this->id)->pluck('apartment_id');
        $apartmentsRemove = $apartments->diff($request->input('apartments'));
        $apartmentsAdd = collect($request->input('apartments'))->diff($apartments);

        if ($apartmentsRemove) {
            $this->apartments()->detach($apartmentsRemove);
        }

        if ($apartmentsAdd) {
            $this->apartments()->attach($apartmentsAdd);
        }

        $this->statuses()->sync($request->input('status'));
    }

    public function preDestroy($ids)
    {
        return $this->find($ids)->pluck('client_id');
    }

    public function postDestroy($api, $ids, $rows)
    {
        $status = Status::where('parent', 2)->where('action', 'viewing')->value('id');
        $result = ClientStatus::whereIn('client_id', $rows)->where('status_id', $status)->update([
            'deleted_at' => Carbon::now(),
        ]);

        if ($result) {
            // make it an enquiry
            $status = Status::where('parent', 2)->where('default', 1)->value('id');
            foreach ($rows as $row) {
                $client = Client::findOrFail($row);
                $active = $client->statuses()->whereNull('client_status.deleted_at')->count();
                $viewings = Viewing::where('client_id', $row)->count();
                if (!$viewings && !$active) {
                    $client->statuses()->attach($status, ['user_id' => Auth::user()->id]);
                }
            }
        }
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'viewed_at', 'viewed');
        $data = Datatable::render($data, 'viewed', ['sort' => ['viewed_at' => 'timestamp']]);
        $data = Datatable::relationship($data, 'client_name', 'client');

        $data->first()->projectName = $this->project->name;

        $data->first()->agent = Agent::select('company AS agent')->where('id', $data->first()->agent_id)->value('agent');

        $data->first()->units = Apartment::selectRaw('GROUP_CONCAT(DISTINCT apartments.unit SEPARATOR ", ") AS units')
            ->leftJoin('apartment_viewing', 'apartment_viewing.apartment_id', '=', 'apartments.id')
            ->where('apartment_viewing.viewing_id', $data->first()->id)
            ->value('units');

        $data->first()->status = Status::selectRaw('GROUP_CONCAT(DISTINCT statuses.name SEPARATOR ", ") AS statuses')
            ->leftJoin('status_viewing', 'status_viewing.status_id', '=', 'statuses.id')
            ->where('status_viewing.viewing_id', $data->first()->id)
            ->value('statuses');

        $data = Datatable::popover($data, 'status');

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
                'id' => 'id',
                'name' => trans('labels.id'),
            ],
            [
                'id' => 'viewed',
                'name' => trans('labels.viewedAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
        ];

        if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'client')) {
            array_push($columns, [
                'id' => 'units',
                'name' => trans('labels.apartments'),
                'class' => 'vertical-center cell-max-15',
            ]);
        }

        if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'apartment')) {
            array_push($columns, [
                'id' => 'client_name',
                'name' => trans('labels.client'),
            ]);
        }

        array_push($columns, [
            'id' => 'agent',
            'name' => trans('labels.agent'),
            'class' => 'vertical-center',
        ]);

        array_push($columns, [
            'id' => 'status',
            'name' => trans('labels.status'),
            'class' => 'vertical-center popovers',
        ]);

        if (!session('project')) {
            array_push($columns, [
                'id' => 'projectName',
                'name' => trans('labels.project'),
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
                'visible' => Auth::user()->can('Create: Viewings'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Viewings'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Viewings'),
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
        $viewings = $this->with('client')->selectRaw($table . '.id, ' . $table . '.description, ' . $table . '.viewed_at, ' . $table . '.client_id, TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) AS projectName, agents.company AS agent, GROUP_CONCAT(DISTINCT apartments.unit ORDER BY CAST(apartments.unit AS UNSIGNED), apartments.unit SEPARATOR ", ") as units, GROUP_CONCAT(DISTINCT statuses.name SEPARATOR ", ") as status')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->leftJoin('status_viewing', 'status_viewing.viewing_id', '=', $table . '.id')
            ->leftJoin('statuses', 'statuses.id', '=', 'status_viewing.status_id')
            ->leftJoin('clients', 'clients.id', '=', $table . '.client_id')
            ->leftJoin('apartment_viewing', 'apartment_viewing.viewing_id', '=', $table . '.id')
            ->leftJoin('apartments', 'apartments.id', '=', 'apartment_viewing.apartment_id')
            ->leftJoin('agents', 'agents.id', '=', $table . '.agent_id')
            ->whereIn($table . '.project_id', Helper::project());

        if ($api->model->_parent) {
            if ($api->meta->_parent->model == 'client') {
                $viewings = $viewings->where($table . '.client_id', $api->model->_parent->id);
            } elseif ($api->meta->_parent->model == 'apartment') {
                $viewings = $viewings->where('apartment_viewing.apartment_id', $api->model->_parent->id);
            }
        }

        $viewings = $viewings->groupBy($table . '.id')
            ->orderBy($table . '.viewed_at', 'desc')
            ->get();

        $data = Datatable::format($viewings, 'date', 'd.m.Y', 'viewed_at', 'viewed');
        $data = Datatable::render($data, 'viewed', ['sort' => ['viewed_at' => 'timestamp']]);
        $data = Datatable::relationship($data, 'client_name', 'client');
        $data = Datatable::popover($data, 'status');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
