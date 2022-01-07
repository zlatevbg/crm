@php $overview = (!isset($key) || (isset($key) && !ends_with($key, '-overview'))) ? false : true; @endphp

<div class="datatable-buttons">
    @foreach ($buttons as $action => $button)
        @if (isset($button['dropdown']) && (!isset($button['visible']) || $button['visible'] === true))
            <div class="dropdown">
                <button id="{{ isset($key) ? $key . '-' : '' }}button-{{ $action }}-dropdown" data-toggle="dropdown" class="btn {{ $overview ? '' : 'fa-left' }} {{ $button['class'] }}" {!! $button['parameters'] ?? null !!} aria-haspopup="true" aria-expanded="false">
                    @if ($button['icon'])<i class="fas fa-{{ $button['icon'] }}"></i>@endif
                    {{ $overview ? '' : $button['name'] }}
                </button>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-right-button" aria-labelledby="button-{{ $action }}-dropdown">
                    @foreach ($button['dropdown'] as $item)
                        @if (str_contains($button['class'], 'js-link'))
                            <a id="{{ isset($key) ? $key . '-' : '' }}button-{{ $action }}" href="{{ $item['url'] }}" @isset($key) data-table="{{ $key }}" @endisset class="btn btn-lg dropdown-item {{ $overview ? '' : 'fa-left' }} {{ $item['class'] }}">
                                @if ($item['icon'])<i class="fas fa-fw fa-{{ $item['icon'] }}"></i>@endif
                                {{ $overview ? '' : $item['name'] }}
                            </a>
                        @else
                            <button id="{{ isset($key) ? $key . '-' : '' }}button-{{ $action }}" data-target=".modal" data-toggle="modal" data-action="{{ $item['url'] }}" @isset($key) data-table="{{ $key }}" @endisset class="btn btn-lg dropdown-item {{ $overview ? '' : 'fa-left' }} {{ $item['class'] }}" @if ($item['method'])data-method="{{ $item['method'] }}"@endif @if ($overview) data-overview @endif>
                                @if ($item['icon'])<i class="fas fa-fw fa-{{ $item['icon'] }}"></i>@endif
                                {{ $overview ? '' : $item['name'] }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @elseif (isset($button['view']))
            @include('partials/' . $button['view'])
        @elseif ($action === 'upload' && (!isset($button['visible']) || $button['visible'] === true))
            <div data-action="{{ $button['url'] }}" @isset($key) data-table="{{ $key }}" @endisset class="btn {{ $overview ? '' : 'fa-left' }} {{ $button['class'] }}" {!! $button['parameters'] ?? null !!}>
                @if ($button['icon'])<i class="fas fa-{{ $button['icon'] }}"></i>@endif
                {{ $overview ? '' : $button['name'] }}
            </div>
        @elseif (str_contains($button['class'], 'js-link') && (!isset($button['visible']) || $button['visible'] === true))
            <a id="{{ isset($key) ? $key . '-' : '' }}button-{{ $action }}" href="{{ $button['url'] }}" @isset($key) data-table="{{ $key }}" @endisset class="btn {{ $overview ? '' : 'fa-left' }} {{ $button['class'] }}" {!! $button['parameters'] ?? null !!}>
                @if ($button['icon'])<i class="fas fa-{{ $button['icon'] }}"></i>@endif
                {{ $overview ? '' : $button['name'] }}
            </a>
        @elseif (!isset($button['visible']) || $button['visible'] === true)
            <button id="{{ isset($key) ? $key . '-' : '' }}button-{{ $action }}" @unless (isset($button['parameters']) && str_contains($button['parameters'], 'data-ajax')) data-target=".modal" data-toggle="modal" @endunless data-action="{{ $button['url'] }}" @isset($key) data-table="{{ $key }}" @endisset class="btn {{ $overview ? '' : 'fa-left' }} {{ $button['class'] }}" {!! $button['parameters'] ?? null !!} {!! $button['query'] ?? null !!} @if ($button['method'])data-method="{{ $button['method'] }}"@endif @if ($overview) data-overview @endif>
                @if ($button['icon'])<i class="fas fa-{{ $button['icon'] }}"></i>@endif
                {{ $overview ? '' : $button['name'] }}
            </button>
        @endif
    @endforeach
</div>
