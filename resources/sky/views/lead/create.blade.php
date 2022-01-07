@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" autofocus placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <div class="form-group">
            <label for="input-sources">@lang('labels.source')</label>
            <select multiple id="input-sources" class="form-control" name="sources[]">
                @foreach ($api->model->selectSources() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-tags">@lang('labels.tag')</label>
            <select multiple id="input-tags" class="form-control" name="tags[]">
                @foreach ($api->model->selectTags() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" placeholder="@lang('placeholders.name')" name="name" type="text">
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
            <label for="input-newsletters">@lang('labels.newslettersSubscription')</label>
            <select id="input-newsletters" class="form-control" name="newsletters">
                @foreach ($api->model->selectNewslettersSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 0 ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-notes">@lang('labels.notes')</label>
            <textarea id="input-notes" class="form-control" placeholder="@lang('placeholders.notes')" name="notes"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-phone')
    var country_id = document.querySelector('#input-country_id');

    $(country_id).multiselect({
        multiple: false,
    });

    $('#input-sources').multiselect();
    $('#input-tags').multiselect();
@endsection
