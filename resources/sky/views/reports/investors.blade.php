@extends('layouts.main')

@section('content')
   <div class="reports-wrapper">
       <form method="POST" action="{{ Helper::route('generate-report', $slug) }}" accept-charset="UTF-8" id="report-form" data-ajax>
            @csrf

            <div class="input-group">
                <div class="form-group mx-1">
                    <label for="input-projects">@lang('labels.projects')</label>
                    <select autocomplete="off" autofocus multiple required {{-- disabled --}} id="input-projects" class="form-control" name="projects[]">
                        @foreach ($projects as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-sources">@lang('labels.source')</label>
                    <select multiple id="input-sources" class="form-control" name="sources[]">
                        @foreach ($sources as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-countries">@lang('labels.countries')</label>
                    <select multiple id="input-countries" class="form-control" name="countries[]">
                        @foreach ($countries as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-fundSizes">@lang('labels.fundSize')</label>
                    <select multiple id="input-fundSizes" class="form-control" name="fundSizes[]">
                        @foreach ($fundSizes as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-investmentRanges">@lang('labels.investmentRange')</label>
                    <select multiple id="input-investmentRanges" class="form-control" name="investmentRanges[]">
                        @foreach ($investmentRanges as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-categories">@lang('labels.investorCategory')</label>
                    <select multiple id="input-categories" class="form-control" name="categories[]">
                        @foreach ($categories as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-start_at">@lang('labels.startAt')</label>
                    <input id="input-start_at" class="form-control" autocomplete="off" required placeholder="@lang('placeholders.startAt')" name="start_at" type="text">
                </div>

                <div class="form-group mx-1">
                    <label for="input-end_at">@lang('labels.endAt')</label>
                    <input id="input-end_at" class="form-control" autocomplete="off" {{-- disabled --}} placeholder="@lang('placeholders.endAt')" name="end_at" type="text">
                </div>
            </div>
       </form>
    </div>
@endsection

@push('scripts')
    <script>
      loadjs.ready('main', function() {
          $('#input-projects').multiselect();
          $('#input-sources').multiselect();
          $('#input-countries').multiselect();
          $('#input-fundSizes').multiselect();
          $('#input-investmentRanges').multiselect();
          $('#input-categories').multiselect();

          $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
          $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
          $('#input-start_at').datepicker({
              changeYear: true,
              changeMonth: true,
              onSelect: function(date) {
                  var d = new Date(Date.parse($("#input-start_at").datepicker("getDate")));
                  $('#input-end_at').datepicker('option', 'minDate', d);
                  // $('#input-end_at').removeAttr('disabled');
              },
          });

          $('#input-end_at').datepicker({
              changeYear: true,
              changeMonth: true,
          });
      });

      /*var buttonGenerate = document.querySelector('.button-generate');
      var buttonExport = document.querySelector('.button-export');
      var project = document.querySelector('#input-project_id');
      var agents = document.querySelector('#input-agents');*/
      /*var status_id = document.querySelector('#input-status_id');

      status_id.addEventListener('change', function () {
        if (parseInt(this.value) > 0) {
          buttonGenerate.disabled = false;
          buttonGenerate.classList.remove('disabled')
        } else {
          buttonGenerate.disabled = true;
          buttonGenerate.classList.add('disabled');
          buttonExport.classList.add('hidden')
          buttonExport.setAttribute('hidden', '')
          document.querySelector('.dataTables_wrapper').classList.add('table-hidden')
        }
      });*/

      /*project.addEventListener('change', function () {
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
