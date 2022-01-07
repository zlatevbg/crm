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
                    <label for="input-dfrom">@lang('labels.dateFrom')</label>
                    <input id="input-dfrom" class="form-control" autocomplete="off" autofocus required placeholder="@lang('placeholders.dateFrom')" name="dfrom" type="text">
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
      loadjs(
        [
          '{{ Helper::autover('/css/' . Domain::current() . '/vendor/jquery-ui.css') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/vendor/jquery-ui.js') }}',
        ],
        {
          success: function() {
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
          },
          async: false,
        }
      );

      /*var buttonGenerate = document.querySelector('.button-generate');
      var buttonExport = document.querySelector('.button-export');
      var project = document.querySelector('#input-project_id');

      project.addEventListener('change', function () {
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

      function downloadReport(data) {
        window.location.href = '{{ Helper::route('download-report', $slug) }}/' + data.uuid;
      }
    </script>
@endpush
