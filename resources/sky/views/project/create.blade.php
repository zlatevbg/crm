@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        @if (!array_key_exists('database', request()->all()))
            <input id="input-status" name="status" value="1" type="hidden">
        @else
            <input id="input-status" name="status" value="0" type="hidden">
        @endif

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text">
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
            <label for="input-location">@lang('labels.location')</label>
            <input id="input-location" class="form-control" required placeholder="@lang('placeholders.location')" name="location" type="text">
        </div>

        @if (array_key_exists('database', request()->all()))
            <div class="form-group">
                <label for="input-price">@lang('labels.price')</label>
                <input id="input-price" class="form-control" required placeholder="0.00" min="0" max="999999999.99" name="price" step=".01" type="number">
            </div>

            <div class="form-group">
                <label for="input-site_area">@lang('labels.siteArea')</label>
                <input id="input-site_area" class="form-control" placeholder="0.00" min="0" max="999999.99" name="site_area" step=".01" type="number">
            </div>

            <div class="form-group">
                <label for="input-construction_area">@lang('labels.constructionArea')</label>
                <input id="input-construction_area" class="form-control" placeholder="0.00" min="0" max="999999.99" name="construction_area" step=".01" type="number">
            </div>

            <div class="form-group">
                <label for="input-gdv">@lang('labels.gdv')</label>
                <input id="input-gdv" class="form-control" placeholder="0.00" min="0" max="999999999.99" name="gdv" step=".01" type="number">
            </div>

            <div class="form-group">
                <label for="input-equity">@lang('labels.equity')</label>
                <input id="input-equity" class="form-control" placeholder="0.00" min="0" max="999999999.99" name="equity" step=".01" type="number">
            </div>

            <div class="form-group">
                <label for="input-bank">@lang('labels.bank')</label>
                <input id="input-bank" class="form-control" placeholder="0.00" min="0" max="999999999.99" name="bank" step=".01" type="number">
            </div>

            <div class="form-group">
                <label for="input-period">@lang('labels.investmentPeriod')</label>
                <input id="input-period" class="form-control" placeholder="@lang('placeholders.investmentPeriod')" name="period" type="text">
            </div>

            <div class="form-group">
                <label for="input-irr">@lang('labels.targetIrr')</label>
                <input id="input-irr" class="form-control" placeholder="@lang('placeholders.targetIrr')" name="irr" type="text">
            </div>

            <div class="form-group">
                <label for="input-contact_id">@lang('labels.introducer')</label>
                <select id="input-contact_id" class="form-control" name="contact_id">
                    <option value="" selected="selected">@lang('placeholders.introducer')</option>
                    @foreach ($api->model->selectContact() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description"></textarea>
        </div>

        @if (array_key_exists('database', request()->all()))
            <p class="form-section">@lang('labels.projectFeatures')</p>

            @foreach ($api->model->selectFeatures() as $features)
                <div class="form-group">
                    <label for="input-features-{{ $loop->iteration }}">{{ $features['name'] }}</label>
                    @isset($features['children'])
                        <select id="input-features-{{ $loop->iteration }}" class="form-control" name="features[{{ $features['id'] }}]">
                            <option value="">@lang('multiselect.noneSelectedSingle')</option>
                            @foreach ($features['children'] as $feature)
                                <option value="{{ $feature['id'] }}">{{ $feature['name'] }}</option>
                            @endforeach
                        </select>
                    @endisset
                </div>
            @endforeach
        @endif

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @if (array_key_exists('database', request()->all()))
        var contact_id = document.querySelector('#input-contact_id');
        $(contact_id).multiselect({
            multiple: false,
        });
    @endif

    var country_id = document.querySelector('#input-country_id');
    $(country_id).multiselect({
        multiple: false,
    });
@endsection
