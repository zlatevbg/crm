<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Sale extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'closing_at',
        'promissory_at',
        'project_id',
        'apartment_id',
        'client_id',
        'user_id',
        'agent_id',
        'subagent_id',
        'price',
        'furniture',
        'commission',
        'sub_commission',
        'description',
        'lawyer',
    ];

    protected $dates = [
        'closing_at',
        'promissory_at',
        'deleted_at',
    ];

    protected $softCascade = [
        'paymentsSoftDelete',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Sales') && !Auth::user()->can('Edit: Sales') && !Auth::user()->can('Delete: Sales'));
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function subagent()
    {
        return $this->belongsTo(Agent::class, 'subagent_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class)/*->with('block')->with('floor')->with('bed')->with('view')->with('furniture')*/;
    }

    public function paymentsSoftDelete()
    {
        return $this->hasMany(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getClosingAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setClosingAtAttribute($value)
    {
        $this->attributes['closing_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getPromissoryAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setPromissoryAtAttribute($value)
    {
        $this->attributes['promissory_at'] = $value ? Carbon::parse($value) : null;
    }

    public function setFurnitureAttribute($value)
    {
        $this->attributes['furniture'] = $value ?: 0;
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function getCommission()
    {
        $commission = 0;
        /*$client = Client::findOrFail($this->client_id);
        if ($client->agent_id) {
            $agent = Agent::findOrFail($client->agent_id);
            $pivot = $agent->projects()->where('projects.id', $this->project_id)->first();
            if ($pivot) {
                $commission = $pivot->pivot->commission;
            }
        } else {
            // direct clients => Erwin gets his standard commission
        }*/

        if ($this->agent_id) {
            $agent = Agent::findOrFail($this->agent_id);
            $pivot = $agent->projects()->where('projects.id', $this->project_id)->first();
            if ($pivot) {
                $commission = $pivot->pivot->commission;
            }
        } else {
            // direct clients => Erwin gets his standard commission
        }

        return $commission;
    }

    public function getSubCommission()
    {
        $commission = 0;
        $agent = Agent::find($this->subagent_id);
        if ($agent) {
            $pivot = $agent->projects()->where('projects.id', $this->project_id)->first();
            if ($pivot) {
                $commission = $pivot->pivot->sub_commission;
            }
        }

        return $commission;
    }

    public function selectApartment($api = null)
    {
        return Apartment::selectRaw('apartments.id, apartments.price, CONCAT(apartments.unit, IF(ISNULL(blocks.name), "", " / "), COALESCE(blocks.name, "")) AS apartment, COALESCE(furniture.price, "0") AS furniture')
            ->leftJoin('blocks', 'blocks.id', '=', 'apartments.block_id')
            ->leftJoin('furniture', 'furniture.id', '=', 'apartments.furniture_id')
            ->whereIn('apartments.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
            ->whereNotExists(function ($query) use ($api) {
                $query->from('sales')->when($api, function ($q, $api) {
                    return $q->where('sales.apartment_id', '!=', $api->model->apartment_id);
                })->whereColumn('apartments.id', '=', 'sales.apartment_id')->whereNull('sales.deleted_at');
            })
            ->get();
    }

    public function selectClient($api = null)
    {
        // $agentsWithContracts = Agent::has('projects')->pluck('id');
        return Client::select('clients.id', 'clients.first_name', 'clients.last_name', 'clients.agent_id')
            ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
            ->whereIn('client_project.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
            ->/*where(function ($query) use ($agentsWithContracts) { // leftJoin('agents', 'agents.id', '=', 'clients.agent_id')->whereNull('agents.deleted_at')->
            $query->whereIn('agents.id', $agentsWithContracts)->orWhereNull('clients.agent_id');
        })->*/orderBy('clients.first_name')->orderBy('clients.last_name')->get();
    }

    public function selectAgents($api = null)
    {
        return Agent::select('agents.id', 'agents.company AS agent')
            ->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')
            ->whereIn('agent_project.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
            ->orderBy('agent')
            ->pluck('agent', 'id');
    }

    public function selectSubAgents($api = null)
    {
        return Agent::selectRaw('agents.id, agents.company as agent')
            ->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')
            ->whereIn('agent_project.project_id', $api ? array_wrap($api->model->project_id) : Helper::project())
            ->where('agents.type', 'direct')
            ->orderBy('agent')
            ->pluck('agent', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'closing_at' => 'nullable|required|date|after:today',
            'promissory_at' => 'nullable|present|date',
            'project_id' => 'required|numeric|exists:projects,id',
            'apartment_id' => 'required|numeric|exists:apartments,id',
            'client_id' => 'required|numeric|exists:clients,id',
            'price' => 'required|numeric|between:0,9999999.99',
            'furniture' => 'nullable|numeric|between:0,99999.99',
            'commission' => 'nullable|numeric|between:0,9999999.99',
            'agent_id' => 'nullable|numeric|exists:agents,id',
            'subagent_id' => 'nullable|numeric|exists:agents,id',
            'sub_commission' => 'nullable|numeric|between:0,9999999.99',
            'lawyer' => 'present|max:255',
            'description' => 'present',
        ];
    }

    public function updateRules($request, $api)
    {
        $rules = $this->createRules($request, $api);
        $rules['closing_at'] = 'nullable|required|date';

        return $rules;
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['commission'] = $request->input('commission') ?: 0;
        $data['sub_commission'] = $request->input('sub_commission') ?: 0;

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['commission'] = $request->input('commission') ?: 0;
        $data['sub_commission'] = $request->input('sub_commission') ?: 0;
        $data['old_apartment_id'] = $this->apartment_id;
        $data['old_client_id'] = $this->client_id;

        return $data;
    }

    public function postStore($api, $request)
    {
        $status = Status::where('parent', 1)->where('action', 'reserve')->value('id');
        if ($status) {
            $apartment = Apartment::findOrFail($request->input('apartment_id'));
            $ids = $apartment->statuses()->whereNull('apartment_status.deleted_at')->pluck('apartment_status.status_id')->all();
            if (!in_array($status, $ids)) {
                // ApartmentStatus::where('apartment_id', $apartment->id)->delete();
                ApartmentStatus::where('apartment_id', $apartment->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $apartment->statuses()->attach($status, ['user_id' => Auth::user()->id]);
            }
        }

        $status = Status::where('parent', 2)->where('action', 'deposit')->value('id');
        $client = Client::findOrFail($request->input('client_id'));
        $ids = $client->statuses()->whereNull('client_status.deleted_at')->pluck('client_status.status_id')->all();
        if (!in_array($status, $ids)) {
            ClientStatus::where('client_id', $client->id)->update([
                'deleted_at' => Carbon::now(),
            ]);
            $client->statuses()->attach($status, ['user_id' => Auth::user()->id]);
        }
    }

    public function postUpdate($api, $request, $data)
    {
        if ($this->apartment_id != $data['old_apartment_id']) {
            $status = Status::where('parent', 1)->where('action', 'reserve')->value('id');
            $apartment = Apartment::findOrFail($this->apartment_id);
            $ids = $apartment->statuses()->whereNull('apartment_status.deleted_at')->pluck('apartment_status.status_id')->all();
            if (!in_array($status, $ids)) {
                // ApartmentStatus::where('apartment_id', $apartment->id)->delete();
                ApartmentStatus::where('apartment_id', $apartment->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $apartment->statuses()->attach($status, ['user_id' => Auth::user()->id]);
            }

            $apartmentStatus = Status::where('parent', 1)->where('default', 1)->value('id');
            $apartment = Apartment::findOrFail($data['old_apartment_id']);
            ApartmentStatus::where('apartment_id', $apartment->id)->update([
                'deleted_at' => Carbon::now(),
            ]);
            $apartment->statuses()->attach($apartmentStatus, ['user_id' => Auth::user()->id]);
        }

        if ($this->client_id != $data['old_client_id']) {
            $status = Status::where('parent', 2)->where('action', 'deposit')->value('id');
            $client = Client::findOrFail($this->client_id);
            $ids = $client->statuses()->whereNull('client_status.deleted_at')->pluck('client_status.status_id')->all();
            if (!in_array($status, $ids)) {
                ClientStatus::where('client_id', $client->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
                $client->statuses()->attach($status, ['user_id' => Auth::user()->id]);
            }

            $clientStatus = Status::where('parent', 2)->where('action', 'deposit')->value('id');
            $viewingStatus = Status::where('parent', 2)->where('action', 'viewing')->value('id');
            $defaultStatus = Status::where('parent', 2)->where('default', 1)->value('id');
            $count = $this->where('client_id', $data['old_client_id'])->count();
            if (!$count) {
                ClientStatus::where('client_id', $data['old_client_id'])->where('status_id', $clientStatus)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                $client = Client::findOrFail($data['old_client_id']);
                $ids = $client->statuses()->whereNull('client_status.deleted_at')->pluck('client_status.status_id')->all();
                $viewings = Viewing::where('client_id', $data['old_client_id'])->count();
                if ($viewings) { // make it a Lead
                    if (!in_array($viewingStatus, $ids)) {
                        $client->statuses()->attach($viewingStatus, ['user_id' => Auth::user()->id]);
                    }
                } else { // make it an enquiry
                    if (!in_array($defaultStatus, $ids)) {
                        $client->statuses()->attach($defaultStatus, ['user_id' => Auth::user()->id]);
                    }
                }
            }
        }
    }

    public function preDestroy($ids)
    {
        return $this->select('apartment_id', 'client_id')->find($ids);
    }

    public function postDestroy($api, $ids, $rows)
    {
        $apartmentStatus = Status::where('parent', 1)->where('default', 1)->value('id');
        $clientStatus = Status::where('parent', 2)->where('action', 'deposit')->value('id');
        $viewingStatus = Status::where('parent', 2)->where('action', 'viewing')->value('id');
        $defaultStatus = Status::where('parent', 2)->where('default', 1)->value('id');
        foreach ($rows as $row) {
            $model = Apartment::find($row->apartment_id);
            // $model->statuses()->delete(); // doesn't work: https://github.com/laravel/framework/issues/13909
            ApartmentStatus::where('apartment_id', $model->id)->update([
                'deleted_at' => Carbon::now(),
            ]);
            $model->statuses()->attach($apartmentStatus, ['user_id' => Auth::user()->id]);

            $count = $api->model->where('client_id', $row->client_id)->count();
            if (!$count) {
                ClientStatus::where('client_id', $row->client_id)->where('status_id', $clientStatus)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                $client = Client::findOrFail($row->client_id);
                $ids = $client->statuses()->whereNull('client_status.deleted_at')->pluck('client_status.status_id')->all();
                $viewings = Viewing::where('client_id', $row->client_id)->count();
                if ($viewings) { // make it a Lead
                    if (!in_array($viewingStatus, $ids)) {
                        $client->statuses()->attach($viewingStatus, ['user_id' => Auth::user()->id]);
                    }
                } else { // make it an enquiry
                    if (!in_array($defaultStatus, $ids)) {
                        $client->statuses()->attach($defaultStatus, ['user_id' => Auth::user()->id]);
                    }
                }
            }
        }
    }

    public function datatable($api)
    {
        // $data = Datatable::nl2br($this, 'description');
        $data = Datatable::link($this, 'number', 'id', $api->meta->slug, true);
        // $data = Datatable::format($data, 'date', 'd.m.Y', 'closing_at', 'closing');
        // $data = Datatable::render($data, 'closing', ['sort' => ['closing_at' => 'timestamp']]);
        // $data = Datatable::format($data, 'date', 'd.m.Y', 'promissory_at', 'promissory');
        // $data = Datatable::render($data, 'promissory', ['sort' => ['promissory_at' => 'timestamp']]);

        $data->first()->price = $this->price + $this->furniture;
        $data->first()->balance = $this->price - ($this->payments->sum('amount') ?: 0);
        $data->first()->commission = $this->commission + $this->sub_commission;
        $data = Datatable::price($data, ['price', 'furniture', 'commission', 'balance']);

        // $data->first()->user = User::selectRaw('CONCAT(first_name, " ", COALESCE(last_name, "")) as user')->where('id', $data->first()->user_id)->value('user');

        $apartment = Apartment::select('apartments.id', 'apartments.unit', 'blocks.name AS block', 'beds.name AS bed')->leftJoin('blocks', 'apartments.block_id', '=', 'blocks.id')->leftJoin('beds', 'apartments.bed_id', '=', 'beds.id')->where('apartments.id', $data->first()->apartment_id)->first();
        $data->first()->apartment = $apartment->unit;
        $data->first()->block = $apartment->block;
        $data->first()->bed = $apartment->bed;
        $data->first()->projectName = $this->project->name;
        $data->first()->client = Client::selectRaw('CONCAT(first_name, " ", COALESCE(last_name, "")) as client')->where('id', $data->first()->client_id)->value('client');
        $data->first()->status = Status::select('statuses.name')->leftJoin('apartment_status', 'statuses.id', '=', 'apartment_status.status_id')->where('apartment_status.apartment_id', $apartment->id)->orderBy('apartment_status.created_at', 'desc')->limit(1)->value('name');
        $data->first()->agent = Agent::select('company AS agent')->where('id', $data->first()->agent_id)->value('agent');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->id,
            ],
        ];

        if (Auth::user()->can('View: Payments')) {
            $tabs = array_merge($tabs, [
                'payments' => [
                    'slug' => 'payments',
                    'name' => trans('buttons.payments'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Notes')) {
            $tabs = array_merge($tabs, [
                'notes' => [
                    'slug' => 'notes',
                    'name' => trans('buttons.notes'),
                    'overview' => [
                        'options' => [
                            'dom' => '<"card-block table-responsive"tr>',
                            'order' => false,
                            'class' => 'table-overview',
                        ],
                        'columns' => [
                            [
                                'id' => 'id',
                                'checkbox' => true,
                                'hidden' => (!Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Notes') && !Auth::user()->can('Edit: Notes') && !Auth::user()->can('Delete: Notes'))),
                            ],
                            [
                                'id' => 'created',
                                'name' => trans('labels.createdAt'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'user',
                                'name' => trans('labels.modifiedBy'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'title',
                                'name' => trans('labels.title'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'description',
                                'name' => trans('labels.description'),
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return collect($tabs);
    }

    public function dColumns()
    {
        $columns = [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
            [
                'id' => 'number',
                'name' => trans('labels.id'),
                'class' => 'vertical-center',
            ],/*
            [
                'id' => 'closing',
                'name' => trans('labels.closingAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'promissory',
                'name' => trans('labels.promissoryAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'user',
                'name' => trans('labels.modifiedBy'),
                'class' => 'vertical-center',
            ],*/
            [
                'id' => 'apartment',
                'name' => trans('labels.apartment'),
                'class' => 'text-center vertical-center',
            ],
            [
                'id' => 'block',
                'name' => trans('labels.block'),
                'class' => 'text-center vertical-center',
            ],
            [
                'id' => 'bed',
                'name' => trans('labels.bed'),
                'class' => 'text-center vertical-center',
            ],
            [
                'id' => 'client',
                'name' => trans('labels.client'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'agent',
                'name' => trans('labels.agent'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'price',
                'name' => trans('labels.price'),
                'class' => 'text-right vertical-center',
            ],
            [
                'id' => 'balance',
                'name' => trans('labels.balance'),
                'class' => 'text-right vertical-center',
            ],
            [
                'id' => 'commission',
                'name' => trans('labels.commission'),
                'class' => 'text-right vertical-center',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'vertical-center',
            ],/*
            [
                'id' => 'description',
                'name' => trans('labels.description'),
            ],*/
        ];

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
                'url' => Helper::route('api.create', $api->meta->slug),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Sales'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Sales'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Sales'),
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
        $sold = Apartment::selectRaw('id, (SELECT statuses.id FROM apartment_status LEFT JOIN statuses ON statuses.id = apartment_status.status_id WHERE apartment_status.apartment_id = apartments.id AND apartment_status.deleted_at IS NULL AND statuses.action = "final-balance" ORDER BY apartment_status.created_at DESC LIMIT 1) AS status')->havingRaw('status IS NOT NULL')->pluck('id');

        $table = str_plural($api->meta->model);
        $sales = $this->selectRaw($table . '.id, ' . $table . '.closing_at, ' . $table . '.promissory_at, (' . $table . '.price + ' . $table . '.furniture) as price, (' . $table . '.commission + ' . $table . '.sub_commission) AS commission, apartments.unit AS apartment, blocks.name AS block, beds.name AS bed, TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) AS projectName, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client, agents.company AS agent, (' . $table . '.price + ' . $table . '.furniture) - COALESCE(SUM(payments.amount), 0) AS balance, ' . DB::raw('(SELECT statuses.name FROM apartment_status LEFT JOIN statuses ON statuses.id = apartment_status.status_id WHERE apartment_status.apartment_id = apartments.id AND apartment_status.deleted_at IS NULL ORDER BY apartment_status.created_at DESC LIMIT 1) AS status'))
        ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
        ->leftJoin('apartments', 'apartments.id', '=', $table . '.apartment_id')
        ->leftJoin('blocks', 'apartments.block_id', '=', 'blocks.id')
        ->leftJoin('beds', 'apartments.bed_id', '=', 'beds.id')
        ->leftJoin('clients', $table . '.client_id', '=', 'clients.id')
        ->leftJoin('agents', $table . '.agent_id', '=', 'agents.id')
        // ->leftJoin('users', $table . '.user_id', '=', 'users.id')
        ->leftJoin('payments', function ($join) use ($table) {
            $join->on('payments.sale_id', '=', $table . '.id')->whereNull('payments.deleted_at');
        })
        ->whereIn($table . '.project_id', Helper::project());

        if ($api->model->_parent) {
            $sales = $sales->where($table . '.client_id', $api->model->_parent->id);
        } else {
            $sales = $sales->whereNotIn('apartments.id', $sold);
        }

        $sales = $sales->orderBy($table . '.id', 'desc')->groupBy($table . '.id')->get();

        $data = Datatable::price($sales, ['price', 'furniture', 'commission', 'balance']);
        $data = Datatable::link($data, 'number', 'id', $api->meta->slug, true);
        // $data = Datatable::nl2br($data, 'description');
        // $data = Datatable::format($data, 'date', 'd.m.Y', 'closing_at', 'closing');
        // $data = Datatable::render($data, 'closing', ['sort' => ['closing_at' => 'timestamp']]);
        // $data = Datatable::format($data, 'date', 'd.m.Y', 'promissory_at', 'promissory');
        // $data = Datatable::render($data, 'promissory', ['sort' => ['promissory_at' => 'timestamp']]);

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }

    public function homeView($api)
    {
        if (!in_array(session('project'), [0, $api->model->project_id])) {
            Session::forget('project');
        }

        if (!in_array($api->model->project_id, Helper::project())) {
            abort(403);
        }

        $buttonsAgent = [];

        $buttons = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug) . '/' . $api->id . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Sales'),
            ],
        ];

        $buttonsApartment = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->path) . '/' . (new Apartment)->getTable() . '/' . $api->model->apartment->id . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Apartments'),
            ],
        ];

        $buttonsClient = [];
        if ($api->model->client) {
            $buttonsClient = [
                'edit' => [
                    'url' => Helper::route('api.edit', (new Client)->getTable()) . '/' . $api->model->client->id . '?reload=true',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Clients'),
                ],
            ];
        }

        if ($api->model->agent) {
            $buttonsAgent = [
                'edit' => [
                    'url' => Helper::route('api.edit', (new Agent)->getTable()) . '/' . $api->model->agent->id . '?reload=true',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Agents'),
                ],
            ];
        }

        return view('sale.home', compact('api', 'buttons', 'buttonsApartment', 'buttonsClient', 'buttonsAgent'));
    }
}
