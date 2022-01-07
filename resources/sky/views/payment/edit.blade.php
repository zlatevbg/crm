@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-paid_at">@lang('labels.paidAt')</label>
            <input id="input-paid_at" class="form-control" required placeholder="@lang('placeholders.paidAt')" name="paid_at" type="text" value="{{ $api->model->paid_at }}">
        </div>

        <div class="form-group">
            <label for="input-status_id">@lang('labels.status')</label>
            <select id="input-status_id" class="form-control" name="status_id">
                @foreach ($api->model->selectStatus() as $status)
                    <option value="{{ $status->id }}" {!! $api->model->status_id == $status->id ? 'selected="selected"' : '' !!}>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-payment_method_id">@lang('labels.paymentMethod')</label>
            <select id="input-payment_method_id" class="form-control" name="payment_method_id">
                @foreach ($api->model->selectPaymentMethods() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->payment_method_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-amount">@lang('labels.amount')</label>
            <input id="input-amount" class="form-control" required placeholder="@lang('placeholders.amount')" name="amount" type="text" value="{{ $api->model->amount }}">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-paid_at').datepicker({
        maxDate: 0,
    });
@endsection
