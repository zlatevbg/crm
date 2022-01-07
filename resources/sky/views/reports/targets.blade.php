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
                    <label for="input-year">@lang('labels.year')</label>
                    <select autocomplete="off" autofocus required {{-- disabled --}} id="input-year" class="form-control" name="year">
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-targets">@lang('labels.targets')</label>
                    <select autocomplete="off" multiple required {{-- disabled --}} id="input-targets" class="form-control" name="targets[]">
                        @foreach ($targets as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
       </form>
    </div>
@endsection

@push('scripts')
    <script>
      loadjs.ready('main', function() {
          var targets = document.querySelector('#input-targets');
          $('#input-year').multiselect({
              multiple: false,
              close: function() {
                  ajax.ajaxify({
                      obj: this,
                      method: 'get',
                      queue: 'sync',
                      action: '{{ Helper::route('api.load-data') }}',
                      data: 'method=Targets&year=' + this.value, // project=' + project.value + '&
                      skipErrors: true,
                  }).then(function (data) {
                      while (targets.firstChild) {
                          targets.removeChild(targets.firstChild);
                      }

                      $.each(data.data.targets, function(key, value) {
                          $(targets).append($('<option></option>').attr('value', value.id).text(value.target));
                      });

                      targets.disabled = false;
                      $(targets).multiselect('enable');
                      $(targets).multiselect('refresh');
                  }).catch(function (error) {
                  });
              },
          });

          $(targets).multiselect();
      });

      /*var buttonGenerate = document.querySelector('.button-generate');
      var buttonExport = document.querySelector('.button-export');
      var project = document.querySelector('#input-project_id');
      var year = document.querySelector('#input-year');
      var targets = document.querySelector('#input-targets');

      project.addEventListener('change', function () {
        if (parseInt(this.value) > 0) {
          buttonGenerate.disabled = false;
          buttonGenerate.classList.remove('disabled')

          ajax.ajaxify({
            obj: this,
            method: 'get',
            queue: 'sync',
            action: '{{ Helper::route('api.load-data') }}',
            data: 'method=Targets&project=' + this.value,
            skipErrors: true,
          }).then(function (data) {
            while (year.firstChild) {
                year.removeChild(year.firstChild);
            }

            $.each(data.data.years, function(key, value) {
                $(year).append($('<option></option>').attr('value', value.year).text(value.year));
            });

            year.disabled = false;
            $(year).multiselect('enable');
            $(year).multiselect('refresh');

            while (targets.firstChild) {
                targets.removeChild(targets.firstChild);
            }

            $.each(data.data.targets, function(key, value) {
                $(targets).append($('<option></option>').attr('value', value.id).text(value.target));
            });

            targets.disabled = false;
            $(targets).multiselect('enable');
            $(targets).multiselect('refresh');
          }).catch(function (error) {
          });
        } else {
          buttonGenerate.disabled = true;
          buttonGenerate.classList.add('disabled');
          buttonExport.classList.add('hidden')
          buttonExport.setAttribute('hidden', '')
          document.querySelector('.dataTables_wrapper').classList.add('table-hidden')

          while (year.firstChild) {
            year.removeChild(year.firstChild);
          }

          year.disabled = true;
          $(year).multiselect('disable');
          $(year).multiselect('refresh');

          while (targets.firstChild) {
            targets.removeChild(targets.firstChild);
          }

          targets.disabled = true;
          $(targets).multiselect('disable');
          $(targets).multiselect('refresh');
        }
      });*/

      function downloadReport(data) {
        window.location.href = '{{ Helper::route('download-report', $slug) }}/' + data.uuid;
      }
    </script>
@endpush
