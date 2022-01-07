@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-first_name">@lang('labels.firstName')</label>
            <input id="input-first_name" class="form-control" autofocus required placeholder="@lang('placeholders.firstName')" name="first_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-last_name">@lang('labels.lastName')</label>
            <input id="input-last_name" class="form-control" placeholder="@lang('placeholders.lastName')" name="last_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" placeholder="@lang('placeholders.title')" name="title" type="text">
        </div>

        <div class="form-group">
            <label for="input-gender">@lang('labels.gender')</label>
            <select id="input-gender" class="form-control" name="gender">
                <option selected="selected">@lang('placeholders.gender')</option>
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
            <label for="input-newsletters">@lang('labels.newslettersSubscription')</label>
            <select id="input-newsletters" class="form-control" name="newsletters">
                @foreach ($api->model->selectNewslettersSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 1 ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-phone')
@endsection
