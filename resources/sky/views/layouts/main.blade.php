<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>{{ $title ?? $api->title() }}</title>
    <meta name="description" content="{{ $description ?? $api->meta->description }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#2b5797">
    <meta name="apple-mobile-web-app-title" content="{{ env('APP_NAME') }}">
    <meta name="application-name" content="{{ env('APP_NAME') }}">
    <meta name="theme-color" content="#ffffff">

    <link href="{{ Helper::autover('/css/' . Domain::current() . '/main.css') }}" rel="stylesheet">
    @if (session()->has('project'))
        @isset($api)
            @if ($api->datatables || $api->tabs || $api->tabsOverview)
                <link href="{{ Helper::autover('/css/' . Domain::current() . '/components/datatables.css') }}" rel="stylesheet">
            @endif

            @if (in_array($api->meta->model, ['image', 'text_section', 'newsletter', 'website', 'article', 'post', 'event', 'gallery']))
                <link href="{{ Helper::autover('/css/' . Domain::current() . '/vendor/photoswipe.css') }}" rel="stylesheet">
            @endif
        @endisset
    @endif
    @stack('styles')

    <script defer src="{{ Helper::autover('/js/' . Domain::current() . '/vendor/fontawesome.js') }}"></script>
</head>
<body>
    <div class="sr-only">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="0" height="0">
            <symbol xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 25.96" id="logo">
                <g>
                    <path d="M34.06,18.64H35a1.5,1.5,0,0,1,.59.1,1.32,1.32,0,0,1,.46.29,1.06,1.06,0,0,1,.29.43,1.23,1.23,0,0,1,.11.53h0a1.41,1.41,0,0,1-.11.54,1.17,1.17,0,0,1-.29.43,1.32,1.32,0,0,1-.46.29,1.71,1.71,0,0,1-.59.11h-.94ZM35,21.07a1.26,1.26,0,0,0,.47-.08,1,1,0,0,0,.6-.56,1,1,0,0,0,.08-.42h0a1.06,1.06,0,0,0-.08-.43,1,1,0,0,0-.58-.58,1.17,1.17,0,0,0-.49-.07h-.64v2.14Z" fill="#ffffff"/>
                    <path d="M37.28,18.64h2v.29H37.59v.94h1.49v.28H37.59v1h1.7v.27h-2Z" fill="#ffffff"/>
                    <path d="M39.83,18.64h.36l1,2.35,1-2.35h.33l-1.17,2.74H41Z" fill="#ffffff"/>
                    <path d="M43.16,18.64h2v.29H43.46v.94H45v.28h-1.5v1h1.69v.27h-2Z" fill="#ffffff"/>
                    <path d="M45.94,18.64h.31v2.43h1.54v.29H45.94Z" fill="#ffffff"/>
                    <path d="M49.8,21.43a1.36,1.36,0,0,1-1.29-.87,1.4,1.4,0,0,1-.1-.54h0a1.39,1.39,0,0,1,.1-.53,1.34,1.34,0,0,1,.72-.76,1.42,1.42,0,0,1,.57-.12,1.39,1.39,0,0,1,.57.12,1.23,1.23,0,0,1,.44.3,1.45,1.45,0,0,1,.38,1h0a1.44,1.44,0,0,1-.1.54,1.3,1.3,0,0,1-.72.75A1.39,1.39,0,0,1,49.8,21.43Zm0-.29a1.09,1.09,0,0,0,.43-.08,1,1,0,0,0,.33-.24,1.38,1.38,0,0,0,.23-.36,1.1,1.1,0,0,0,.07-.44h0a1.05,1.05,0,0,0-.07-.43,1,1,0,0,0-.23-.36A1,1,0,0,0,50.2,19a1,1,0,0,0-.43-.09.84.84,0,0,0-.43.09.94.94,0,0,0-.33.23,1.26,1.26,0,0,0-.31.8h0a1.26,1.26,0,0,0,.31.8,1.13,1.13,0,0,0,.36.24,1.29,1.29,0,0,0,.43.07Z" fill="#ffffff"/>
                    <path d="M52,18.64h1a1,1,0,0,1,.42.06.9.9,0,0,1,.32.17.71.71,0,0,1,.21.27.86.86,0,0,1,.07.36h0a.78.78,0,0,1-.32.66,1.11,1.11,0,0,1-.34.18,1.92,1.92,0,0,1-.41.05h-.67v1H52Zm1,1.48a.83.83,0,0,0,.31,0,.77.77,0,0,0,.24-.12.53.53,0,0,0,.2-.44h0a.5.5,0,0,0-.2-.44.87.87,0,0,0-.54-.15h-.69v1.2Z" fill="#ffffff"/>
                    <path d="M54.79,18.64h.31l1,1.48,1-1.48h.31v2.72h-.3V19.15l-1,1.47h0l-1-1.46v2.2h-.3Z" fill="#ffffff"/>
                    <path d="M58.35,18.64h2v.29H58.66v.94h1.49v.28H58.66v1h1.7v.27h-2Z" fill="#ffffff"/>
                    <path d="M61.13,18.64h.3l1.72,2.18V18.64h.3v2.72h-.24l-1.78-2.24v2.24h-.3Z" fill="#ffffff"/>
                    <path d="M65.11,18.93h-.92v-.28h2.14v.28h-.92v2.44h-.3Z" fill="#ffffff"/>
                    <path d="M70,21a1.35,1.35,0,0,1-.4.31,1.09,1.09,0,0,1-.83.06.74.74,0,0,1-.27-.16.61.61,0,0,1-.18-.24.67.67,0,0,1-.07-.31h0a.69.69,0,0,1,.18-.47,1.31,1.31,0,0,1,.48-.32,1.21,1.21,0,0,1-.2-.3.63.63,0,0,1-.07-.3h0a.7.7,0,0,1,.05-.25.8.8,0,0,1,.15-.21.74.74,0,0,1,.22-.14,1,1,0,0,1,.3,0,.81.81,0,0,1,.27,0,.74.74,0,0,1,.21.13,1,1,0,0,1,.14.2.64.64,0,0,1,0,.24h0a.58.58,0,0,1-.18.43,1.18,1.18,0,0,1-.47.28l.62.63.18-.28a2.3,2.3,0,0,0,.15-.32l.26.11c-.06.12-.12.24-.19.36a2.2,2.2,0,0,1-.21.32l.48.49-.23.17Zm-.87.16a.74.74,0,0,0,.36-.09,1.35,1.35,0,0,0,.32-.26L69.05,20a.82.82,0,0,0-.39.25.51.51,0,0,0-.13.33h0a.45.45,0,0,0,0,.2.55.55,0,0,0,.3.28.85.85,0,0,0,.22,0Zm.08-1.39a1.07,1.07,0,0,0,.39-.22.41.41,0,0,0,.14-.3h0a.39.39,0,0,0-.11-.27.41.41,0,0,0-.28-.11A.46.46,0,0,0,69,19a.39.39,0,0,0-.11.29h0a.2.2,0,0,0,0,.11.56.56,0,0,0,0,.12l.08.12A.71.71,0,0,1,69.17,19.75Z" fill="#ffffff"/>
                    <path d="M72.68,18.64H73v2.72h-.31Z" fill="#ffffff"/>
                    <path d="M74,18.64h.29L76,20.82V18.64h.3v2.72H76l-1.76-2.24v2.24H74Z" fill="#ffffff"/>
                    <path d="M77,18.64h.36l1,2.35,1-2.35h.33l-1.18,2.74h-.27Z" fill="#ffffff"/>
                    <path d="M80.31,18.64h2v.29H80.62v.94h1.49v.28H80.62v1H82.3v.27h-2Z" fill="#ffffff"/>
                    <path d="M84,21.4a1.52,1.52,0,0,1-.6-.1,1.65,1.65,0,0,1-.52-.33l.19-.23a1.55,1.55,0,0,0,.43.29,1.25,1.25,0,0,0,.51.1.78.78,0,0,0,.44-.13.44.44,0,0,0,.16-.34h0a.55.55,0,0,0,0-.17.36.36,0,0,0-.11-.14.84.84,0,0,0-.21-.11l-.36-.1a1.48,1.48,0,0,1-.41-.12.89.89,0,0,1-.29-.15.57.57,0,0,1-.16-.22.77.77,0,0,1-.06-.3h0a.59.59,0,0,1,.07-.29.72.72,0,0,1,.19-.24.69.69,0,0,1,.28-.15,1,1,0,0,1,.35-.06,1.89,1.89,0,0,1,.53.08,1.45,1.45,0,0,1,.43.26l-.18.23a1.3,1.3,0,0,0-.38-.22,1.12,1.12,0,0,0-.4-.07.64.64,0,0,0-.43.12.37.37,0,0,0-.15.32h0a.32.32,0,0,0,0,.17.26.26,0,0,0,.11.14.84.84,0,0,0,.23.12l.35.1a1.64,1.64,0,0,1,.68.28.6.6,0,0,1,.21.49h0a.71.71,0,0,1-.07.32.75.75,0,0,1-.19.24.92.92,0,0,1-.29.16A1.48,1.48,0,0,1,84,21.4Z" fill="#ffffff"/>
                    <path d="M86.48,18.93h-.91v-.28h2.14v.28h-.92v2.44h-.31Z" fill="#ffffff"/>
                    <path d="M88.46,18.64h.31l1,1.48,1-1.48h.31v2.72h-.31V19.15l-1,1.47h0l-1-1.46v2.2h-.3Z" fill="#ffffff"/>
                    <path d="M92,18.64h2v.29H92.32v.94h1.49v.28H92.32v1H94v.27H92Z" fill="#ffffff"/>
                    <path d="M94.8,18.64h.29l1.72,2.18V18.64h.3v2.72h-.25L95.1,19.12v2.24h-.3Z" fill="#ffffff"/>
                    <path d="M98.78,18.93h-.92v-.28H100v.28h-.91v2.44h-.31Z" fill="#ffffff"/>
                </g>
                <path d="M50.71,15.19h6.23V13.81H52.09V11.27H56.9V10H52.1V7.27H57V5.9H50.71Z" fill="#ffffff"/>
                <path d="M85.65,15.16h1.43V6H85.65Z" fill="#ffffff"/>
                <path d="M94,15.22h5.46V13.9h-4V6.07H94Z" fill="#ffffff"/>
                <path d="M74.2,15.21h1.38V12.32h2l.16,0,.12,0a3.28,3.28,0,0,0,2.76-3.11V8.93h0v0a3.34,3.34,0,0,0-3.39-3h-3Zm3-4.14H75.58V7.14h1.6a2,2,0,0,1,2.11,2h0a1.94,1.94,0,0,1-.58,1.36l-.06.06a2,2,0,0,1-1.17.54l-.15,0h-.15Z" fill="#ffffff"/>
                <path d="M43.32,15.09h1.36V5.85h-.6L39.32,12.5l0-.06L34.55,5.85H34v9.24H35.4V9.49L39.33,15l4-5.51Z" fill="#ffffff"/>
                <path d="M66.72,9.91a9,9,0,0,0-1-.3,5.82,5.82,0,0,1-1.34-.43,1.57,1.57,0,0,1-.46-.39.9.9,0,0,1-.12-.48,1.08,1.08,0,0,1,.46-.92A2,2,0,0,1,65.53,7a1.83,1.83,0,0,1,1.15.34,1.23,1.23,0,0,1,.35.49l1.38-.56a2.18,2.18,0,0,0-.68-.85,3.45,3.45,0,0,0-2.22-.66,3.82,3.82,0,0,0-2.37.72,2.22,2.22,0,0,0-1,1.87,1.87,1.87,0,0,0,.63,1.47,4.69,4.69,0,0,0,1.94.88l.28.07c1,.26,2.29.75,2.29,1.6a1.34,1.34,0,0,1-.52,1.13,2.23,2.23,0,0,1-1.45.44A2.06,2.06,0,0,1,64,13.53a1.45,1.45,0,0,1-.45-.67l-1.41.57a2.56,2.56,0,0,0,.76,1.05,3.7,3.7,0,0,0,2.44.78,3.88,3.88,0,0,0,2.59-.82,2.55,2.55,0,0,0,1-2.17,2.42,2.42,0,0,0-.63-1.46,4.2,4.2,0,0,0-1.54-.9" fill="#ffffff"/>
                <polygon points="5.89 11.21 12.89 6.43 18.69 10.4 19.9 9.57 12.89 4.78 0 13.59 0.03 13.6 0 13.62 2.27 15.18 3.48 14.35 2.39 13.6 4.71 12.03 7.02 13.6 8.2 12.8 5.89 11.21" fill="#c59c45"/>
                <polyline points="16.39 13.6 14.71 14.75 14.71 21.15 14.71 21.14 14.71 25.96 16.05 25.05 16.05 23.43 16.05 20.26 21.09 16.82 22.27 16.01 25.77 13.62 25.75 13.6 25.77 13.59 22.27 11.2 25.77 8.81 12.89 0 0 8.81 2.3 10.38 3.49 9.57 2.37 8.81 12.89 1.62 23.4 8.81 17.57 12.8 18.75 13.6 21.06 12.03 23.38 13.6 16.05 18.61 16.05 15.45 17.57 14.41" fill="#c59c45"/>
                <polyline points="3.5 16.01 4.68 16.82 9.71 20.26 9.73 23.43 9.73 25.05 11.07 25.96 11.05 21.14 11.06 21.14 11.03 14.73 9.39 13.6 8.2 14.41 9.7 15.44 9.71 18.61 5.89 15.99 4.71 15.18" fill="#c59c45"/>
                <path d="M16.05,25.05l9.72-6.65L22.28,16l-1.19.81L23.4,18.4l-7.35,5ZM9.73,23.43l-7.36-5,2.31-1.58L3.5,16,0,18.4l9.73,6.65ZM5.89,16l2.32-1.58L9.4,13.6l3.5-2.39,5.8,4,1.18-.82-7-4.78L8.21,12.8,7,13.6l-2.3,1.58Z" fill="#ffffff"/>
            </symbol>
        </svg>
    </div>

    <header id="header">
        @include('partials/nav')
    </header>

    <main id="main" class="p-3">
        @include('partials/errors')
        @include('partials/success')

        @if (session()->has('project'))
            @if (isset($api))
                @if ($api->tabs)
                    <nav class="mb-3">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            @foreach ($api->tabs as $id => $tab)
                                @if (!isset($tab['hidden']))
                                    <a class="nav-item nav-link {{ isset($tab['class']) ? $tab['class'] : '' }} {{ (($api->slug == $api->parentSlug && !$tab['slug']) || $api->slug == $tab['slug']) ? 'active' : '' }}" href="{{ url($api->model->library ? str_replace_last('/' . $api->slug . ($api->id ? '/' . $api->id : ''), '', $api->path) : $api->parentSlug . ($api->parentId ? '/' . $api->parentId : '')) . ($tab['slug'] ? '/' . $tab['slug'] : '') }}">{{ $tab['name'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </nav>
                    @if ($api->datatables)
                        @include('partials/datatables', ['datatables' => $api->datatables])
                    @else
                        @if ($api->breadcrumbs || $api->actions)
                            <div class="datatable-sticky-wrapper">
                                <div class="datatable-sticky-header">
                                    <div class="datatable-header">
                                        @isset($api->breadcrumbs)
                                            @include('partials/breadcrumbs', ['breadcrumbs' => $api->breadcrumbs()])
                                        @endisset
                                        @isset($api->actions)
                                            @include('partials/buttons', ['buttons' => $api->actions])
                                        @endisset
                                    </div>
                                </div>
                            </div>
                        @endif
                        @yield('home')
                    @endif
                @elseif ($api->datatables)
                    @include('partials/datatables', ['datatables' => $api->datatables])
                @endif
            @else
                @yield('content')
            @endif
        @endif

        <div class="modal fade" data-backdrop="static" data-focus="false" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h3 id="modalTitle" class="modal-title"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="@lang('buttons.close')">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('buttons.cancel')</button>
              </div>
            </div>
          </div>
        </div>
    </main>

    <footer>

    </footer>

    <div class="qq-upload-drop-area">@lang('fine-uploader.dropzone')</div>

    <script src="{{ Helper::autover('/js/vendor/loadjs.js') }}"></script>
    <script>
      var CKEDITOR_BASEPATH = '/js/{{ Domain::current() }}/vendor/ckeditor/';
      var unikatSettings = {
        url: '{{ env('APP_URL') }}',
        datatablesSearch: '{{ Request::input('search') }}',
        alertErrorMessage: "@lang('text.alertError')",
        text: {
          close: "@lang('buttons.close')",
        },

        @isset($api)
            @foreach ([[(count($api->datatables) || count($api->tabs) || count($api->tabsOverview)) => 'datatables'], [in_array($api->meta->model, ['library', 'image', 'project', 'new_project', 'apartment', 'client', 'agent', 'newsletter', 'text_section', 'background_section', 'attachment', 'task', 'article', 'post', 'event', 'gallery', 'website']) => 'fine-uploader'], [in_array($api->meta->model, ['apartment', 'agent', 'booking', 'client', 'guest', 'lead', 'sale', 'contract', 'payment', 'report', 'target', 'viewing', 'new_project', 'task', 'website', 'article', 'post', 'event', 'investor']) => 'datepicker'], [true => 'multiselect'], [in_array($api->meta->model, ['image', 'text_section', 'newsletter', 'website', 'article', 'post', 'event', 'gallery']) => 'photoswipe']] as $plugins)
                @foreach ($plugins as $required => $plugin)
                    @if ($required)
                    {{ camel_case($plugin) }}: {
                        @if ($plugin === 'fine-uploader')
                            imageExtensions: @json(config('upload.imageExtensions')),
                            fileExtensions: @json(config('upload.fileExtensions')),
                        @endif

                        @foreach (trans($plugin) as $key => $value)
                            @if (is_array($value) && !is_numeric(key($value)))
                            {{ $key }}: {
                                @foreach ($value as $k => $v)
                                    @if (is_array($v) && !is_numeric(key($v)))
                                    {{ $k }}: {
                                        @foreach ($v as $sk => $sv)
                                            {{ $sk }}: @json($sv),
                                        @endforeach
                                    },
                                    @else
                                    {{ $k }}: @json($v),
                                    @endif
                                @endforeach
                            },
                            @else
                            {{ $key }}: @json($value),
                            @endif
                        @endforeach
                    },
                    @endif
                @endforeach
            @endforeach
        @endisset
      }

      loadjs(
        [
          '{{ Helper::autover('/js/vendor/babel-polyfill.js') }}',
          '{{ Helper::autover('/js/vendor/jquery.js') }}',
          '{{ Helper::autover('/js/vendor/popper.js') }}',
          '{{ Helper::autover('/js/vendor/bootstrap.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/main.js') }}',
          '{{ Helper::autover('/js/vendor/ajaxq.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/components/tweezer.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/ajax.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/vendor/jquery-ui.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/components/multiselect.js') }}',
        ],
        'main',
        {
          success: function() {
            var project = document.querySelector('#input-project');
            if (project) {
                var currentProject = '{{ session('project') }}';
                $(project).multiselect({
                    multiple: false,
                    footer: false,
                    classes: 'text-nowrap w-auto',
                    close: function() {
                        /*var qs = /([&\?]project=)\d+/;
                        if (!document.location.search.match(qs)) {
                            var separator = document.location.search.indexOf('?') === -1 ? '?' : '&'
                            document.location.href += separator + 'project=' + this.value;
                        } else {
                            document.location.href = document.location.href.replace(qs, '\$1' + this.value);
                        }*/

                        if (currentProject != this.value) {
                            ajax.ajaxify({
                                obj: this,
                                method: 'post',
                                queue: 'sync',
                                action: '{{ Helper::route('api.change-project', [], false) }}/' + this.value,
                                skipErrors: true,
                            }).then(function (data) {
                            }).catch(function (error) {
                            });
                        }
                    }
                });

                if (currentProject != project.value) {
                    project.value = currentProject;
                    $(project).multiselect('refresh');
                }
            }
          },
          async: false,
        }
      );
    </script>

    @if (session()->has('project') && isset($api) && in_array($api->meta->model, ['library', 'image', 'project', 'new_project', 'apartment', 'client', 'agent', 'newsletter', 'text_section', 'background_section', 'attachment', 'task', 'article', 'post', 'event', 'gallery', 'website']))
    <script>
      loadjs(
        [
          '{{ Helper::autover('/js/' . Domain::current() . '/vendor/fine-uploader.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/fine-uploader.js') }}',
        ],
        {
          success: function() {
            if (typeof uploader !== 'undefined') {
                uploader.init();
              }
          },
          async: false,
        }
      );
    </script>
    @endif

    @if (session()->has('project') && isset($api) && (count($api->datatables) || count($api->tabs) || count($api->tabsOverview)))
    <script>
      loadjs(
        [
          '{{ Helper::autover('/js/' . Domain::current() . '/vendor/datatables.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/vendor/waypoints.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/datatables.js') }}',
        ],
        {
          success: function() {
            datatable.setup(@json($api->datatables));

            @isset($api->tabsOverview)
                @foreach ($api->tabsOverview as $key => $tab)
                    datatable.setup(@json($tab['datatables-overview']));
                @endforeach
            @endisset
          },
          async: false,
        }
      );
    </script>
    @endif

    @if (session()->has('project') && isset($api) && in_array($api->meta->model, ['image', 'text_section', 'newsletter', 'website', 'article', 'post', 'event', 'gallery']))
    <script>
      loadjs(
        [
          '{{ Helper::autover('/js/vendor/photoswipe.js') }}',
          '{{ Helper::autover('/js/' . Domain::current() . '/components/photoswipe.js') }}',
        ],
        {
          success: function() {
            photoswipe.setup('.photoswipe-wrapper')
          },
          async: false,
        }
      );
    </script>
    @endif

    @stack('scripts')
</body>
</html>
