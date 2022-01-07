@extends('layouts.main')

@section('content')
   <div class="reports-wrapper">
       <form method="POST" action="{{ Helper::route('generate-report', $slug) }}" accept-charset="UTF-8" id="report-form" data-ajax>
            @csrf

            <div class="input-group">
                <div class="form-group mx-1">
                    <label for="input-users">@lang('labels.users')</label>
                    <select autocomplete="off" autofocus multiple required id="input-users" class="form-control" name="users[]">
                        @foreach ($users as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-priorities">@lang('labels.priorities')</label>
                    <select autocomplete="off" autofocus multiple required id="input-priorities" class="form-control" name="priorities[]">
                        @foreach ($priorities as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-departments">@lang('labels.departments')</label>
                    <select autocomplete="off" autofocus multiple required id="input-departments" class="form-control" name="departments[]">
                        @foreach ($departments as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mx-1">
                    <label for="input-status_id">@lang('labels.status')</label>
                    <select autocomplete="off" required id="input-status_id" class="form-control" name="status_id">
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
      loadjs.ready('main', function() {
          $('#input-users').multiselect();
          $('#input-priorities').multiselect();
          $('#input-departments').multiselect();
      });

      function downloadReport(data) {
        window.location.href = '{{ Helper::route('download-report', $slug) }}/' + data.uuid;
      }
    </script>
@endpush
