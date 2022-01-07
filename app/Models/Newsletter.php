<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Facades\Domain;
use App\Models\Library;
use App\Models\BackgroundSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Newsletter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject',
        'teaser',
        'template',
        'group',
        'source',
        'goldenvisa',
        'status',
        'projects',
        'recipients',
        'include_team',
    ];

    public $_parent;

    public $attachmentsMetaId = 27;
    public $imagesMetaId = 28;
    public $backgroundsMetaId = 30;

    protected $dates = [
        'sent_at',
        'deleted_at',
    ];

    public $groups = [
        'agent-contacts',
        'clients',
        'investors',
        'guests',
        'gvcontacts',
        'rental-contacts',
        'mespil',
        'ph',
        'pgv',
    ];

    public $templates = [
        'www-mespil-ie',
        'www-pinehillsvilamoura-com',
        'www-portugal-golden-visa-pt',
        'previsão-optima',
    ];

    public $placeholders = [
        'name' => 'NAME',
        'first_name' => 'FIRST_NAME',
        'last_name' => 'LAST_NAME',
        'company' => 'COMPANY',
        'email' => 'EMAIL',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Newsletter') && !Auth::user()->can('Edit: Newsletter') && !Auth::user()->can('Delete: Newsletter'));
    }

    public function agentContacts()
    {
        return $this->belongsToMany(AgentContact::class)->withTimestamps()->where('newsletters', 1)->whereNotNull('email');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class)->withTimestamps()->where('newsletters', 1)->whereNotNull('email');
    }

    public function investors()
    {
        return $this->belongsToMany(Investor::class)->withTimestamps()->where('newsletters', 1)->whereNotNull('email');
    }

    public function guests()
    {
        return $this->belongsToMany(Guest::class)->withTimestamps()->where('newsletters', 1)->whereNotNull('email');
    }

    public function subscriber($website)
    {
        return $this->belongsToMany(Subscriber::class)->withTimestamps()->where('is_subscribed', 1)->where('website', $website)->whereNotNull('email');
    }

    public function gvcontacts()
    {
        return $this->belongsToMany(Gvcontact::class)->withTimestamps()->where('is_subscribed', 1)->whereNotNull('email');
    }

    public function rentalContacts()
    {
        return $this->belongsToMany(RentalContact::class)->withTimestamps()->where('is_subscribed', 1)->whereNotNull('email');
    }

    public function textSections()
    {
        return $this->hasMany(TextSection::class)->orderBy('order');
    }

    public function backgroundSections()
    {
        return $this->hasMany(BackgroundSection::class)->orderBy('order');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'model_id')->where('meta_id', $this->attachmentsMetaId);
    }

    public function setRecipientsAttribute($value)
    {
        $this->attributes['recipients'] = $value ? implode(',', $value) : null;
    }

    public function getRecipientsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = ($value && is_array($value)) ? implode(',', $value) : $value;
    }

    public function setSourceAttribute($value)
    {
        $this->attributes['source'] = ($value && is_array($value)) ? implode(',', $value) : $value;
    }

    public function getStatusAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getSourceAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setProjectsAttribute($value)
    {
        $this->attributes['projects'] = ($value && is_array($value)) ? implode(',', $value) : $value;
    }

    public function getProjectsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getSentAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setSentAtAttribute($value)
    {
        $this->attributes['sent_at'] = $value ? Carbon::parse($value) : null;
    }

    public function selectGroup()
    {
        return [
            '' => trans('labels.all'),
            'agent-contacts' => trans('labels.agent-contacts'),
            'clients' => trans('labels.clients'),
            'investors' => trans('labels.investors'),
            'guests' => trans('labels.guests'),
            'gvcontacts' => trans('labels.gvcontacts'),
            'rental-contacts' => trans('labels.rentalContacts'),
            'mespil' => trans('labels.mespil'),
            'ph' => trans('labels.ph'),
            'pgv' => trans('labels.pgv'),
        ];
    }

    public function selectTemplate()
    {
        return [
            '' => trans('labels.default'),
            'www-mespil-ie' => trans('labels.wwwMespilIe'),
            'www-pinehillsvilamoura-com' => trans('labels.wwwPinehillsvilamouraCom'),
            'www-portugal-golden-visa-pt' => trans('labels.wwwPortugalGoldenVisaPt'),
            'previsão-optima' => trans('labels.previsãoOptima'),
        ];
    }

    public function selectGoldenVisa()
    {
        return [
            '' => trans('text.pleaseSelect'),
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function selectRecipients($status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        if ($this->group == 'agent-contacts') {
            return Agent::select('agents.id', 'agents.company AS recipient')->leftJoin('agent_contacts', 'agent_contacts.agent_id', '=', 'agents.id')->where('agent_contacts.newsletters', 1)->whereNotNull('agent_contacts.email')->when($projects, function ($query) use ($projects) {
                return $query->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereNull('agent_project.deleted_at')->whereIn('agent_project.project_id', $projects);
            })->when(($goldenvisa === 0 || $goldenvisa === 1), function ($query) use ($goldenvisa) {
                return $query->where('goldenvisa', $goldenvisa);
            })->groupBy('agents.id')->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'clients') {
            return Client::selectRaw('clients.id, TRIM(CONCAT(clients.first_name, " ", COALESCE(clients.last_name, ""))) AS recipient')->when($status, function ($query) use ($status) {
                return $query->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')->whereNull('client_status.deleted_at')->whereIn('client_status.status_id', $status);
            })->when($projects, function ($query) use ($projects) {
                return $query->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->whereNull('client_project.deleted_at')->whereIn('client_project.project_id', $projects);
            })->where('clients.newsletters', 1)->whereNotNull('clients.email')->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'investors') {
            return Investor::selectRaw('investors.id, TRIM(CONCAT(investors.first_name, " ", COALESCE(investors.last_name, ""))) AS recipient')->where('investors.newsletters', 1)->when($projects, function ($query) use ($projects) {
                return $query->leftJoin('investor_project', 'investor_project.investor_id', '=', 'investors.id')->whereNull('investor_project.deleted_at')->whereIn('investor_project.project_id', $projects);
            })->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'guests') {
            return Guest::selectRaw('id, TRIM(CONCAT(first_name, " ", COALESCE(last_name, ""))) AS recipient')->where('newsletters', 1)->when($projects, function ($query) use ($projects) {
                return $query->whereIn('project_id', $projects);
            })->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'gvcontacts') {
            return Gvcontact::select('id', 'email AS recipient')->when($source, function ($query) use ($source) {
                return $query->whereIn('type', $source);
            })->where('is_subscribed', 1)->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'rental-contacts') {
            return RentalContact::select('id', 'email AS recipient')->where('is_subscribed', 1)->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'mespil') {
            return Subscriber::select('id', 'email AS recipient')->when($source, function ($query) use ($source) {
                return $query->whereIn('source', $source);
            })->where('is_subscribed', 1)->where('website', 'mespil')->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'ph') {
            return Subscriber::select('id', 'email AS recipient')->when($source, function ($query) use ($source) {
                return $query->whereIn('source', $source);
            })->where('is_subscribed', 1)->where('website', 'ph')->orderBy('recipient')->pluck('recipient', 'id');
        } elseif ($this->group == 'pgv') {
            return Subscriber::select('id', 'email AS recipient')->when($source, function ($query) use ($source) {
                return $query->whereIn('source', $source);
            })->where('is_subscribed', 1)->where('website', 'pgv')->orderBy('recipient')->pluck('recipient', 'id');
        }

        return [];
    }

    public function selectSource()
    {
        if ($this->group == 'gvcontacts') {
            return [
                'agent' => trans('text.gvagent'),
                'client' => trans('text.gvclient'),
            ];
        } elseif ($this->group == 'ph') {
            return [
                'subscribe-newsletter' => trans('text.subscribeNewsletter'),
            ];
        } elseif ($this->group == 'mespil') {
            return [
                'subscribe-newsletter' => trans('text.subscribeNewsletter'),
                'join-investor-club' => trans('text.joinInvestorClub'),
                'download-investor-brochure' => trans('text.downloadInvestorBrochure'),
            ];
        } elseif ($this->group == 'pgv') {
            return [
                'subscribe-newsletter' => trans('text.subscribeNewsletter'),
            ];
        }
    }

    public function selectStatus()
    {
        return Status::select('name', 'id')->where('parent', 2)->orderBy('name')->get()->pluck('name', 'id');
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectIncludeTeam()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function createRules($request, $api)
    {
        return [
            'subject' => 'required|max:255',
            'teaser' => 'present|max:255',
            'template' => 'nullable|present|in:' . implode(',', $this->templates),
            'group' => 'nullable|present|in:' . implode(',', $this->groups),
            'source' => 'nullable|array',
            'status' => 'nullable|array',
            'projects' => 'nullable|array',
            'goldenvisa' => 'nullable|present|in:0,1',
            'recipients' => 'array',
        ];
    }

    /*public function postStore($api, $request)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }
    }*/

    public function postUpdate($api, $request)
    {
        $this->sent_at = null;
        $this->save();
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['source'] = $data['source'] ?? null;
        $data['status'] = $data['status'] ?? null;
        $data['projects'] = $data['projects'] ?? null;
        $data['recipients'] = $data['recipients'] ?? null;

        return $data;
    }

    // SOFT DELETE !
    /*public function postDestroy($api, $ids, $rows)
    {
        Library::whereIn('meta_id', [$this->attachmentsMetaId, $this->imagesMetaId])->whereIn('model_id', $ids)->delete();

        foreach ($ids as $id) {
            Storage::disk('public')->deleteDirectory($this->attachmentsMetaId . DIRECTORY_SEPARATOR . $id);
            Storage::disk('public')->deleteDirectory($this->imagesMetaId . DIRECTORY_SEPARATOR . $id);
        }

        $ids = BackgroundSection::whereIn('newsletter_id', $ids)->get();
        Library::where('meta_id', $this->backgroundsMetaId)->whereIn('model_id', $ids)->delete();

        foreach ($ids as $id) {
            Storage::disk('public')->deleteDirectory($this->backgroundsMetaId . DIRECTORY_SEPARATOR . $id);
        }
    }*/

    public function datatable($api)
    {
        $data = Datatable::link($this, 'subject', 'subject', $api->meta->slug, true);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'sent_at', 'sent');
        $data = Datatable::render($data, 'sent', ['sort' => ['sent_at' => 'timestamp']]);
        $data = Datatable::trans($data, 'group', 'labels');
        $data = Datatable::default($data, 'group', trans('labels.all'));
        $data = Datatable::trans($data, 'template', 'labels');
        $data = Datatable::default($data, 'template', trans('labels.default'));
        $data = Datatable::actions($api, $data);
        $data = Datatable::trans($data, 'source', 'text');

        $statuses = Status::select('id', 'name')->where('parent', 2)->get();

        $data = Datatable::default($data, 'status', function ($item) use ($statuses) {
            if ($item->status) {
                return $statuses->whereIn('id', $item->status)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        $projects = Project::selectRaw('id, TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS name')->where('status', 1)->get();

        $data = Datatable::default($data, 'projects', function ($item) use ($projects) {
            if ($item->projects) {
                return $projects->whereIn('id', $item->projects)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->subject,
            ],
        ];

        if (Auth::user()->can('View: Text Sections')) {
            $tabs = array_merge($tabs, [
                'text-sections' => [
                    'slug' => 'text-sections',
                    'name' => trans('buttons.text-sections'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Background Sections')) {
            $tabs = array_merge($tabs, [
                'background-sections' => [
                    'slug' => 'background-sections',
                    'name' => trans('buttons.background-sections'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Attachments')) {
            $tabs = array_merge($tabs, [
                'attachments' => [
                    'slug' => 'attachments',
                    'name' => trans('buttons.attachments'),
                ],
            ]);
        }

        return collect($tabs);
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
                'id' => 'subject',
                'name' => trans('labels.subject'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'template',
                'name' => trans('labels.template'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'group',
                'name' => trans('labels.group'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'projects',
                'name' => trans('labels.projects'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'source',
                'name' => trans('labels.source'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'sent',
                'name' => trans('labels.sentAt'),
                'render' =>  ['sort'],
                'class' => 'text-center vertical-center',
            ],
            [
                'id' => 'actions',
                'name' => trans('labels.actions'),
                'class' => 'text-right datatable-actions vertical-center',
                'order' => false,
            ],
        ];
    }

    public function dButtons($api)
    {
        return [
            /*'send' => [
                'url' => Helper::route('api.send-newsletter', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-ajax data-append-id',
                'class' => 'btn-primary',
                'icon' => 'envelope',
                'method' => 'get',
                'name' => trans('buttons.sendNewsletter'),
                'visible' => Auth::user()->can('Send Newsletter'),
            ],
            'test' => [
                'url' => Helper::route('api.test-newsletter', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-ajax data-append-id',
                'class' => 'btn-info mr-auto',
                'icon' => 'at',
                'method' => 'get',
                'name' => trans('buttons.testNewsletter'),
                'visible' => Auth::user()->can('Test Newsletter'),
            ],*/
            'create' => [
                'url' => Helper::route('api.create', $api->meta->slug),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Newsletter'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Newsletter'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Newsletter'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 6 : 7, 'desc'],
        ];
    }

    public function dActions($api)
    {
        return [
            'send' => [
                'url' => Helper::route('api.send-newsletter', $api->meta->slug),
                'parameters' => 'data-ajax',
                'class' => 'btn-primary',
                'icon' => 'envelope',
                'method' => 'get',
                'name' => trans('buttons.send'),
                'hide' => 'sent_at',
                'visible' => Auth::user()->can('Send Newsletter'),
            ],
            'test' => [
                'url' => Helper::route('api.test-newsletter', $api->meta->slug),
                'parameters' => 'data-ajax',
                'class' => 'btn-info',
                'icon' => 'at',
                'method' => 'get',
                'name' => trans('buttons.test'),
                'hide' => 'sent_at',
                'visible' => Auth::user()->can('Test Newsletter'),
            ],
        ];
    }

    public function dData($api)
    {
        $websites = [
            1 => 'www-mespil-ie',
            2 => [
                'www-pinehillsvilamoura-com',
                'previsão-optima',
            ],
            3 => 'www-portugal-golden-visa-pt',
        ];

        $templates = [];
        foreach (Auth::user()->websites->pluck('id') as $id) {
            if (isset($websites[$id])) {
                if (is_array($websites[$id])) {
                    foreach ($websites[$id] as $template) {
                        array_push($templates, $template);
                    }
                } else {
                    array_push($templates, $websites[$id]);
                }
            }
        }

        $data = $this->select('id', 'subject', 'sent_at', 'template', 'group', 'source', 'status', 'projects')->when($templates, function ($query) use ($templates) {
            return $query->whereIn('template', $templates)->orWhereNull('template');
        })->orderByRaw('NOT ISNULL(sent_at)')->orderBy('sent_at', 'desc')->get();
        $data = Datatable::format($data, 'date', 'd.m.Y', 'sent_at', 'sent');
        $data = Datatable::render($data, 'sent', ['sort' => ['sent_at' => 'timestamp']]);
        $data = Datatable::trans($data, 'group', 'labels');
        $data = Datatable::default($data, 'group', trans('labels.all'));
        $data = Datatable::trans($data, 'template', 'labels');
        $data = Datatable::default($data, 'template', trans('labels.default'));
        $data = Datatable::actions($api, $data);
        $data = Datatable::trans($data, 'source', 'text');

        $statuses = Status::select('id', 'name')->where('parent', 2)->get();

        $data = Datatable::default($data, 'status', function ($item) use ($statuses) {
            if ($item->status) {
                return $statuses->whereIn('id', $item->status)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        $projects = Project::selectRaw('id, TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS name')->where('status', 1)->get();

        $data = Datatable::default($data, 'projects', function ($item) use ($projects) {
            if ($item->projects) {
                return $projects->whereIn('id', $item->projects)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        return Datatable::link($data, 'subject', 'subject', $api->path, true);
    }

    public function homeView($api)
    {
        $html = '<style type="text/css">
            @media only screen and (min-width:480px) {
              .mj-column-per-66 {
                width: 66.66666666666666%!important
              }

              .mj-column-per-33 {
                width: 33.33333333333333%!important
              }

              .mj-column-per-100 {
                width: 100%!important
              }

              .mj-column-per-25 {
                width: 25%!important
              }

              .mj-column-per-50 {
                width: 50%!important
              }
            }

            @media only screen and (max-width:480px) {
              .mobile-center {
                text-align: center!important
              }
            }
        </style>';

        $images = Helper::getTemplateImages($api->model, true);

        $body = '';

        foreach ($api->model->backgroundSections()->where('position', 'header')->get() as $backgroundSection) {
            $body .= view('newsletter.background', compact('backgroundSection'))->render();

            if ($backgroundSection->image) {
                array_push($images, ['filePath' => Helper::autover('/storage/' . $api->model->backgroundsMetaId . '/' . $backgroundSection->id . '/' . $backgroundSection->image->uuid . '/' . $backgroundSection->image->file), 'filename' => $backgroundSection->image->file]);
            }
        }

        foreach ($api->model->textSections as $textSection) {
            $body .= view('newsletter.text', compact('textSection'))->render();

            foreach ($textSection->images as $image) {
                array_push($images, ['filePath' => Helper::autover('/storage/' . $api->model->imagesMetaId . '/' . $textSection->id . '/' . $image->uuid . '/' . $image->file), 'filename' => $image->file]);
            }
        }

        foreach ($api->model->backgroundSections()->where('position', 'footer')->get() as $backgroundSection) {
            $body .= view('newsletter.background', compact('backgroundSection'))->render();

            if ($backgroundSection->image) {
                array_push($images, ['filePath' => Helper::autover('/storage/' . $api->model->backgroundsMetaId . '/' . $backgroundSection->id . '/' . $backgroundSection->image->uuid . '/' . $backgroundSection->image->file), 'filename' => $backgroundSection->image->file]);
            }
        }

        foreach ($api->model->placeholders as $placeholder) {
            if (mb_strpos($body, $placeholder) !== false) {
                $body = preg_replace('/\[\[' . $placeholder . '\]\]/', '<span style="background-color: #ff0;">' . $placeholder . '</span>', $body);
            }
        }

        $template = view('newsletter.templates.' . ($api->model->template ?: 'default'), compact('api', 'body'))->render();
        if (preg_match('/<body.*?>(.*)<\/body>/sm', $template, $matches)) {
            $html .= $matches[1];
        }

        foreach ($images as $image) {
            $html = preg_replace('/cid:' . preg_quote($image['filename']) . '/', $image['filePath'], $html);
        }

        return view('newsletter.home', compact('api', 'html'));
    }

    public function actions($api)
    {
        return [
            'edit' => [
                'url' => Helper::route('api.edit', $api->path) . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Newsletter'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path) . '?reload=true',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Newsletter'),
            ],
        ];
    }
}
