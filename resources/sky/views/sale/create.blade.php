@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        @if (session('project'))
            <input id="input-project_id" name="project_id" value="{{ session('project') }}" type="hidden">
        @else
            <div class="form-group">
                <label for="input-project_id">@lang('labels.project')</label>
                <select required id="input-project_id" class="form-control" name="project_id">
                    <option selected="selected">@lang('placeholders.project')</option>
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-apartment_id">@lang('labels.apartment')</label>
            <select autofocus required {{ !session('project') ? 'disabled' : '' }} id="input-apartment_id" class="form-control" name="apartment_id">
                @if (session('project'))
                    <option selected="selected">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectApartment() as $value)
                        <option data-price="{{ $value->price }}" data-furniture="{{ $value->furniture }}" value="{{ $value->id }}">{{ $value->apartment }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="input-price">@lang('labels.price')</label>
            <input id="input-price" class="form-control" disabled required placeholder="@lang('placeholders.price')" name="price" type="text">
        </div>

        <div class="form-group">
            <label for="input-furniture">@lang('labels.furniturePack')</label>
            <input id="input-furniture" class="form-control" disabled placeholder="@lang('placeholders.furniturePack')" name="furniture" type="text">
        </div>

        <div class="form-group">
            <label for="input-client_id">@lang('labels.client')</label>
            <select disabled required id="input-client_id" class="form-control" name="client_id">
                @if (session('project'))
                    <option value="0" selected="selected">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectClient() as $value)
                        <option value="{{ $value->id }}" data-agent="{{ $value->agent_id }}">{{ $value->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="input-agent_id">@lang('labels.agent')</label>
            <select disabled id="input-agent_id" class="form-control" name="agent_id">
                @if (session('project'))
                    <option selected="selected">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectAgents() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="input-commission">@lang('labels.commission')</label>
            <input id="input-commission" class="form-control" required disabled placeholder="@lang('placeholders.commission')" name="commission" type="text">
        </div>

        <div class="form-group">
            <label for="input-subagent_id">@lang('labels.subAgent')</label>
            <select disabled id="input-subagent_id" class="form-control" name="subagent_id">
                <option value="" selected="selected">@lang('multiselect.noneSelectedSingle')</option>
                @if (session('project'))
                    @foreach ($api->model->selectSubAgents() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="input-sub_commission">@lang('labels.subCommission')</label>
            <input id="input-sub_commission" class="form-control" disabled placeholder="@lang('placeholders.subCommission')" name="sub_commission" type="text">
        </div>

        <div class="form-group">
            <label for="input-closing_at">@lang('labels.closingAt')</label>
            <input id="input-closing_at" class="form-control" required placeholder="@lang('placeholders.closingAt')" name="closing_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-promissory_at">@lang('labels.promissoryAt')</label>
            <input id="input-promissory_at" class="form-control" placeholder="@lang('placeholders.promissoryAt')" name="promissory_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-lawyer">@lang('labels.lawyer')</label>
            <input id="input-lawyer" class="form-control" placeholder="@lang('placeholders.lawyer')" name="lawyer" type="text">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-sale')

    $('#input-closing_at').datepicker({
        minDate: '+1d',
    });
@endsection
