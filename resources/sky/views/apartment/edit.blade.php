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
            <label for="input-unit">@lang('labels.unit')</label>
            <input id="input-unit" class="form-control" autofocus required placeholder="@lang('placeholders.unit')" name="unit" type="text" value="{{ $api->model->unit }}">
        </div>

        <div class="form-group">
            <label for="input-price">@lang('labels.price')</label>
            <input id="input-price" class="form-control" required placeholder="0.00" min="0" max="9999999.99" name="price" step=".01" type="number" value="{{ $api->model->price }}">
        </div>

        @if (count($api->model->selectBlocks($api)))
            <div class="form-group">
                <label for="input-block_id">@lang('labels.block')</label>
                <select id="input-block_id" class="form-control" name="block_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectBlocks($api) as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->block_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-block_id">@lang('labels.block')</label>
                <select disabled id="input-block_id" class="form-control" name="block_id"></select>
            </div>
        @endif

        @if (count($api->model->selectFloors($api)))
            <div class="form-group">
                <label for="input-floor_id">@lang('labels.floor')</label>
                <select id="input-floor_id" class="form-control" name="floor_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectFloors($api) as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->floor_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-floor_id">@lang('labels.floor')</label>
                <select disabled id="input-floor_id" class="form-control" name="floor_id"></select>
            </div>
        @endif

        @if (count($api->model->selectBeds($api)))
            <div class="form-group">
                <label for="input-bed_id">@lang('labels.bed')</label>
                <select id="input-bed_id" class="form-control" name="bed_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectBeds($api) as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->bed_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-bed_id">@lang('labels.bed')</label>
                <select disabled id="input-bed_id" class="form-control" name="bed_id"></select>
            </div>
        @endif

        @if (count($api->model->selectViews($api)))
            <div class="form-group">
                <label for="input-view_id">@lang('labels.view')</label>
                <select id="input-view_id" class="form-control" name="view_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectViews($api) as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->view_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-view_id">@lang('labels.view')</label>
                <select disabled id="input-view_id" class="form-control" name="view_id"></select>
            </div>
        @endif

        @if (count($api->model->selectFurnitures($api)))
            <div class="form-group">
                <label for="input-furniture_id">@lang('labels.furniture')</label>
                <select id="input-furniture_id" class="form-control" name="furniture_id">
                    <option value="">@lang('multiselect.noneSelectedSingle')</option>
                    @foreach ($api->model->selectFurnitures($api) as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->furniture_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-furniture_id">@lang('labels.furniture')</label>
                <select disabled id="input-furniture_id" class="form-control" name="furniture_id"></select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-apartment_area">@lang('labels.apartmentArea')</label>
            <input id="input-apartment_area" class="form-control" placeholder="0.00" min="0" max="9999.99" name="apartment_area" step=".01" type="number" value="{{ $api->model->apartment_area }}">
        </div>

        <div class="form-group">
            <label for="input-balcony_area">@lang('labels.balconyArea')</label>
            <input id="input-balcony_area" class="form-control" placeholder="0.00" min="0" max="9999.99" name="balcony_area" step=".01" type="number" value="{{ $api->model->balcony_area }}">
        </div>

        <div class="form-group">
            <label for="input-parking_area">@lang('labels.parkingArea')</label>
            <input id="input-parking_area" class="form-control" placeholder="0.00" min="0" max="9999.99" name="parking_area" step=".01" type="number" value="{{ $api->model->parking_area }}">
        </div>

        <div class="form-group">
            <label for="input-common_area">@lang('labels.commonArea')</label>
            <input id="input-common_area" class="form-control" placeholder="0.00" min="0" max="9999.99" name="common_area" step=".01" type="number" value="{{ $api->model->common_area }}">
        </div>

        <div class="form-group">
            <label for="input-total_area">@lang('labels.totalArea')</label>
            <input id="input-total_area" class="form-control" placeholder="0.00" min="0" max="9999.99" name="total_area" step=".01" type="number" value="{{ $api->model->total_area }}">
        </div>

        <div class="form-group">
            <label for="input-reports">@lang('labels.includeInReports')</label>
            <select id="input-reports" class="form-control" name="reports">
                @foreach ($api->model->selectReportsVisibility() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->reports == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-public">@lang('labels.includeInPublic')</label>
            <select id="input-public" class="form-control" name="public">
                @foreach ($api->model->selectPublicVisibility() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->public == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @if (!session('project'))
        var project = document.querySelector('#input-project_id');
        var selectedProject = '{{ $api->model->project_id }}';
        var block_id = document.querySelector('#input-block_id');
        var floor_id = document.querySelector('#input-floor_id');
        var bed_id = document.querySelector('#input-bed_id');
        var view_id = document.querySelector('#input-view_id');
        var furniture_id = document.querySelector('#input-furniture_id');

        project.addEventListener('change', function() {
            if (parseInt(this.value) != selectedProject) {
                selectedProject = this.value;

                if (parseInt(this.value) > 0) {
                    ajax.ajaxify({
                        obj: this,
                        method: 'get',
                        queue: 'sync',
                        action: '{{ Helper::route('api.load-data') }}',
                        data: 'method=BlocksFloorsBedsViewsFurniture&project=' + this.value,
                        skipErrors: true,
                    }).then(function (data) {
                        while (block_id.firstChild) {
                            block_id.removeChild(block_id.firstChild);
                        }

                        if (data.data.blocks.length > 1) {
                            $.each(data.data.blocks, function(key, value) {
                                $(block_id).append($('<option></option>').attr('value', value.id).text(value.name));
                            });

                            block_id.closest('.form-group').classList.remove('table-hidden');
                            block_id.disabled = false;
                        } else {
                            block_id.value = '';
                            block_id.disabled = true;
                            block_id.closest('.form-group').classList.add('table-hidden');
                        }

                        while (floor_id.firstChild) {
                            floor_id.removeChild(floor_id.firstChild);
                        }

                        if (data.data.floors.length > 1) {
                            $.each(data.data.floors, function(key, value) {
                                $(floor_id).append($('<option></option>').attr('value', value.id).text(value.name));
                            });

                            floor_id.closest('.form-group').classList.remove('table-hidden');
                            floor_id.disabled = false;
                        } else {
                            floor_id.value = '';
                            floor_id.disabled = true;
                            floor_id.closest('.form-group').classList.add('table-hidden');
                        }

                        while (bed_id.firstChild) {
                            bed_id.removeChild(bed_id.firstChild);
                        }

                        if (data.data.beds.length > 1) {
                            $.each(data.data.beds, function(key, value) {
                                $(bed_id).append($('<option></option>').attr('value', value.id).text(value.name));
                            });

                            bed_id.closest('.form-group').classList.remove('table-hidden');
                            bed_id.disabled = false;
                        } else {
                            bed_id.value = '';
                            bed_id.disabled = true;
                            bed_id.closest('.form-group').classList.add('table-hidden');
                        }

                        while (view_id.firstChild) {
                            view_id.removeChild(view_id.firstChild);
                        }

                        if (data.data.views.length > 1) {
                            $.each(data.data.views, function(key, value) {
                                $(view_id).append($('<option></option>').attr('value', value.id).text(value.name));
                            });

                            view_id.closest('.form-group').classList.remove('table-hidden');
                            view_id.disabled = false;
                        } else {
                            view_id.value = '';
                            view_id.disabled = true;
                            view_id.closest('.form-group').classList.add('table-hidden');
                        }

                        while (furniture_id.firstChild) {
                            furniture_id.removeChild(furniture_id.firstChild);
                        }

                        if (data.data.furniture.length > 1) {
                            $.each(data.data.furniture, function(key, value) {
                                $(furniture_id).append($('<option></option>').attr('value', value.id).text(value.name));
                            });

                            furniture_id.closest('.form-group').classList.remove('table-hidden');
                            furniture_id.disabled = false;
                        } else {
                            furniture_id.value = '';
                            furniture_id.disabled = true;
                            furniture_id.closest('.form-group').classList.add('table-hidden');
                        }
                    }).catch(function (error) {
                    });
                } else {
                    block_id.value = '';
                    block_id.disabled = true;
                    block_id.closest('.form-group').classList.add('table-hidden');

                    floor_id.value = '';
                    floor_id.disabled = true;
                    floor_id.closest('.form-group').classList.add('table-hidden');

                    bed_id.value = '';
                    bed_id.disabled = true;
                    bed_id.closest('.form-group').classList.add('table-hidden');

                    view_id.value = '';
                    view_id.disabled = true;
                    view_id.closest('.form-group').classList.add('table-hidden');

                    furniture_id.value = '';
                    furniture_id.disabled = true;
                    furniture_id.closest('.form-group').classList.add('table-hidden');
                }
            }
        });
    @endif
@endsection
