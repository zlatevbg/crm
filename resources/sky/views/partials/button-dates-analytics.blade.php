<span class="separator"></span>
<form action="{{ Helper::route('analytics', $api->id, false) }}" accept-charset="UTF-8">
    <div class="input-group">
        <div class="form-group date-range">
            <input id="input-from" class="form-control" autocomplete="off" placeholder="@lang('placeholders.dateFrom')" name="from" type="text" value="{{ Request::input('from') }}">
        </div>
        <div class="form-group mx-1 date-range">
            <input id="input-to" class="form-control" autocomplete="off" placeholder="@lang('placeholders.dateTo')" name="to" type="text" value="{{ Request::input('to') }}">
        </div>
        <button id="button-dates" type="submit" {{ (Request::input('from') || Request::input('to') ? '' : 'disabled') }} class="btn btn-primary" autocomplete="off">@lang('buttons.ok')</button>
    </div>
</form>
