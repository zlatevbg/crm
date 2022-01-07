@extends('layouts.main')

@section('content')
   <div class="reports-wrapper">
       <form method="POST" action="{{ Helper::route('generate-report', $slug) }}" accept-charset="UTF-8" id="report-form" data-ajax>
            @csrf

            <div class="input-group">
                {{-- <div class="form-group mx-1">
                    <label for="input-project_id">@lang('labels.project')</label>
                    <select autocomplete="off" autofocus required id="input-project_id" class="form-control" name="project_id">
                        <option selected="selected">@lang('placeholders.project')</option>
                        @foreach ($projects as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="form-group mx-1">
                    <label for="input-agents">@lang('labels.agents')</label>
                    <select autocomplete="off" autofocus multiple {{-- disabled --}}  id="input-agents" class="form-control" name="agents[]">
                        @foreach ($agents as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-clients">@lang('labels.clients')</label>
                    <select autocomplete="off" multiple id="input-clients" class="form-control" name="clients[]">
                        @foreach ($clients as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-apartments">@lang('labels.apartments')</label>
                    <select autocomplete="off" multiple id="input-apartments" class="form-control" name="apartments[]">
                        @foreach ($apartments as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-statuses">@lang('labels.statuses')</label>
                    <select autocomplete="off" multiple id="input-statuses" class="form-control" name="statuses[]">
                        @foreach ($statuses as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="input-group d-flex justify-content-center">
                <div class="form-group mx-1">
                    <label for="input-dfrom">@lang('labels.dateFrom')</label>
                    <input id="input-dfrom" class="form-control" autocomplete="off" placeholder="@lang('placeholders.dateFrom')" name="dfrom" type="text">
                </div>

                <div class="form-group mx-1">
                    <label for="input-dto">@lang('labels.dateTo')</label>
                    <input id="input-dto" class="form-control" autocomplete="off" {{-- disabled --}} placeholder="@lang('placeholders.dateTo')" name="dto" type="text">
                </div>
            </div>
       </form>
    </div>
@endsection

@push('scripts')
    <script>
      loadjs.ready('main', function() {
          $('#input-agents').multiselect();
          $('#input-clients').multiselect();
          $('#input-apartments').multiselect();
          $('#input-statuses').multiselect();

          $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
          $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
          $('#input-dfrom').datepicker({
              changeYear: true,
              changeMonth: true,
              maxDate: 0,
              onSelect: function(date) {
                  var d = new Date(Date.parse($("#input-dfrom").datepicker("getDate")));
                  $('#input-dto').datepicker('option', 'minDate', d);
                  // $('#input-dto').removeAttr('disabled');
              },
          });

          $('#input-dto').datepicker({
              changeYear: true,
              changeMonth: true,
              maxDate: 0,
          });
      });

      /*var buttonGenerate = document.querySelector('.button-generate');
      var buttonExport = document.querySelector('.button-export');
      var project = document.querySelector('#input-project_id');
      var agents = document.querySelector('#input-agents');

      project.addEventListener('change', function () {
        if (parseInt(this.value) > 0) {
          buttonGenerate.disabled = false;
          buttonGenerate.classList.remove('disabled')

          ajax.ajaxify({
            obj: this,
            method: 'get',
            queue: 'sync',
            action: '{{ Helper::route('api.load-data') }}',
            data: 'method=Agents&project=' + this.value,
            skipErrors: true,
          }).then(function (data) {
            while (agents.firstChild) {
                agents.removeChild(agents.firstChild);
            }

            $.each(data.data, function(key, value) {
                $(agents).append($('<option></option>').attr('value', value.id).text(value.agent));
            });

            agents.disabled = false;
            $(agents).multiselect('enable');
            $(agents).multiselect('refresh');
          }).catch(function (error) {
          });
        } else {
          buttonGenerate.disabled = true;
          buttonGenerate.classList.add('disabled');
          buttonExport.classList.add('hidden')
          buttonExport.setAttribute('hidden', '')
          document.querySelector('.dataTables_wrapper').classList.add('table-hidden')

          while (agents.firstChild) {
            agents.removeChild(agents.firstChild);
          }

          agents.disabled = true;
          $(agents).multiselect('disable');
          $(agents).multiselect('refresh');
        }
      });*/

      function downloadReport(data) {
        window.location.href = '{{ Helper::route('download-report', $slug) }}/' + data.uuid;
      }
    </script>
@endpush
