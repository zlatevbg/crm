@inject('carbon', '\Carbon\Carbon')

@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        @if (session('project'))
            <input id="input-project_id" name="project_id" value="{{ session('project') }}" type="hidden">
        @else
            <div class="form-group">
                <label for="input-project_id">@lang('labels.project')</label>
                <select required id="input-project_id" class="form-control" name="project_id">
                    <option selected="selected">@lang('placeholders.project')</option>
                    @foreach ($api->model->selectProjects($api->model->_parent && $api->meta->_parent->model == 'apartment' ? $api->model->_parent : null) as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($api->model->_parent)
            @if ($api->meta->_parent->model == 'client')
                <input id="input-client_id" name="client_id" value="{{ $api->model->_parent->id }}" type="hidden">
            @elseif ($api->meta->_parent->model == 'apartment')
                <input id="input-apartments" name="apartments[]" value="{{ $api->model->_parent->id }}" type="hidden">
            @endif
        @endif

        <div class="form-group">
            <label for="input-viewed_at">@lang('labels.viewedAt')</label>
            <input id="input-viewed_at" class="form-control" placeholder="@lang('placeholders.viewedAt')" name="viewed_at" type="text" value="{{ date('d.m.Y') }}">
        </div>

        @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'apartment'))
            <div class="form-group">
                <label for="input-client_id">@lang('labels.client')</label>
                <select autofocus required {{ !session('project') ? 'disabled' : '' }} id="input-client_id" class="form-control" name="client_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @if (session('project'))
                        @foreach ($api->model->selectClients() as $key => $value)
                            <option value="{{ $value->id }}" data-agent="{{ $value->agent_id }}">{{ $value->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        @endif

        @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'client'))
            <div class="form-group">
                <label for="input-apartments">@lang('labels.apartments')</label>
                <select multiple required {{ !session('project') ? 'disabled' : '' }} id="input-apartments" class="form-control" name="apartments[]">
                    @if (session('project'))
                        @foreach ($api->model->selectApartments() as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-agent_id">@lang('labels.agent')</label>
            <select {{ !session('project') ? 'disabled' : '' }} id="input-agent_id" class="form-control" name="agent_id">
                <option value="">@lang('multiselect.noneSelectedSingle')</option>
                @if (session('project'))
                    @foreach ($api->model->selectAgents() as $key => $value)
                        <option value="{{ $key }}" @if ($api->meta->_parent && $api->meta->_parent->model == 'client' && $api->model->_parent->agent_id == $key) selected="selected" @endif>{{ $value }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="input-status">@lang('labels.status')</label>
            <select multiple id="input-status" class="form-control" name="status[]">
                @foreach ($api->model->selectStatus() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.comments')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.comments')" name="description"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    var status = document.querySelector('#input-status');
    var apartments = document.querySelector('#input-apartments');
    var client_id = document.querySelector('#input-client_id');
    var agent_id = document.querySelector('#input-agent_id');

    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-viewed_at').datepicker({
        {{-- maxDate: 0, --}}
        onSelect: function(date) {
            var parts = date.split('.');
            var date = new Date(Date.UTC(parts[2], (parts[1] - 1), parts[0]));
            var date = date.getFullYear() + '' + (date.getMonth() + 1) + '' + date.getDate();
            if (date > '{{ $carbon->format('Ymd') }}') {
                status.value = '{{ $api->model->futureStatus() }}';
                $(status).multiselect('refresh');
            }
        },
    });

    @if (!session('project'))
        document.querySelector('#input-project_id').addEventListener('change', function() {
            if (parseInt(this.value) > 0) {
                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=ApartmentsAgentsClients&exclude=sales&agents=all&project=' + this.value,
                    skipErrors: true,
                }).then(function (data) {
                    @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'client'))
                        while (apartments.firstChild) {
                            apartments.removeChild(apartments.firstChild);
                        }

                        $.each(data.data.apartments, function(key, value) {
                            $(apartments).append($('<option></option>').attr('value', value.id).text(value.apartment));
                        });

                        $(apartments).multiselect('refresh');
                    @endif

                    @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'apartment'))
                        while (client_id.firstChild) {
                            client_id.removeChild(client_id.firstChild);
                        }

                        $.each(data.data.clients, function(key, value) {
                            $(client_id).append($('<option></option>').attr('value', value.id).text(value.client));
                        });

                        $(client_id).multiselect('refresh');
                    @endif

                    while (agent_id.firstChild) {
                        agent_id.removeChild(agent_id.firstChild);
                    }

                    $.each(data.data.agents, function(key, value) {
                        $(agent_id).append($('<option></option>').attr('value', value.id).text(value.agent));
                    });

                    $(agent_id).multiselect('refresh');
                }).catch(function (error) {
                });

                @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'client'))
                    apartments.disabled = false;
                    $(apartments).multiselect('enable');
                @endif
                @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'apartment'))
                    client_id.disabled = false;
                    $(client_id).multiselect('enable');
                @endif
                agent_id.disabled = false;
                $(agent_id).multiselect('enable');
            } else {
                @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'client'))
                    apartments.disabled = true;
                    $(apartments).multiselect('disable');
                @endif
                @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'apartment'))
                    client_id.disabled = true;
                    $(client_id).multiselect('disable');
                @endif
                agent_id.disabled = true;
                $(agent_id).multiselect('disable');
            }
        });
    @endif

    @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'client'))
        $(apartments).multiselect();
    @endif

    @if (!$api->model->_parent || ($api->meta->_parent && $api->meta->_parent->model == 'apartment'))
        $(client_id).multiselect({
            multiple: false,
            close: function() {
                if (parseInt(this.value) > 0) {
                    agent_id.value = this.options[this.selectedIndex].getAttribute('data-agent');
                } else {
                    agent_id.value = 0;
                }
                $(agent_id).multiselect('refresh');
            },
        });
    @endif

    $(agent_id).multiselect({
        multiple: false,
    });

    $(status).multiselect();
@endsection
