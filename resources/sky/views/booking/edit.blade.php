@inject('carbon', '\Carbon\Carbon')

@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax novalidate>
        @csrf
        @method('PATCH')

        @if (session('project'))
            <input id="input-project_id" name="project_id" value="{{ session('project') }}" type="hidden">
        @else
            <div class="form-group">
                <label for="input-project_id">@lang('labels.project')</label>
                <select required id="input-project_id" class="form-control" name="project_id">
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->project_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($api->model->_parent)
            <input id="input-guest_id" name="guest_id" value="{{ $api->model->_parent->id }}" type="hidden">
        @endif

        <div class="form-group">
            <label for="input-arrive_at">@lang('labels.arriveAt')</label>
            <input id="input-arrive_at" class="form-control" placeholder="@lang('placeholders.arriveAt')" name="arrive_at" type="text" value="{{ $api->model->arrive_at }}">
        </div>

        <div class="form-group">
            <label for="input-depart_at">@lang('labels.departAt')</label>
            <input id="input-depart_at" class="form-control" placeholder="@lang('placeholders.departAt')" name="depart_at" type="text" value="{{ $api->model->depart_at }}">
        </div>

        @if (!$api->model->_parent)
            <div class="form-group">
                <label for="input-guest_id">@lang('labels.guest')</label>
                <select autofocus required id="input-guest_id" class="form-control" name="guest_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectGuests($api) as $key => $value)
                        <option value="{{ $value->id }}" {!! $api->model->guest_id == $value->id ? 'selected="selected"' : '' !!}>{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-apartments">@lang('labels.apartments')</label>
            <select multiple required id="input-apartments" class="form-control" name="apartments[]">
                @php $selectedApartments = $api->model->apartments->pluck('id')->all(); @endphp
                @foreach ($api->model->selectApartments($api) as $key => $value)
                    <option value="{{ $key }}" {!! in_array($key, $selectedApartments) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.comments')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.comments')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
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
        {!! $api->model->depart_at ? 'minDate: new Date("' . $carbon->parse($api->model->depart_at) . '"),' : '' !!}
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
