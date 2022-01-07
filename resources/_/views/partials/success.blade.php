@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="@lang('buttons.close')"><span aria-hidden="true">&times;</span></button>
        <ul>
            @foreach (array_wrap(session('success')) as $s)
                <li>{{ $s }}</li>
            @endforeach
        </ul>
    </div>
@endif
