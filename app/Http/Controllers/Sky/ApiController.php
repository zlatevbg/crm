<?php

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Requests\Store;
use App\Services\Api;
use App\Services\Helper;
use App\Services\FineUploader;
use App\Facades\Domain;
use App\Models\Project;
use App\Models\Apartment;
use App\Models\Client;
use App\Models\Investor;
use App\Models\Guest;
use App\Models\Subscriber;
use App\Models\Gvcontact;
use App\Models\RentalContact;
use App\Models\Agent;
use App\Models\AgentContact;
use App\Models\Target;
use App\Models\Status;
use App\Models\TaskStatus;
use App\Models\Block;
use App\Models\Floor;
use App\Models\Bed;
use App\Models\Question;
use App\Models\View as ModelView;
use App\Models\Furniture;
use App\Models\User;
use Mailgun\Mailgun;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Http\Client\Curl\Client as CurlClient;
use Http\Message\MessageFactory\DiactorosMessageFactory;
use Http\Message\StreamFactory\DiactorosStreamFactory;

class ApiController extends Controller
{
    public function __invoke(Request $request, $path = null)
    {
        $segment = $request->segment(1);
        $user = Auth::user();

        if ($segment == 'statuses' && !$user->can('View: Statuses')) {
            abort(403);
        }

        if ($segment == 'sources' && !$user->can('View: Sources')) {
            abort(403);
        }

        if ($segment == 'features' && !$user->can('View: Project Features')) {
            abort(403);
        }

        if ($segment == 'categories' && !$user->can('View: Categories')) {
            abort(403);
        }

        if ($segment == 'activities' && !$user->can('View: Activities')) {
            abort(403);
        }

        $api = new Api($path);

        if (method_exists($api->model, 'homeView') && $api->id) {
            return $api->model->homeView($api);
        } else {
            return view('layouts.main', compact('api'));
        }
    }

    public function create(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Create')) {
            abort(403);
        }

        $action = $request->input('view', 'create');
        $view = method_exists($api->model, 'viewName') ? $api->model->viewName($api, $action) : $api->meta->model . '.' . $action;

