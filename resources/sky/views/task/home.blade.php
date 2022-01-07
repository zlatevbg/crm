@extends('layouts.main')

@section('home')
<section class="mt-3 overview-grid-container">
    <div>
        <article class="card card-border">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.taskDetails')</h4>
                @include('partials/buttons')
            </header>
            <div class="card-body">
                <table class="table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.name'):</th>
                            <td>{{ $api->model->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.status'):</th>
                            <td>{{ $api->model->statuses()->whereNull('task_status.deleted_at')->orderBy('task_status.created_at', 'desc')->first()->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.assignedTo'):</th>
                            <td>{{ optional($api->model->users)->implode('name', ', ') }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.department'):</th>
                            <td>{{ optional($api->model->department)->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.deadline'):</th>
                            <td>{{ $api->model->end_at }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.completed'):</th>
                            <td>{{ $api->model->completed_at }}</td>
                        </tr>
                        <tr class="priorities">
                            <th>@lang('labels.priority'):</th>
                            <td class="task-priority priority{{ optional($api->model->priority)->order }}">{{ optional($api->model->priority)->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.project'):</th>
                            <td>{{ optional($api->model->project)->name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @can('View System Details')
                <div class="card-body">
                    <h4 class="card-title mb-0 font-weight-bold">@lang('labels.systemDetails')</h4>
                    <table class="mt-3 table table-sm table-card">
                        <tbody>
                            <tr>
                                <th>@lang('labels.id'):</th>
                                <td>{{ $api->model->id }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.user'):</th>
                                <td>{{ $api->model->user->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.createdAt'):</th>
                                <td>{{ $api->model->created_at }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.updatedAt'):</th>
                                <td>{{ $api->model->updated_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endcan
        </article>
    </div>
    <div>
        @if ($api->model->description || $attachments->count())
            <article class="card card-border mb-3">
                <div class="card-body">
                    {!! $api->model->description !!}
                    @if ($attachments->count())
                        <ul class="mb-0">
                            @foreach ($attachments as $attachment)
                                <li>{!! $attachment->name . ' [' . $attachment->size . ']' !!}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </article>
        @endif

        @foreach ($api->tabsOverview as $tab)
            <article class="card mb-3">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 font-weight-bold text-uppercase">{{ $tab['name'] }}</h4>
                    @include('partials/buttons', ['buttons' => current($tab['datatables-overview'])['buttons'], 'key' => key($tab['datatables-overview'])])
                </header>
                @include('partials/datatables', ['datatables' => $tab['datatables-overview']])
            </article>
        @endforeach
    </div>
</section>
@endsection
