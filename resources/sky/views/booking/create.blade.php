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
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($api->model->_parent)
            <input id="input-guest_id" name="guest_id" value="{{ $api->model->_parent->id }}" type="hidden">
        @endif

        <div class="form-group">
            <label for="input-arrive_at">@lang('labels.arriveAt')</label>
            <input id="input-arrive_at" class="form-control" placeholder="@lang('placeholders.arriveAt')" name="arrive_at" type="text" value="{{ date('d.m.Y') }}">
        </div>

        <div class="form-group">
            <label for="input-depart_at">@lang('labels.departAt')</label>
            <input id="input-depart_at" class="form-control" placeholder="@lang('placeholders.departAt')" name="depart_at" type="text">
        </div>

        @if (!$api->model->_parent)
            <div class="form-group">
                <label for="input-guest_id">@lang('labels.guest')</label>
                <select autofocus required {{ !session('project') ? 'disabled' : '' }} id="input-guest_id" class="form-control" name="guest_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @if (session('project'))
                        @foreach ($api->model->selectGuests() as $key => $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-apartment">@lang('labels.apartments')</label>
            <select multiple required {{ !session('project') ? 'disabled' : '' }} id="input-apartments" class="form-control" name="apartments[]">
                @if (session('project'))
                    @foreach ($api->model->selectApartments() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                @endif
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
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-arrive_at').datepicker({
        changeYear: true,
        changeMonth: true,
        onSelect: function(date) {
            var d = Date.parse($("#input-arrive_at").datepicker("getDate"));
            $('#input-depart_at').datepicker('option', 'minDate', new Date(d));
        }
    });

    $('#input-depart_at').datepicker({
        changeYear: true,
        changeMonth: true,
        minDate: 0,
    });

    var apartments = document.querySelector('#input-apartments');
    var guest_id = document.querySelector('#input-guest_id');

    @if (!session('project'))
        document.querySelector('#input-project_id').addEventListener('change', function() {
            if (parseInt(this.value) > 0) {
                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=ApartmentsGuests&project=' + this.value,
                    skipErrors: true,
                }).then(function (data) {
                    while (guest_id.firstChild) {
                        guest_id.removeChild(guest_id.firstChild);
                    }

                    $.each(data.data.guests, function(key, value) {
                        $(guest_id).append($('<option></option>').attr('value', value.id).text(value.guest));
                    });

                    $(guest_id).multiselect('refresh');

                    while (apartments.firstChild) {
                        apartments.removeChild(apartments.firstChild);
                    }

                    $.each(data.data.apartments, function(key, value) {
                        $(apartments).append($('<option></option>').attr('value', value.id).text(value.apartment));
                    });

                    $(apartments).multiselect('refresh');
                }).catch(function (error) {
                });

                guest_id.disabled = false;
                $(guest_id).multiselect('enable');
                apartments.disabled = false;
                $(apartments).multiselect('enable');
            } else {
                guest_id.disabled = true;
                $(guest_id).multiselect('disable');
                apartments.disabled = true;
                $(apartments).multiselect('disable');
            }
        });
    @endif

    @if (!$api->model->_parent)
        $('#input-guest_id').multiselect({
            multiple: false,
        });
    @endif

    $('#input-apartments').multiselect();
@endsection
