@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-company">@lang('labels.company')</label>
            <input id="input-company" class="form-control" autofocus placeholder="@lang('placeholders.company')" name="company" type="text" value="{{ $api->model->company }}">
        </div>

        <div class="form-group">
            <label for="input-type">@lang('labels.type')</label>
            <select id="input-type" class="form-control" name="type">
                @foreach ($api->model->selectType() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->type == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-goldenvisa">@lang('labels.goldenVisaAgent')</label>
            <select id="input-goldenvisa" class="form-control" name="goldenvisa">
                @foreach ($api->model->selectGoldenVisaAgents() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->goldenvisa == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-website">@lang('labels.website')</label>
            <input id="input-website" class="form-control" placeholder="@lang('placeholders.website')" name="website" type="url" value="{{ $api->model->website }}">
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

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-phone')
    var country_id = document.querySelector('#input-country_id');

    $(country_id).multiselect({
        multiple: false,
    });
@endsection
