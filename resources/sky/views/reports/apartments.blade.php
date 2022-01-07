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
                    <label for="input-status_id">@lang('labels.status')</label>
                    <select autocomplete="off" autofocus required id="input-status_id" class="form-control" name="status_id">
                        <option value="0" selected="selected">@lang('placeholders.all')</option>
                        @foreach ($statuses as $key => $value)
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
