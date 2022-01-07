@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        @if (session('project'))
            <input id="input-project_id" name="project_id" value="{{ session('project') }}" type="hidden">
        @else
            <div class="form-group">
                <label for="input-project_id">@lang('labels.project')</label>
                <select required id="input-project_id" class="form-control" name="project_id">
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->project_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-apartment_id">@lang('labels.apartment')</label>
            <select autofocus required id="input-apartment_id" class="form-control" name="apartment_id">
                @foreach ($api->model->selectApartment($api) as $value)
                    <option data-price="{{ $value->price }}" data-furniture="{{ $value->furniture }}" value="{{ $value->id }}" {!! $api->model->apartment_id == $value->id ? 'selected="selected"' : '' !!}>{{ $value->apartment }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-price">@lang('labels.price')</label>
            <input id="input-price" class="form-control" required placeholder="@lang('placeholders.price')" name="price" type="text" value="{{ $api->model->price }}">
        </div>

        <div class="form-group">
            <label for="input-furniture">@lang('labels.furniturePack')</label>
            <input id="input-furniture" class="form-control" placeholder="@lang('placeholders.furniturePack')" name="furniture" type="text" value="{{ $api->model->furniture }}">
        </div>

        <div class="form-group">
            <label for="input-client_id">@lang('labels.client')</label>
            <select required id="input-client_id" class="form-control" name="client_id">
                <option value="0">@lang('multiselect.noneSelectedSingle')</option>
                @foreach ($api->model->selectClient($api) as $value)
                    <option value="{{ $value->id }}" {!! $api->model->client_id == $value->id ? 'selected="selected"' : '' !!} data-agent="{{ $value->agent_id }}">{{ $value->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-agent_id">@lang('labels.agent')</label>
            <select id="input-agent_id" class="form-control" name="agent_id">
                <option value="">@lang('multiselect.noneSelectedSingle')</option>
                @foreach ($api->model->selectAgents($api) as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->agent_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-commission">@lang('labels.commission')</label>
            <input id="input-commission" class="form-control" required placeholder="@lang('placeholders.commission')" name="commission" type="text" value="{{ $api->model->commission }}">
        </div>

        <div class="form-group">
            <label for="input-subagent_id">@lang('labels.subAgent')</label>
            <select id="input-subagent_id" class="form-control" name="subagent_id">
                <option value="">@lang('multiselect.noneSelectedSingle')</option>
                @foreach ($api->model->selectSubAgents($api) as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->subagent_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-sub_commission">@lang('labels.subCommission')</label>
            <input id="input-sub_commission" class="form-control" required placeholder="@lang('placeholders.subCommission')" name="sub_commission" type="text" value="{{ $api->model->sub_commission }}" {{ $api->model->subagent_id ? '' : 'disabled' }}>
        </div>

        <div class="form-group">
            <label for="input-closing_at">@lang('labels.closingAt')</label>
            <input id="input-closing_at" class="form-control" required placeholder="@lang('placeholders.closingAt')" name="closing_at" type="text" value="{{ $api->model->closing_at }}">
        </div>

        <div class="form-group">
            <label for="input-promissory_at">@lang('labels.promissoryAt')</label>
            <input id="input-promissory_at" class="form-control" placeholder="@lang('placeholders.promissoryAt')" name="promissory_at" type="text" value="{{ $api->model->promissory_at }}">
        </div>

        <div class="form-group">
            <label for="input-lawyer">@lang('labels.lawyer')</label>
            <input id="input-lawyer" class="form-control" placeholder="@lang('placeholders.lawyer')" name="lawyer" type="text" value="{{ $api->model->lawyer }}">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-sale')

    $('#input-closing_at').datepicker();
@endsection
