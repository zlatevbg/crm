@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-paid_at">@lang('labels.paidAt')</label>
            <input id="input-paid_at" class="form-control" autofocus required placeholder="@lang('placeholders.paidAt')" name="paid_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-status_id">@lang('labels.status')</label>
            <select id="input-status_id" class="form-control" name="status_id">
                @foreach ($api->model->selectStatus() as $status)
                    <option value="{{ $status->id }}" {!! $status->default ? 'selected="selected"' : '' !!}>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-payment_method_id">@lang('labels.paymentMethod')</label>
            <select required id="input-payment_method_id" class="form-control" name="payment_method_id">
                <option selected="selected">@lang('placeholders.paymentMethod')</option>
                @foreach ($api->model->selectPaymentMethods() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-amount">@lang('labels.amount')</label>
            <input id="input-amount" class="form-control" required placeholder="@lang('placeholders.amount')" name="amount" type="text">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-paid_at').datepicker({
        maxDate: 0,
    });
@endsection
