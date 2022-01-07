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
                    <select autocomplete="off" autofocus multiple required {{-- disabled --}} id="input-agents" class="form-control" name="agents[]">
                        @foreach ($agents as $key => $value)
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
          $('#input-agents').multiselect();
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
