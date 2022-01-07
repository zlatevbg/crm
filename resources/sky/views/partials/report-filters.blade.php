<div class="form-group form-group-analytics mb-0">
    <label for="input-view" class="sr-only">@lang('labels.view')</label>
    <select id="input-view" class="form-control" name="view">
        <option value="" {!! !Request::segment(2) ? 'selected="selected"' : '' !!}>@lang('text.dashboard')</option>
        <option value="sales" {!! Request::segment(3) === 'sales' ? 'selected="selected"' : '' !!}>@lang('labels.sales')</option>
        <option value="funding" {!! Request::segment(3) === 'funding' ? 'selected="selected"' : '' !!}>@lang('labels.funding')</option>
        <option value="analytics" {!! Request::segment(3) === 'analytics' ? 'selected="selected"' : '' !!}>@lang('labels.analytics')</option>
    </select>
</div>
<span class="separator"></span>

@push('scripts')
    <script>
        loadjs.ready('main', function() {
            var view = document.querySelector('#input-view');
            if (view) {
                var currentView = view.value;
                $(view).multiselect({
                    multiple: false,
                    footer: false,
                    close: function() {
                        if (currentView != this.value) {
                            currentView = this.value;

                            ajax.ajaxify({
                                obj: this,
                                method: 'post',
                                queue: 'sync',
                                action: '{{ Helper::route('api.change-report-view', [], false) }}' + (this.value ? '/' + this.value : ''),
                                skipErrors: true,
                            }).then(function (data) {
                            }).catch(function (error) {
                            });
                        }
                    }
                });
            }
        });
    </script>
@endpush