        $content = View::exists($view) ? view($view, compact('api'))->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content,
        ]);
    }

    public function store(Store $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Store')) {
            abort(403);
        }

        $model = method_exists($api->model, 'storeModel') ? $api->model->storeModel($request) : null;

        $model = method_exists($api->model, 'restoreData') ? $api->model->restoreData($api, $request) : $model;

        if (!$model) {
            $data = method_exists($api->model, 'storeData') ? $api->model->storeData($api, $request) : $request->all();
            $model = $api->model->create($data);
        }

        if ($model->id) {
            if (method_exists($model, 'postStore')) {
                $model->postStore($api, $request);
            }

            $datatables = [
                'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => [
                    'added' => $model->datatable($api),
                ],
            ];

            return back()->with('closeModal', true)->with('datatables', $datatables);
        } else {
            return back()->withErrors(trans('text.saveError'));
        }
    }

    public function edit(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Edit')) {
            abort(403);
        }

        $reload = $request->has('reload');
        $action = $request->input('view', 'edit');
        $view = method_exists($api->model, 'viewName') ? $api->model->viewName($api, $action) : $api->meta->model . '.' . $action;

        $content = View::exists($view) ? view($view, compact('api', 'reload'))->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content,
        ]);
    }

    public function update(Store $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Update')) {
            abort(403);
        }

        $reload = $request->has('reload');

        $updated = method_exists($api->model, 'updateModel') ? $api->model->updateModel($api, $request) : null;

        if (!$updated) {
            $data = method_exists($api->model, 'updateData') ? $api->model->updateData($request) : $request->all();
            $updated = $api->model->update($data);
        }

        if ($updated) {
            if (method_exists($api->model, 'postUpdate')) {
                $reload = $api->model->postUpdate($api, $request, $data);
            }

            if (!$reload) {
                $datatables = [
                    'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => [
                        'updated' => $api->model->datatable($api),
                    ],
                ];
            }

            return $request->has('reload') ? back()->with('reload', true) : back()->with('closeModal', true)->with('datatables', $datatables);
        } else {
            return back()->withErrors(trans('text.saveError'));
        }
    }

    public function delete(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Delete')) {
            abort(403);
        }

        $reload = $request->has('reload');
        $overview = $request->has('overview');
        $content = View::first([$api->meta->model . '.delete', 'delete'], compact('api', 'reload', 'overview'))->renderSections();
        return response()->json([
            'modal' => $content,
        ]);
    }

    public function destroy(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Destroy')) {
            abort(403);
        }

        $reload = $request->has('reload');
        $ids = $request->has('ids') ? explode(',', $request->input('ids')) : [$api->id];

        if (method_exists($api->model, 'preDestroy')) {
            $rows = $api->model->preDestroy($ids);
        }

        if ($api->model->destroy($ids)) {
            if (method_exists($api->model, 'postDestroy')) {
                $reload = $api->model->postDestroy($api, $ids, $rows ?? []);
            }

            if (!$reload) {
                $datatables = [
                    'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => [
                        'deleted' => collect($ids)->map(function ($item, $key) {
                            return '#' . $item;
                        })->implode(','),
                    ],
                ];
            }

            if ($request->has('reload')) {
                return redirect(str_before(url()->previous(), '/' . $api->id));
            } else {
                return $reload ? back()->with('reload', true) : back()->with('closeModal', true)->with('datatables', $datatables);
            }
        } else {
            return back()->withErrors(trans('text.deleteError'));
        }
    }

    public function order(Request $request, $path)
    {
        if (!$request->all()) {
            return;
        }

        if (!Auth::user()->can('Reorder')) {
            abort(403);
        }

        $api = new Api($path);

        $columns = collect(Schema::getColumnListing($api->model->getTable()))->filter(function ($value, $key) {
            return !in_array($value, ['id', 'order', 'created_at', 'updated_at', 'deleted_at']);
        });

        $values = '';
        foreach ($request->all() as $key => $value) {
            $values .= '(' . $key . ',' . $value . str_repeat(',' . ($columns->contains('action') ? 'NULL' : 0), $columns->count()) . '),';
        }

        DB::statement('INSERT INTO ' . $api->model->getTable() . ' (`id`, `order`' . ($columns->count() ? ', `' . implode('`, `', $columns->all()) . '`' : '') . ') VALUES ' . rtrim($values, ',') . ' ON DUPLICATE KEY UPDATE `order` =VALUES(`order`)');
    }

    public function move(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Move')) {
            abort(403);
        }

        $action = $request->input('view', 'move');
        $view = method_exists($api->model, 'viewName') ? $api->model->viewName($api, $action) : $api->meta->model . '.' . $action;

        $categories = Question::whereNull('parent')->get();

        $content = View::exists($view) ? view($view, compact('api', 'categories'))->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content,
        ]);
    }

    public function moveConfirm(Request $request, $path)
    {
        if (!$request->all()) {
            return;
        }

        if (!Auth::user()->can('Move')) {
            abort(403);
        }

        $api = new Api($path);

        $ids = explode(',', $request->input('ids'));

        $parent = $request->input('category');
        $questions = Question::whereNotNull('parent')->where('parent', '!=', $request->input('category'))->findOrFail($ids);
        if ($questions->first()->parent != $parent) {
            $order = $questions->first()->where('parent', $parent)->max('order');
            foreach ($questions as $question) {
                DB::statement('SET @pos := 0');
                DB::update('update ' . $api->model->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE `parent` = ? AND `id` != ? ORDER BY `order`', [$question->parent, $question->id]);

                $question->order = ++$order;
                $question->parent = $parent;
                $question->save();
            }
        }

        /*$datatables = [
            'datatable-' . $api->meta->model => [
                'deleted' => collect($ids)->map(function ($item, $key) {
                    return '#' . $item;
                })->implode(','),
            ],
        ];*/

        return back()->with('closeModal', true)->with('reload', true); // ->with('datatables', $datatables);
    }

    public function upload(Request $request, FineUploader $uploader, $path)
    {
        if ($request->input('id')) {
            $path .= '/' . (is_array($request->input('id')) ? current($request->input('id')) : $request->input('id'));
        }

        $api = new Api($path);

        if (!Auth::user()->can('Upload')) {
            abort(403);
        }

        if (method_exists($api->model->_parent, 'upload')) {
            $uploader = $api->model->_parent->upload($uploader);
        } elseif (method_exists($api->model, 'upload')) {
            $uploader = $api->model->upload($uploader);
        } else {
            $uploader->isImage = false;
            $uploader->allowedExtensions = config('upload.fileExtensions');
        }

        $uploader->uploadDirectory = $api->model->uploadDirectory($api);
        $disk = 'public';
        if ($api->model->_parent->disks && $api->model->_parent->_parent) {
            $disk = $api->model->_parent->disks[$api->model->_parent->_parent->id];
        }
        if (!Storage::disk($disk)->exists($uploader->uploadDirectory)) {
            Storage::disk($disk)->makeDirectory($uploader->uploadDirectory);
        }

        if (array_key_exists('done', $request->query())) {
            $response = $uploader->combineChunks();
        } else {
            $response = $uploader->handleUpload();
        }

        if (isset($response['success']) && isset($response['fileName'])) {
            if ($request->input('id')) {
                $data = ['updated' => $api->model->datatableUpdated($api, $response)];
            } else {
                $data = ['added' => $api->model->datatableAdded($api, $request, $response)];
            }

            $response = [
                'success' => true,
                'data' => [
                    'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => $data,
                ],
            ];
        }

        return response()->json($response, $uploader->getStatus())->header('Content-Type', 'text/plain');
    }

    public function status(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Change Status')) {
            abort(403);
        }

        $status = $request->input('status');
        $id = $request->input('id');
        $value = $request->input('value');

        $model = $api->model->findOrFail($id);
        $model->$status = $value;
        $model->save();

        return back()->with('status', [
            'href' => Helper::route('api.status', $path) . '?status=' . $status . '&id=' . $id . '&value=' . !$value,
            'title' => trans('text.status' . ($value ? 'On' : 'Off')),
            'icon' => $value ? 'check-square' : 'square',
        ]);
    }

    public function download(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Download')) {
            abort(403);
        }

        $directory = $api->model->meta_id . DIRECTORY_SEPARATOR . $api->model->model_id . ($api->model->parent ? DIRECTORY_SEPARATOR . $api->model->parent : '');

        // redirect: Storage::disk('public')->url($directory . '/' . $api->model->uuid . '/' . $api->model->file)
        return Response::download(Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($directory . DIRECTORY_SEPARATOR . $api->model->uuid . DIRECTORY_SEPARATOR . $api->model->file), null, ['Cache-Control' => 'no-cache, no-store, must-revalidate', 'Pragma' => 'no-cache', 'Expires' => '0']);
    }

    /*public function modalLibrary(Request $request, $id = null)
    {
        $apiModal = new Api('modal-library' . ($id ? '/' . $id : ''));

        $datatables = $this->datatables($apiModal);

        $view = $apiModal->meta->slug;
        $content = View::exists($view) ? view($view, compact('datatables'))->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content,
        ]);
    }*/

    public function deleteImage(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Delete Photo')) {
            abort(403);
        }

        $datatables = [
            'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => [
                'updated' => $api->model->datatableUpdated($api),
            ],
        ];

        return back()->with('datatables', $datatables);
    }

    public function getClientsSmsRecipients($api, $status = null, $projects = null)
    {
        $recipients = Client::select('clients.id', 'clients.phone_code', 'clients.phone_number')->where('clients.sms', 1)->when($status, function ($query) use ($status) {
            return $query->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')->whereNull('client_status.deleted_at')->whereIn('client_status.status_id', $status);
        })->when($projects, function ($query) use ($projects) {
            return $query->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->whereNull('client_project.deleted_at')->whereIn('client_project.project_id', explode(',', $projects));
        })->whereNotNull('clients.phone_number')->whereNotExists(function ($query) use ($api) {
            $query->from('client_sms')->whereColumn('client_sms.client_id', 'clients.id')->where('client_sms.sms_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('clients.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getCustomSmsRecipients($api, $status = null, $projects = null)
    {
        $recipients = [];
        $numbers = explode("\n", str_replace("\r", "", $api->model->numbers));
        foreach ($numbers as $number) {
            array_push($recipients, preg_replace('/^00/', '', str_replace([' ', '-', '.', '/', '+'], '', $number)));
        }

        return $recipients;
    }

    public function getClientsRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Client::select('clients.id', 'clients.first_name', 'clients.last_name', 'clients.email')->where('clients.newsletters', 1)->when($status, function ($query) use ($status) {
            return $query->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')->whereNull('client_status.deleted_at')->whereIn('client_status.status_id', $status);
        })->when($projects, function ($query) use ($projects) {
            return $query->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->whereNull('client_project.deleted_at')->whereIn('client_project.project_id', $projects);
        })->whereNotNull('clients.email')->whereNotExists(function ($query) use ($api) {
            $query->from('client_newsletter')->whereColumn('client_newsletter.client_id', 'clients.id')->where('client_newsletter.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('clients.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getAgentContactsRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = AgentContact::select('agent_contacts.*', 'agents.company')->leftJoin('agents', 'agents.id', '=', 'agent_contacts.agent_id')->where('agent_contacts.newsletters', 1)->whereNotNull('agent_contacts.email')->whereNull('agents.deleted_at')->when($projects, function ($query) use ($projects) {
            return $query->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereNull('agent_project.deleted_at')->whereIn('agent_project.project_id', $projects);
        })->when(($goldenvisa === 0 || $goldenvisa === 1), function ($query) use ($goldenvisa) {
            return $query->where('goldenvisa', $goldenvisa);
        })->whereNotExists(function ($query) use ($api) {
            $query->from('agent_contact_newsletter')->whereColumn('agent_contact_newsletter.agent_contact_id', 'agent_contacts.id')->where('agent_contact_newsletter.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('agents.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getInvestorsRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Investor::where('investors.newsletters', 1)->when($projects, function ($query) use ($projects) {
            return $query->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereNull('agent_project.deleted_at')->whereIn('agent_project.project_id', $projects);
        })->whereNotNull('investors.email')->whereNotExists(function ($query) use ($api) {
            $query->from('investor_newsletter')->whereColumn('investor_newsletter.investor_id', 'investors.id')->where('investor_newsletter.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('investors.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getGuestsRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Guest::where('guests.newsletters', 1)->when($projects, function ($query) use ($projects) {
            return $query->whereIn('guests.project_id', $projects);
        })->whereNotNull('guests.email')->whereNotExists(function ($query) use ($api) {
            $query->from('guest_newsletter')->whereColumn('guest_newsletter.guest_id', 'guests.id')->where('guest_newsletter.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('guests.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getGvcontactsRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Gvcontact::when($source, function ($query) use ($source) {
                return $query->whereIn('type', $source);
            })->where('gvcontacts.is_subscribed', 1)->whereNotNull('gvcontacts.email')->whereNotExists(function ($query) use ($api) {
            $query->from('gvcontact_newsletter')->whereColumn('gvcontact_newsletter.gvcontact_id', 'gvcontacts.id')->where('gvcontact_newsletter.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('gvcontacts.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getRentalContactsRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = RentalContact::where('rental_contacts.is_subscribed', 1)->whereNotNull('rental_contacts.email')->whereNotExists(function ($query) use ($api) {
            $query->from('newsletter_rental_contact')->whereColumn('newsletter_rental_contact.rental_contact_id', 'rental_contacts.id')->where('newsletter_rental_contact.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('rental_contacts.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getMespilRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Subscriber::when($source, function ($query) use ($source) {
                return $query->whereIn('source', $source);
            })->where('subscribers.is_subscribed', 1)->where('subscribers.website', 'mespil')->whereNotNull('subscribers.email')->whereNotExists(function ($query) use ($api) {
            $query->from('newsletter_subscriber')->whereColumn('newsletter_subscriber.subscriber_id', 'subscribers.id')->where('newsletter_subscriber.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('subscribers.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getPhRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Subscriber::when($source, function ($query) use ($source) {
                return $query->whereIn('source', $source);
            })->where('subscribers.is_subscribed', 1)->where('subscribers.website', 'ph')->whereNotNull('subscribers.email')->whereNotExists(function ($query) use ($api) {
            $query->from('newsletter_subscriber')->whereColumn('newsletter_subscriber.subscriber_id', 'subscribers.id')->where('newsletter_subscriber.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('subscribers.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function getPgvRecipients($api, $status = null, $projects = null, $source = null, $goldenvisa = null)
    {
        $recipients = Subscriber::when($source, function ($query) use ($source) {
                return $query->whereIn('source', $source);
            })->where('subscribers.is_subscribed', 1)->where('subscribers.website', 'pgv')->whereNotNull('subscribers.email')->whereNotExists(function ($query) use ($api) {
            $query->from('newsletter_subscriber')->whereColumn('newsletter_subscriber.subscriber_id', 'subscribers.id')->where('newsletter_subscriber.newsletter_id', $api->id);
        });

        if ($api->model->recipients) {
            $recipients = $recipients->whereIn('subscribers.id', $api->model->recipients);
        }

        $recipients = $recipients->get();

        return $recipients;
    }

    public function completeTask(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Complete Task')) {
            abort(403);
        }

        if ($api->model->completed_at) {
            return back()->withErrors(trans('text.taskCompleteError'));
        } else {
            $api->model->completed_at = Carbon::now();
            $api->model->save();

            $status = Status::where('parent', 6)->where('action', 'complete')->value('id');
            if ($status) {
                $ids = $api->model->statuses()->whereNull('task_status.deleted_at')->pluck('task_status.status_id')->all();
                if (!in_array($status, $ids)) {
                    // TaskStatus::where('task_id', $api->model->id)->delete();
                    TaskStatus::where('task_id', $api->model->id)->update([
                        'deleted_at' => Carbon::now(),
                    ]);

                    $api->model->statuses()->attach($status, ['user_id' => Auth::user()->id]);
                }
            }

            return back()->with('reload', true)->with('success', trans('text.taskCompletedSuccess'));
        }
    }

    public function testNewsletter(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Test Newsletter')) {
            abort(403);
        }

        if ($api->model->sent_at) {
            return back()->withErrors(trans('text.newsletterSentError'));
        } else {
            $images = Helper::getTemplateImages($api->model);

            $body = '';

            if ($api->model->template != 'www-portugal-golden-visa-pt') {
                $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->backgroundsMetaId);
                foreach ($api->model->backgroundSections()->where('position', 'header')->get() as $backgroundSection) {
                    $body .= view('newsletter.background', compact('backgroundSection'))->render();

                    $path = $directory . DIRECTORY_SEPARATOR . $backgroundSection->id;
                    if ($backgroundSection->image) {
                        array_push($images, ['filePath' => $path . DIRECTORY_SEPARATOR . $backgroundSection->image->uuid . DIRECTORY_SEPARATOR . $backgroundSection->image->file, 'filename' => $backgroundSection->image->file]);
                    }
                }

                $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->imagesMetaId);
                foreach ($api->model->textSections as $textSection) {
                    $body .= view('newsletter.text', compact('textSection'))->render();

                    $path = $directory . DIRECTORY_SEPARATOR . $textSection->id;
                    foreach ($textSection->images as $image) {
                        array_push($images, ['filePath' => $path . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file, 'filename' => $image->file]);
                    }
                }

                $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->backgroundsMetaId);
                foreach ($api->model->backgroundSections()->where('position', 'footer')->get() as $backgroundSection) {
                    $body .= view('newsletter.background', compact('backgroundSection'))->render();

                    $path = $directory . DIRECTORY_SEPARATOR . $backgroundSection->id;
                    if ($backgroundSection->image) {
                        array_push($images, ['filePath' => $path . DIRECTORY_SEPARATOR . $backgroundSection->image->uuid . DIRECTORY_SEPARATOR . $backgroundSection->image->file, 'filename' => $backgroundSection->image->file]);
                    }
                }

                foreach ($api->model->placeholders as $placeholder) {
                    if (mb_strpos($body, $placeholder) !== false) {
                        $body = preg_replace('/\[\[' . $placeholder . '\]\]/', '<span style="background-color: #ff0;">' . $placeholder . '</span>', $body);
                    }
                }

                $body = Helper::inlineHtml($body);
            }

            $html = view('newsletter.templates.' . ($api->model->template ?: 'default'), compact('api', 'body'))->render();
            $text = html_entity_decode(preg_replace(['/[\r\n]+[\s\t]*[\r\n]+/', '/[ \t]+/'], ["\r\n", ' '], strip_tags(preg_replace('/<br\s*\/?\s*>/Usi', "\r\n", $html)))); /* remove excessive spaces and tabs // strip blank lines (blank, with tabs or spaces)*/

            $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->attachmentsMetaId . DIRECTORY_SEPARATOR . $api->model->id);
            $attachments = [];
            foreach ($api->model->attachments as $attachment) {
                array_push($attachments, ['filePath' => $directory . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file, 'filename' => (ends_with($attachment->name, '.' . $attachment->extension) ? $attachment->name : $attachment->name . '.' . $attachment->extension)]);
            }

            $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');

            $mailgun->messages()->send(trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'domain'), [
                'from' => trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from'),
                'h:Sender' => trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from'),
                'h:Reply-To' => (\Lang::has('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'reply') ? trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'reply') : trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from')),
                'to' => Auth::user()->name . " <" . Auth::user()->email . ">",
                'subject' => $api->model->subject,
                'html' => $html,
                'text' => $text,
                'o:tag' => 'test',
                'v:id' => $api->id,
                'attachment' => $attachments,
                'inline' => $images,
            ]);

            return back()->with('reload', true)->with('success', trans('text.newsletterSentSuccess'));
        }
    }

    public function sendNewsletter(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Send Newsletter')) {
            abort(403);
        }

        if ($api->model->sent_at) {
            return back()->withErrors(trans('text.newsletterSentError'));
        } else {
            if ($api->model->group) {
                $method = 'get' . studly_case($api->model->group) . 'Recipients';
                $recipientGroups[$api->model->group] = $this->{$method}($api, $api->model->status, $api->model->projects, $api->model->source, $api->model->goldenvisa);
            } else {
                foreach ($api->model->groups as $group) {
                    $method = 'get' . studly_case(str_slug($group, '_')) . 'Recipients';
                    $recipientGroups[$group] = $this->{$method}($api, $api->model->status, $api->model->projects, $api->model->source, $api->model->goldenvisa);
                }
            }

            $images = Helper::getTemplateImages($api->model);

            $body = '';

            if ($api->model->template != 'www-portugal-golden-visa-pt') {
                $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->backgroundsMetaId);
                foreach ($api->model->backgroundSections()->where('position', 'header')->get() as $backgroundSection) {
                    $body .= view('newsletter.background', compact('backgroundSection'))->render();

                    $path = $directory . DIRECTORY_SEPARATOR . $backgroundSection->id;
                    if ($backgroundSection->image) {
                        array_push($images, ['filePath' => $path . DIRECTORY_SEPARATOR . $backgroundSection->image->uuid . DIRECTORY_SEPARATOR . $backgroundSection->image->file, 'filename' => $backgroundSection->image->file]);
                    }
                }

                $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->imagesMetaId);
                foreach ($api->model->textSections as $textSection) {
                    $body .= view('newsletter.text', compact('textSection'))->render();

                    $path = $directory . DIRECTORY_SEPARATOR . $textSection->id;
                    foreach ($textSection->images as $image) {
                        array_push($images, ['filePath' => $path . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file, 'filename' => $image->file]);
                    }
                }

                $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->backgroundsMetaId);
                foreach ($api->model->backgroundSections()->where('position', 'footer')->get() as $backgroundSection) {
                    $body .= view('newsletter.background', compact('backgroundSection'))->render();

                    $path = $directory . DIRECTORY_SEPARATOR . $backgroundSection->id;
                    if ($backgroundSection->image) {
                        array_push($images, ['filePath' => $path . DIRECTORY_SEPARATOR . $backgroundSection->image->uuid . DIRECTORY_SEPARATOR . $backgroundSection->image->file, 'filename' => $backgroundSection->image->file]);
                    }
                }

                foreach ($api->model->placeholders as $key => $placeholder) {
                    if (mb_strpos($body, $placeholder) !== false) {
                        $body = preg_replace('/\[\[' . $placeholder . '\]\]/', '%recipient.' . $key . '%', $body);
                    }
                }

                $body = Helper::inlineHtml($body);
            }

            $html = view('newsletter.templates.' . ($api->model->template ?: 'default'), compact('api', 'body'))->render();
            $text = html_entity_decode(preg_replace(['/[\r\n]+[\s\t]*[\r\n]+/', '/[ \t]+/'], ["\r\n", ' '], strip_tags(preg_replace('/<br\s*\/?\s*>/Usi', "\r\n", $html)))); /* remove excessive spaces and tabs // strip blank lines (blank, with tabs or spaces)*/

            $directory = Storage::disk('public')->getDriver()->getAdapter()->applyPathPrefix($api->model->attachmentsMetaId . DIRECTORY_SEPARATOR . $api->model->id);
            $attachments = [];
            foreach ($api->model->attachments as $attachment) {
                array_push($attachments, ['filePath' => $directory . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file, 'filename' => (ends_with($attachment->name, '.' . $attachment->extension) ? $attachment->name : $attachment->name . '.' . $attachment->extension)]);
            }

            foreach ($recipientGroups as $group => $recipients) {
                if ($recipients->count()) {
                    $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');

                    $chunks = $recipients->chunk(1000);
                    foreach ($chunks as $chunk) {
                        $variables = $chunk->map(function ($item, $key) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'first_name' => $item->first_name,
                                'last_name' => $item->last_name,
                                'company' => $item->company,
                                'email' => $item->email,
                            ];
                        })->keyBy('email');

                        $mailgun->messages()->send(trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'domain'), [
                            'from' => trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from'),
                            'h:Sender' => trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from'),
                            'h:Reply-To' => (\Lang::has('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'reply') ? trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'reply') : trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from')),
                            'to' => $variables->map(function ($item, $key) {
                                return str_replace([',', ';'], '', $item['name']) . ' <' . (env('MAIL_TO_ADDRESS') ?: $item['email']) . '>';
                            })->implode(','),
                            'subject' => $api->model->subject,
                            'html' => $html,
                            'text' => $text,
                            'o:tag' => 'newsletter-' . ($api->model->group ?: 'all'),
                            'v:id' => $api->id,
                            'v:recipient' => '%recipient.id%',
                            'recipient-variables' => json_encode($variables),
                            'attachment' => $attachments,
                            'inline' => $images,
                        ]);

                        $website = null;
                        if (in_array($group, ['mespil', 'ph', 'pgv'])) {
                            $website = $group;
                            $group = 'subscriber';
                        }
                        $api->model->{camel_case(str_slug($group, '_'))}($website)->saveMany($chunk);
                    }
                }
            }

            if ($api->model->include_team) {
                $variables = User::all()->map(function ($item, $key) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'email' => $item->email,
                    ];
                })->keyBy('email');

                $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');

                $mailgun->messages()->send(trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'domain'), [
                    'from' => trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from'),
                    'h:Sender' => trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from'),
                    'h:Reply-To' => (\Lang::has('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'reply') ? trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'reply') : trans('newsletters.' . ($api->model->template ? $api->model->template . '.' : '') . 'from')),
                    'to' => $variables->map(function ($item, $key) {
                        return str_replace([',', ';'], '', $item['name']) . ' <' . (env('MAIL_TO_ADDRESS') ?: $item['email']) . '>';
                    })->implode(','),
                    'subject' => $api->model->subject,
                    'html' => $html,
                    'text' => $text,
                    'o:tag' => 'newsletter-users',
                    'v:id' => $api->id,
                    'v:recipient' => '%recipient.id%',
                    'recipient-variables' => json_encode($variables),
                    'attachment' => $attachments,
                    'inline' => $images,
                ]);
            }

            $api->model->sent_at = Carbon::now();
            $api->model->save();

            return back()->with('reload', true)->with('success', trans('text.newsletterSentSuccess'));
        }
    }

    public function reply(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Reply')) {
            abort(403);
        }

        $template = $request->input('template', 'mespil.ie');
        $template = [
            'mespil.ie' => 'www-mespil-ie',
            'portugal-golden-visa.pt' => 'www-portugal-golden-visa-pt',
        ][$template];

        $subject = str_replace('[ID]', $api->model->_parent->id, trans('newsletters.' . $template . '.subject'));
        $message = Helper::linkUrlsInTrustedHtml($request->input('message'));

        foreach ($api->model->_parent->placeholders as $key => $placeholder) {
            if (mb_strpos($message, $placeholder) !== false) {
                $message = preg_replace('/\[\[' . $placeholder . '\]\]/', $api->model->_parent->{$key}, $message);
            }
        }

        $body = Helper::inlineHtml($message, $template);
        $images = Helper::getReplyImages($template);

        if ($api->model->id) {
            $messages = $api->model->_parent->messages()->where('created_at', ($request->has('update') ? '<' : '<='), $api->model->getOriginal('created_at'))->get();
        } else {
            $messages = $api->model->_parent->messages;
        }

        if (!$request->input('history')) {
            $messages = $messages->slice(0, 1);
        }

        $html = view('newsletter.templates.leads.' . $template, compact('api', 'template', 'subject', 'body', 'messages'))->render();
        $text = html_entity_decode(preg_replace(['/[\r\n]+[\s\t]*[\r\n]+/', '/[ \t]+/'], ["\r\n", ' '], strip_tags(preg_replace('/<br\s*\/?\s*>/Usi', "\r\n", $html)))); /* remove excessive spaces and tabs // strip blank lines (blank, with tabs or spaces)*/

        $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');

        $mailgun->messages()->send(trans('newsletters.' . $template . '.domain'), [
            'from' => trans('newsletters.' . $template . '.reply-from'),
            'h:Sender' => trans('newsletters.' . $template . '.reply-from'),
            'h:Reply-To' => (\Lang::has('newsletters.' . $template . '.reply') ? trans('newsletters.' . $template . '.reply') : trans('newsletters.' . $template . '.reply-from')),
            'to' => ($request->has('test') ? (Auth::user()->name . " <" . Auth::user()->email . ">") : (str_replace([',', ';'], '', null) . " <" . $api->model->_parent->email . ">")),
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
            'o:tag' => ($request->has('test') ? 'test-' : '') . 'reply-' . $api->model->_parent->id,
            'v:id' => $api->model->_parent->id,
            'inline' => $images,
        ]);

        if ($request->has('test')) {
            return back()->with('success', trans('text.replySentSuccess'));
        } else {
            if ($request->has('update')) {
                $data = $request->all();

                if ($api->model->created_at == $data['created_at']) {
                    unset($data['created_at']);
                } else {
                    $data['created_at'] = $data['created_at'] ?? Carbon::now();
                }

                $data['user_id'] = $data['from'] ?? null;
                $data['updated_by'] = Auth::user()->id;
                $data['message'] = $message;

                $api->model->update($data);

                $datatables = [
                    'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => [
                        'updated' => $api->model->datatable($api),
                    ],
                ];

                return back()->with('closeModal', true)->with('datatables', $datatables);
            } else {
                $data = $request->all();
                $data['created_at'] = $data['created_at'] ?? Carbon::now();
                $data['user_id'] = $data['from'] ?? null;
                $data['created_by'] = Auth::user()->id;
                $data['model_id'] = $api->model->_parent->id;
                $data['model'] = $api->meta->_parent->model;
                $data['message'] = $message;

                $model = $api->model->create($data);

                $datatables = [
                    'datatable-' . $api->meta->model . ($request->has('overview') ? '-overview' : '') => [
                        'added' => $model->datatable($api),
                    ],
                ];

                return back()->with('closeModal', true)->with('datatables', $datatables);
            }

            return back()->with('reload', true)->with('success', trans('text.replySentSuccess'));
        }
    }

    public function testSms(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Test SMS')) {
            abort(403);
        }

        if ($api->model->sent_at) {
            return back()->withErrors(trans('text.smsSentError'));
        } else {
            $options = [
                CURLOPT_CONNECTTIMEOUT => 5, // The number of seconds to wait while trying to connect.
            ];
            $adapter_client = new CurlClient(new DiactorosMessageFactory(), new DiactorosStreamFactory(), $options);
            $nexmo_client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Basic(env('NEXMO_KEY'), env('NEXMO_SECRET')), [], $adapter_client);

            try {
                $gsm7 = Helper::validateGSM7($api->model->message);

                $nexmo_client->message()->send([
                    'to' => Auth::user()->nexmoPhone,
                    'from' => starts_with(Auth::user()->nexmoPhone, '1') ? '18033067670' : 'MESPIL',
                    'text' => $api->model->message,
                    'type' => $gsm7 ? 'text' : 'unicode',
                ]);

                return back()->with('success', trans('text.smsSentSuccess'));
            } catch (\Nexmo\Client\Exception\Request $e) {
                return back()->withErrors($e->getMessage());
            }
        }
    }

    public function sendSms(Request $request, $path)
    {
        $api = new Api($path);

        if (!Auth::user()->can('Send SMS')) {
            abort(403);
        }

        if ($api->model->sent_at) {
            return back()->withErrors(trans('text.smsSentError'));
        } else {
            if ($api->model->group) {
                $method = 'get' . studly_case($api->model->group) . 'SmsRecipients';
                $recipientGroups[$api->model->group] = $this->{$method}($api, $api->model->status, $api->model->projects);
            } else {
                foreach ($api->model->groups as $group) {
                    $method = 'get' . studly_case(str_slug($group, '_')) . 'SmsRecipients';
                    $recipientGroups[$group] = $this->{$method}($api, $api->model->status, $api->model->projects);
                }
            }

            $options = [
                CURLOPT_CONNECTTIMEOUT => 5, // The number of seconds to wait while trying to connect.
            ];
            $adapter_client = new CurlClient(new DiactorosMessageFactory(), new DiactorosStreamFactory(), $options);
            $nexmo_client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Basic(env('NEXMO_KEY'), env('NEXMO_SECRET')), [], $adapter_client);

            $errors = [];
            foreach ($recipientGroups as $group => $recipients) {
                $ids = [];
                foreach ($recipients as $recipient) {
                    try {
                        $phone = ($group == 'custom' ? $recipient : $recipient->nexmoPhone);
                        $gsm7 = Helper::validateGSM7($api->model->message);

                        $nexmo_client->message()->send([
                            'to' => $phone,
                            'from' => starts_with($phone, '1') ? '18033067670' : 'MESPIL',
                            'text' => $api->model->message,
                            'type' => $gsm7 ? 'text' : 'unicode',
                        ]);

                        array_push($ids, $recipient);
                    } catch (\Nexmo\Client\Exception\Request $e) {
                        array_push($errors, 'ID ' . ($group == 'custom' ? $recipient : $recipient->id) . ': ' . $e->getMessage());
                    }
                }

                if ($group != 'custom') {
                    $api->model->{camel_case(str_slug($group, '_'))}()->saveMany($ids);
                }
            }

            if ($errors) {
                return back()->withErrors($errors);
            } else {
                $api->model->sent_at = Carbon::now();
                $api->model->save();

                return back()->with('reload', true)->with('success', trans('text.smsSentSuccess'));
            }
        }
    }

    public function loadData(Request $request)
    {
        $data = [];
        $method = 'load' . $request->input('method');
        if (method_exists($this, $method)) {
            $data = $this->{$method}($request);
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function loadApartmentsAgentsClients(Request $request)
    {
        $project = Project::findOrFail($request->input('project', session('project')));

        $data = [];

        $data['apartments'] = $this->loadApartments($request, $project);
        $data['agents'] = $this->loadAgents($request, $project)->forget(0)->prepend(['id' => '', 'agent' => trans('multiselect.noneSelectedSingle')]);
        $data['clients'] = $this->loadClients($request, $project)->prepend(['id' => '', 'client' => trans('multiselect.noneSelectedSingle')]);

        if ($request->input('subagents')) {
            $data['subagents'] = $data['agents']->where('type', 'direct')->prepend(['id' => '', 'agent' => trans('multiselect.noneSelectedSingle')]);
        }

        return $data;
    }

    public function loadBlocksFloorsBedsViewsFurniture(Request $request)
    {
        $project = Project::findOrFail($request->input('project', session('project')));

        $data = [];

        $data['blocks'] = $this->loadBlocks($request, $project)->prepend(['id' => '', 'name' => trans('multiselect.noneSelectedSingle')]);
        $data['floors'] = $this->loadFloors($request, $project)->prepend(['id' => '', 'name' => trans('multiselect.noneSelectedSingle')]);
        $data['beds'] = $this->loadBeds($request, $project)->prepend(['id' => '', 'name' => trans('multiselect.noneSelectedSingle')]);
        $data['views'] = $this->loadViews($request, $project)->prepend(['id' => '', 'name' => trans('multiselect.noneSelectedSingle')]);
        $data['furniture'] = $this->loadFurniture($request, $project)->prepend(['id' => '', 'name' => trans('multiselect.noneSelectedSingle')]);

        return $data;
    }

    public function loadApartmentsGuests(Request $request)
    {
        $project = Project::findOrFail($request->input('project', session('project')));

        $data = [];

        $data['apartments'] = $this->loadApartments($request, $project);
        $data['guests'] = $this->loadGuests($request, $project)->prepend(['id' => '', 'guest' => trans('multiselect.noneSelectedSingle')]);

        return $data;
    }

    public function loadGuests(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $guests = Guest::selectRaw('id, TRIM(CONCAT(first_name, " ", COALESCE(last_name, ""))) AS guest')->where('project_id', $project->id)->get();

        return $guests;
    }

    public function loadBlocks(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $blocks = Block::where('project_id', $project->id)->get();

        return $blocks;
    }

    public function loadFloors(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $floors = Floor::where('project_id', $project->id)->get();

        return $floors;
    }

    public function loadBeds(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $beds = Bed::where('project_id', $project->id)->get();

        return $beds;
    }

    public function loadViews(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $views = ModelView::where('project_id', $project->id)->get();

        return $views;
    }

    public function loadFurniture(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $furniture = Furniture::where('project_id', $project->id)->get();

        return $furniture;
    }

    public function loadApartments(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $apartments = Apartment::selectRaw('apartments.id, apartments.price, CONCAT(apartments.unit, IF(ISNULL(blocks.name), "", " / "), COALESCE(blocks.name, "")) as apartment, COALESCE(furniture.price, "0") as furniture')->leftJoin('blocks', 'blocks.id', '=', 'apartments.block_id')->leftJoin('furniture', 'furniture.id', '=', 'apartments.furniture_id')->where('apartments.project_id', $project->id);

        if ($request->has('exclude')) {
            if ($request->input('exclude') == 'sales') {
                $apartments = $apartments->whereNotExists(function ($query) {
                    $query->from('sales')->whereColumn('apartments.id', '=', 'sales.apartment_id')->whereNull('sales.deleted_at');
                });
            } elseif ($request->input('exclude') == 'sold') {
                $apartments = $apartments->leftJoin('apartment_status', 'apartment_status.apartment_id', '=', 'apartments.id')->leftJoin('statuses', 'statuses.id', '=', 'apartment_status.status_id')->whereNull('apartment_status.deleted_at')->whereNull('statuses.deleted_at')->where(function ($query) {
                    $query->where('statuses.action', '!=', 'final-balance')->orWhereNull('statuses.action');
                })->groupBy('apartments.id');
            }
        }

        $apartments = $apartments->get();

        return $apartments;
    }

    public function loadAgents(Request $request, $project = null)
    {
        $agents = Agent::selectRaw('agents.id, agents.company AS agent, agents.type');

        if (!$request->has('agents') || $request->input('agents') != 'all') {
            if (!$project) {
                $project = Project::findOrFail($request->input('project', session('project')));
            }

            $agents = $agents->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->where('agent_project.project_id', $project->id);
        }

        $agents = $agents->orderBy('agent')->get()->prepend(['id' => 0, 'agent' => trans('text.directClient')]);

        return $agents;
    }

    public function loadClients(Request $request, $project = null)
    {
        if (!$project) {
            $project = Project::findOrFail($request->input('project', session('project')));
        }

        $clients = Client::selectRaw('clients.*, TRIM(CONCAT(clients.first_name, " ", COALESCE(clients.last_name, ""))) AS client')->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->where('client_project.project_id', $project->id)->when($request->input('id'), function ($query) use ($request) {
            return $query->where('clients.id', $request->input('id'));
        });

        if ($request->input('id')) {
            $clients = $clients->first();
        } else {
            $clients = $clients->orderBy('clients.first_name')->orderBy('clients.last_name')->get();
        }

        return $clients;
    }

    public function loadTargets(Request $request)
    {
        $years = [];
        // $project = Project::findOrFail($request->input('project'));
        $targets = Target::select('id', 'name as target');

        if ($request->input('year')) {
            $year = Target::whereNull('parent')->whereIn('project_id', Helper::project())->where('name', $request->input('year'))->value('id');
            $targets = $targets->where('parent', $year);
        } else {
            $years = Target::select('id', 'name as year')->whereNull('parent')->whereIn('project_id', Helper::project())->orderBy('name', 'desc')->get();
            if (count($years) > 0) {
                $targets = $targets->where('parent', $years->first()->id);
            }
        }

        $targets = $targets->whereIn('project_id', Helper::project())->orderBy('name')->get();

        return [
            'years' => $years,
            'targets' => $targets,
        ];
    }

    public function loadCommission(Request $request)
    {
        $commission = 0;
        /*$client = Client::findOrFail($request->input('client'));
        if ($client->agent_id) {
            $agent = Agent::findOrFail($client->agent_id);
            $pivot = $agent->projects()->where('projects.id', $request->input('project', session('project')))->first();
            if ($pivot) {
                $commission = $pivot->pivot->commission;
            }
        } else {
            // direct clients => Erwin gets his standard commission
        }*/

        if ($request->input('agent')) {
            $agent = Agent::findOrFail($request->input('agent'));
            $pivot = $agent->projects()->where('projects.id', $request->input('project', session('project')))->first();
            if ($pivot) {
                $commission = $pivot->pivot->commission;
            }
        }

        return $commission;
    }

    public function loadSubCommission(Request $request)
    {
        $commission = 0;
        $subagent = Agent::findOrFail($request->input('subagent'));
        $pivot = $subagent->projects()->where('projects.id', $request->input('project', session('project')))->first();
        if ($pivot) {
            $commission = $pivot->pivot->sub_commission;
        }

        return $commission;
    }

    public function loadRecipients(Request $request)
    {
        $data = [];
        if ($request->input('group') == 'agent-contacts') {
            $data['recipients'] = Agent::select('agents.id', 'agents.company AS recipient')->leftJoin('agent_contacts', 'agent_contacts.agent_id', '=', 'agents.id')->where('agent_contacts.newsletters', 1)->whereNotNull('agent_contacts.email')->when($request->input('projects'), function ($query) use ($request) {
                return $query->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereNull('agent_project.deleted_at')->whereIn('agent_project.project_id', explode(',', $request->input('projects')));
            })->when($request->has('goldenvisa'), function ($query) use ($request) {
                return $query->where('goldenvisa', $request->input('goldenvisa'));
            })->groupBy('agents.id')->orderBy('recipient')->get();
        } elseif ($request->input('group') == 'clients') {
            $data['recipients'] = Client::selectRaw('clients.id, TRIM(CONCAT(clients.first_name, " ", COALESCE(clients.last_name, ""))) AS recipient')->where('clients.newsletters', 1)->whereNotNull('clients.email')->when($request->input('status'), function ($query) use ($request) {
                return $query->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')->whereNull('client_status.deleted_at')->whereIn('client_status.status_id', explode(',', $request->input('status')));
            })->when($request->input('projects'), function ($query) use ($request) {
                return $query->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->whereNull('client_project.deleted_at')->whereIn('client_project.project_id', explode(',', $request->input('projects')));
            })->orderBy('clients.first_name')->get();

            if ($request->has('include-status')) {
                $data['status'] = Status::select('id', 'name AS status')->where('parent', 2)->orderBy('name')->get();
            }
        } elseif ($request->input('group') == 'investors') {
            $data['recipients'] = Investor::selectRaw('investors.id, TRIM(CONCAT(investors.first_name, " ", COALESCE(investors.last_name, ""))) AS recipient')->where('investors.newsletters', 1)->when($request->input('projects'), function ($query) use ($request) {
                return $query->leftJoin('investor_project', 'investor_project.investor_id', '=', 'investors.id')->whereNull('investor_project.deleted_at')->whereIn('investor_project.project_id', explode(',', $request->input('projects')));
            })->orderBy('recipient')->get();
        } elseif ($request->input('group') == 'guests') {
            $data['recipients'] = Guest::selectRaw('id, TRIM(CONCAT(first_name, " ", COALESCE(last_name, ""))) AS recipient')->where('newsletters', 1)->when($request->input('projects'), function ($query) use ($request) {
                return $query->whereIn('project_id', explode(',', $request->input('projects')));
            })->orderBy('recipient')->get();
        } elseif ($request->input('group') == 'gvcontacts') {
            $data['recipients'] = Gvcontact::select('id', 'email AS recipient')->when($request->input('source'), function ($query) use ($request) {
                return $query->whereIn('type', explode(',', $request->input('source')));
            })->where('is_subscribed', 1)->orderBy('recipient')->get();

            if ($request->has('include-source')) {
                $data['source'] = [
                    [
                        'id' => 'agent',
                        'source' => trans('text.gvagent'),
                    ],
                    [
                        'id' => 'client',
                        'source' => trans('text.gvclient'),
                    ],
                ];
            }
        } elseif ($request->input('group') == 'rental-contacts') {
            $data['recipients'] = RentalContact::select('id', 'email AS recipient')->where('is_subscribed', 1)->orderBy('recipient')->get();
        } elseif ($request->input('group') == 'mespil') {
            $data['recipients'] = Subscriber::select('id', 'email AS recipient')->when($request->input('source'), function ($query) use ($request) {
                return $query->whereIn('source', explode(',', $request->input('source')));
            })->where('is_subscribed', 1)->where('website', 'mespil')->orderBy('recipient')->get();

            if ($request->has('include-source')) {
                $data['source'] = [
                    [
                        'id' => 'subscribe-newsletter',
                        'source' => trans('text.subscribeNewsletter'),
                    ],
                    [
                        'id' => 'join-investor-club',
                        'source' => trans('text.joinInvestorClub'),
                    ],
                    [
                        'id' => 'download-investor-brochure',
                        'source' => trans('text.downloadInvestorBrochure'),
                    ],
                ];
            }
        } elseif ($request->input('group') == 'ph') {
            $data['recipients'] = Subscriber::select('id', 'email AS recipient')->when($request->input('source'), function ($query) use ($request) {
                return $query->whereIn('source', explode(',', $request->input('source')));
            })->where('is_subscribed', 1)->where('website', 'ph')->orderBy('recipient')->get();

            if ($request->has('include-source')) {
                $data['source'] = [
                    [
                        'id' => 'subscribe-newsletter',
                        'source' => trans('text.subscribeNewsletter'),
                    ],
                ];
            }
        } elseif ($request->input('group') == 'pgv') {
            $data['recipients'] = Subscriber::select('id', 'email AS recipient')->when($request->input('source'), function ($query) use ($request) {
                return $query->whereIn('source', explode(',', $request->input('source')));
            })->where('is_subscribed', 1)->where('website', 'pgv')->orderBy('recipient')->get();

            if ($request->has('include-source')) {
                $data['source'] = [
                    [
                        'id' => 'subscribe-newsletter',
                        'source' => trans('text.subscribeNewsletter'),
                    ],
                ];
            }
        }

        if ($request->has('include-projects') && in_array($request->input('group'), ['agent-contacts', 'clients', 'investors', 'guests'])) {
            $data['projects'] = Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->orderBy('projects')->get();
        }

        return $data;
    }

    public function loadSmsRecipients(Request $request)
    {
        $data = [];
        if ($request->input('group') == 'clients') {
            $data['recipients'] = Client::selectRaw('clients.id, TRIM(CONCAT(clients.first_name, " ", COALESCE(clients.last_name, ""))) AS recipient')->where('clients.sms', 1)->whereNotNull('clients.phone_number')->when($request->input('status'), function ($query) use ($request) {
                return $query->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')->whereNull('client_status.deleted_at')->whereIn('client_status.status_id', explode(',', $request->input('status')));
            })->when($request->input('projects'), function ($query) use ($request) {
                return $query->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->whereNull('client_project.deleted_at')->whereIn('client_project.project_id', explode(',', $request->input('projects')));
            })->orderBy('clients.first_name')->get();

            if ($request->has('include-status')) {
                $data['status'] = Status::select('id', 'name AS status')->where('parent', 2)->orderBy('name')->get();
            }

            if ($request->has('include-projects')) {
                $data['projects'] = Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->orderBy('projects')->get();
            }
        }

        return $data;
    }

    public function changeProject($project)
    {
        Helper::projects($project, true);

        return response()->json(null, 200)->header('X-Location', url()->previous());
    }

    public function changeReportView($view = null)
    {
        return response()->json(null, 200)->header('X-Location', secure_url('reports' . ($view ? '/dashboard/' . $view : '')));
    }
}
