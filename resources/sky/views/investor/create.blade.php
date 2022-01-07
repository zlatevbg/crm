@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        <div class="form-group">
            <label for="input-projects">@lang('labels.projects')</label>
            <select multiple required id="input-projects" class="form-control" name="projects[]">
                @foreach ($api->model->selectProjects() as $key => $value)
                    <option value="{{ $key }} {!! $key == session('project') ? 'selected="selected"' : '' !!}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-source_id">@lang('labels.source')</label>
            <select id="input-source_id" class="form-control" name="source_id">
                <option value="" selected="selected">@lang('placeholders.source')</option>
                @foreach ($api->model->selectSources() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-fund_size_id">@lang('labels.fundSize')</label>
            <select id="input-fund_size_id" class="form-control" name="fund_size_id">
                <option value="" selected="selected">@lang('placeholders.fundSize')</option>
                @foreach ($api->model->selectFundSize() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-investment_range_id">@lang('labels.investmentRange')</label>
            <select id="input-investment_range_id" class="form-control" name="investment_range_id">
                <option value="" selected="selected">@lang('placeholders.investmentRange')</option>
                @foreach ($api->model->selectInvestmentRange() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-start_at">@lang('labels.startAt')</label>
            <input id="input-start_at" class="form-control" placeholder="@lang('placeholders.startAt')" name="start_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-end_at">@lang('labels.endAt')</label>
            <input id="input-end_at" class="form-control" placeholder="@lang('placeholders.endAt')" name="end_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-category_id">@lang('labels.investorCategory')</label>
            <select id="input-category_id" class="form-control" name="category_id">
                <option value="" selected="selected">@lang('placeholders.investorCategory')</option>
                @foreach ($api->model->selectInvestorCategory() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-first_name">@lang('labels.firstName')</label>
            <input id="input-first_name" class="form-control" autofocus required placeholder="@lang('placeholders.firstName')" name="first_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-last_name">@lang('labels.lastName')</label>
            <input id="input-last_name" class="form-control" placeholder="@lang('placeholders.lastName')" name="last_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-gender">@lang('labels.gender')</label>
            <select required id="input-gender" class="form-control" name="gender">
                <option value="" selected="selected">@lang('placeholders.gender')</option>
                @foreach ($api->model->selectGender() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 'not-known' ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <div class="form-group">
            <label for="input-phone_number">@lang('labels.phone')</label>
            <div class="input-group">
                <select id="input-phone_code" class="form-control" name="phone_code">
                    <option value="" selected="selected">@lang('placeholders.phoneCode')</option>
                    @foreach ($api->model->selectPhoneCodes() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <input id="input-phone_number" disabled class="form-control" placeholder="@lang('placeholders.phoneNumber')" name="phone_number" type="tel">
            </div>
        </div>

        <div class="form-group">
            <label for="input-country_id">@lang('labels.country')</label>
            <select id="input-country_id" class="form-control" name="country_id">
                <option value="" selected="selected">@lang('placeholders.country')</option>
                @foreach ($api->model->selectCountries() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-city">@lang('labels.city')</label>
            <input id="input-city" class="form-control" placeholder="@lang('placeholders.city')" name="city" type="text">
        </div>

        <div class="form-group">
            <label for="input-postcode">@lang('labels.postcode')</label>
            <input id="input-postcode" class="form-control" placeholder="@lang('placeholders.postcode')" name="postcode" type="text">
        </div>

        <div class="form-group">
            <label for="input-address1">@lang('labels.address1')</label>
            <input id="input-address1" class="form-control" placeholder="@lang('placeholders.address1')" name="address1" type="text">
        </div>

        <div class="form-group">
            <label for="input-address2">@lang('labels.address2')</label>
            <input id="input-address2" class="form-control" placeholder="@lang('placeholders.address2')" name="address2" type="text">
        </div>

        <div class="form-group">
            <label for="input-bank">@lang('labels.bank')</label>
            <input id="input-bank" class="form-control" placeholder="@lang('placeholders.bank')" name="bank" type="text">
        </div>

        <div class="form-group">
            <label for="input-company_name">@lang('labels.company')</label>
            <input id="input-company_name" class="form-control" placeholder="@lang('placeholders.company')" name="company_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-company_phone">@lang('labels.companyPhone')</label>
            <input id="input-company_phone" class="form-control" placeholder="@lang('placeholders.companyPhone')" name="company_phone" type="text">
        </div>

        <div class="form-group">
            <label for="input-website">@lang('labels.website')</label>
            <input id="input-website" class="form-control" placeholder="@lang('placeholders.website')" name="website" type="url">
        </div>

        <div class="form-group">
            <label for="input-newsletters">@lang('labels.newslettersSubscription')</label>
            <select id="input-newsletters" class="form-control" name="newsletters">
                @foreach ($api->model->selectNewslettersSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 1 ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-sms">@lang('labels.smsSubscription')</label>
            <select id="input-sms" class="form-control" name="sms">
                @foreach ($api->model->selectSmsSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 1 ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-phone')

    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});

    $('#input-start_at').datepicker({
        onSelect: function(date) {
            var d = new Date(Date.parse($("#input-start_at").datepicker("getDate")));
            $('#input-end_at').datepicker('option', 'minDate', d);
            $('#input-end_at').removeAttr('disabled');
        },
    });
    $('#input-end_at').datepicker();

    var projects = document.querySelector('#input-projects');
    var country_id = document.querySelector('#input-country_id');

    $(projects).multiselect();

    $(country_id).multiselect({
        multiple: false,
    });
@endsection
