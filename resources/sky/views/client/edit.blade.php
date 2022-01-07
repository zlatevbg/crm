@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax novalidate>
        @csrf
        @method('PATCH')

        @if (!session('project'))
            <div class="form-group">
                <label for="input-projects">@lang('labels.projects')</label>
                <select multiple required id="input-projects" class="form-control" name="projects[]">
                    @php $selectedProjects = $api->model->projects->pluck('id')->all(); @endphp
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedProjects) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-source_id">@lang('labels.source')</label>
            <select id="input-source_id" class="form-control" name="source_id">
                <option value="">@lang('placeholders.source')</option>
                @foreach ($api->model->selectSources() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->source_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group {{ $api->model->agent_id ? '' : 'table-hidden' }}">
            <label for="input-agent_id">@lang('labels.agent')</label>
            <select id="input-agent_id" class="form-control" name="agent_id" {{ $api->model->agent_id ? '' : 'disabled' }}>
                <option value="">@lang('placeholders.agent')</option>
                @foreach ($api->model->selectAgent() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->agent_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-first_name">@lang('labels.firstName')</label>
            <input id="input-first_name" class="form-control" autofocus required placeholder="@lang('placeholders.firstName')" name="first_name" type="text" value="{{ $api->model->first_name }}">
        </div>

        <div class="form-group">
            <label for="input-last_name">@lang('labels.lastName')</label>
            <input id="input-last_name" class="form-control" placeholder="@lang('placeholders.lastName')" name="last_name" type="text" value="{{ $api->model->last_name }}">
        </div>

        <div class="form-group">
            <label for="input-gender">@lang('labels.gender')</label>
            <select required id="input-gender" class="form-control" name="gender">
                @foreach ($api->model->selectGender() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->gender == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" placeholder="@lang('placeholders.email')" name="email" type="email" value="{{ $api->model->email }}">
        </div>

        <div class="form-group">
            <label for="input-phone_number">@lang('labels.phone')</label>
            <div class="input-group">
                <select id="input-phone_code" class="form-control" name="phone_code">
                    <option value="">@lang('placeholders.phoneCode')</option>
                    @foreach ($api->model->selectPhoneCodes() as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->phone_code == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
                <input id="input-phone_number" class="form-control" {{ $api->model->phone_code ? '' : 'disabled' }} placeholder="@lang('placeholders.phoneNumber')" name="phone_number" type="tel" value="{{ $api->model->phone_number }}">
            </div>
        </div>

        <div class="form-group">
            <label for="input-country_id">@lang('labels.country')</label>
            <select id="input-country_id" class="form-control" name="country_id">
                <option value="">@lang('placeholders.country')</option>
                @foreach ($api->model->selectCountries() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->country_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-city">@lang('labels.city')</label>
            <input id="input-city" class="form-control" placeholder="@lang('placeholders.city')" name="city" type="text" value="{{ $api->model->city }}">
        </div>

        <div class="form-group">
            <label for="input-postcode">@lang('labels.postcode')</label>
            <input id="input-postcode" class="form-control" placeholder="@lang('placeholders.postcode')" name="postcode" type="text" value="{{ $api->model->postcode }}">
        </div>

        <div class="form-group">
            <label for="input-address1">@lang('labels.address1')</label>
            <input id="input-address1" class="form-control" placeholder="@lang('placeholders.address1')" name="address1" type="text" value="{{ $api->model->address1 }}">
        </div>

        <div class="form-group">
            <label for="input-address2">@lang('labels.address2')</label>
            <input id="input-address2" class="form-control" placeholder="@lang('placeholders.address2')" name="address2" type="text" value="{{ $api->model->address2 }}">
        </div>

        <div class="form-group">
            <label for="input-passport">@lang('labels.passport')</label>
            <input id="input-passport" class="form-control" placeholder="@lang('placeholders.passport')" name="passport" type="text" value="{{ $api->model->passport }}">
        </div>

        <div class="form-group">
            <label for="input-newsletters">@lang('labels.newslettersSubscription')</label>
            <select id="input-newsletters" class="form-control" name="newsletters">
                @foreach ($api->model->selectNewslettersSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->newsletters == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-sms">@lang('labels.smsSubscription')</label>
            <select id="input-sms" class="form-control" name="sms">
                @foreach ($api->model->selectSmsSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->sms == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-phone')
    var source_id = document.querySelector('#input-source_id');
    var agent_id = document.querySelector('#input-agent_id');
    var first_name = document.querySelector('#input-first_name');
    var last_name = document.querySelector('#input-last_name');
    var gender = document.querySelector('#input-gender');
    var email = document.querySelector('#input-email');
    var phone_code = document.querySelector('#input-phone_code');
    var phone_number = document.querySelector('#input-phone_number');
    var country_id = document.querySelector('#input-country_id');
    var city = document.querySelector('#input-city');
    var postcode = document.querySelector('#input-postcode');
    var address1 = document.querySelector('#input-address1');
    var address2 = document.querySelector('#input-address2');
    var passport = document.querySelector('#input-passport');
    var newsletters = document.querySelector('#input-newsletters');
    var sms = document.querySelector('#input-sms');

    @if (session('project'))
        $(document.querySelector('#input-client_id')).multiselect({
            multiple: false,
            close: function() {
                if (parseInt(this.value) > 0) {
                    ajax.ajaxify({
                        obj: this,
                        method: 'get',
                        queue: 'sync',
                        action: '{{ Helper::route('api.load-data') }}',
                        data: 'method=Clients&id=' + this.value,
                        skipErrors: true,
                    }).then(function (data) {
                        var client = data.data;

                        source_id.value = client.source_id;
                        $(source_id).multiselect('refresh');
                        agent_id.value = client.agent_id;
                        $(agent_id).multiselect('refresh');
                        first_name.value = client.first_name;
                        last_name.value = client.last_name;
                        gender.value = client.gender;
                        email.value = client.email;
                        phone_code.value = client.phone_code;
                        $(phone_code).multiselect('refresh');
                        phone_number.value = client.phone_number;
                        if (client.phone_number) {
                            phone_number.disabled = false;
                        }
                        country_id.value = client.country_id;
                        $(country_id).multiselect('refresh');
                        city.value = client.city;
                        postcode.value = client.postcode;
                        address1.value = client.address1;
                        address2.value = client.address2;
                        passport.value = client.passport;
                        newsletters.value = client.newsletters;
                        sms.value = client.sms;
                    }).catch(function (error) {
                    });
                } else {
                    source_id.value = '';
                    $(source_id).multiselect('refresh');
                    agent_id.value = '';
                    $(agent_id).multiselect('refresh');
                    first_name.value = '';
                    last_name.value = '';
                    gender.value = '';
                    email.value = '';
                    phone_code.value = '';
                    $(phone_code).multiselect('refresh');
                    phone_number.value = '';
                    country_id.value = '';
                    $(country_id).multiselect('refresh');
                    city.value = '';
                    postcode.value = '';
                    address1.value = '';
                    address2.value = '';
                    passport.value = '';
                    newsletters.value = '';
                    sms.value = '';
                }
            },
        });
    @else
        $('#input-projects').multiselect();
    @endif

    $(source_id).multiselect({
        multiple: false,
        close: function() {
            if (parseInt(this.value) == 11) {
                agent_id.closest('.form-group').classList.remove('table-hidden');
                agent_id.disabled = false;
                $(agent_id).multiselect('enable');
            } else {
                agent_id.closest('.form-group').classList.add('table-hidden');
                agent_id.value = '';
                agent_id.disabled = true;
                $(agent_id).multiselect('disable');
                $(agent_id).multiselect('refresh');
            }
        },
    });

    $(agent_id).multiselect({
        multiple: false,
    });

    $(country_id).multiselect({
        multiple: false,
    });
@endsection
