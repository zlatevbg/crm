<nav class="navbar-header navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="{{ Helper::route('dashboard') }}">
        {{-- <img src="{{ Helper::autover('/img/' . Domain::current() . '/logo-nav.png') }}" alt=""> --}}
        <svg class="logo-small">
            <use xlink:href="#logo"></use>
        </svg>
    </a>

    <button class="navbar-toggler navbar-toggler-right collapsed" type="button" data-toggle="collapse" data-target="#headerNav" aria-controls="headerNav" aria-expanded="false" aria-label="@lang('buttons.toggleNavigation')">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="headerNav">
        @isset($projects)
            @if (count($projects) > 1)
                <div>
                    <label for="input-project" class="sr-only">@lang('labels.project')</label>
                    <select id="input-project" class="form-control" name="project">
                        @foreach ($projects as $key => $value)
                            <option value="{{ $key }}" {!! session('project') == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endisset

        <ul class="navbar-nav ml-auto">
            {{-- <li class="nav-item{{ Request::segment(1) === 'files' ? ' active' : '' }}"><a class="nav-link" href="{{ secure_url('files') }}"><i class="fas fa-cloud fa-lg m-auto fa-fw"></i>Files</a></li> --}}
            @can('View: Projects')<li class="nav-item{{ Request::segment(1) === 'projects' ? ' active' : '' }}"><a class="nav-link" href="{{ secure_url('projects') }}"><i class="fas fa-home fa-lg m-auto fa-fw"></i>Projects</a></li>@endcan
            @can('View: Apartments')<li class="nav-item{{ Request::segment(1) === 'apartments' ? ' active' : '' }}"><a class="nav-link" href="{{ secure_url('apartments') }}"><i class="fas fa-building fa-lg m-auto fa-fw"></i>Apartments</a></li>@endcan
            @can('View: Viewings')<li class="nav-item{{ Request::segment(1) === 'viewings' ? ' active' : '' }}"><a class="nav-link" href="{{ secure_url('viewings') }}"><i class="fas fa-eye fa-lg m-auto fa-fw"></i>Viewings</a></li>@endcan
            @can('View: Sales')<li class="nav-item{{ Request::segment(1) === 'sales' ? ' active' : '' }}"><a class="nav-link" href="{{ secure_url('sales') }}"><i class="fas fa-euro-sign fa-lg m-auto fa-fw"></i>Sales</a></li>@endcan

            @can('View: Users')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ in_array(Request::segment(1), ['agents', 'clients', 'investors', 'users', 'gvcontacts', 'rental-contacts', 'subscribers', 'leads']) ? ' active' : '' }}" href="#" id="navbarDropdownUsers" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-lg m-auto fa-fw"></i>Users</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUsers">
                        @can('View: Admins')<a class="fa-left dropdown-item{{ Request::segment(1) === 'users' ? ' active' : '' }}" href="{{ secure_url('users') }}"><i class="fas fa-users-cog fa-fw"></i>Admins</a>@endcan
                        @can('View: Agents')<a class="fa-left dropdown-item{{ Request::segment(1) === 'agents' ? ' active' : '' }}" href="{{ secure_url('agents') }}"><i class="fas fa-id-badge fa-fw"></i>Agents</a>@endcan
                        @can('View: Clients')<a class="fa-left dropdown-item{{ Request::segment(1) === 'clients' ? ' active' : '' }}" href="{{ secure_url('clients') }}"><i class="fas fa-users fa-fw"></i>Clients</a>@endcan
                        @can('View: Investors')<a class="fa-left dropdown-item{{ Request::segment(1) === 'investors' ? ' active' : '' }}" href="{{ secure_url('investors') }}"><i class="fas fa-user-tie fa-fw"></i>Investors</a>@endcan
                        @can('View: GVContacts')<a class="fa-left dropdown-item{{ Request::segment(1) === 'gvcontacts' ? ' active' : '' }}" href="{{ secure_url('gvcontacts') }}"><i class="fas fa-star fa-fw"></i>Golden Visa Contacts</a>@endcan
                        @can('View: Rental Contacts')<a class="fa-left dropdown-item{{ Request::segment(1) === 'rental-contacts' ? ' active' : '' }}" href="{{ secure_url('rental-contacts') }}"><i class="fas fa-bed fa-fw"></i>Rental Contacts</a>@endcan
                        @can('View All Subscribers')<a class="fa-left dropdown-item{{ Request::segment(1) === 'subscribers' ? ' active' : '' }}" href="{{ secure_url('subscribers') }}"><i class="fas fa-user-check fa-fw"></i>Subscribers</a>@endcan
                        @can('View: Leads')<a class="fa-left dropdown-item{{ Request::segment(1) === 'leads' ? ' active' : '' }}" href="{{ secure_url('leads') }}"><i class="fas fa-address-card fa-fw"></i>Leads</a>@endcan
                    </div>
                </li>
            @endcan

            @can('View: Office')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ in_array(Request::segment(1), ['bookings', 'contacts', 'guests', 'newsletters', 'sms', 'tasks', 'websites']) ? ' active' : '' }}" href="#" id="navbarDropdownOffice" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-desktop fa-lg m-auto fa-fw"></i>Office</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownOffice">
                        @can('View: Bookings')<a class="fa-left dropdown-item{{ Request::segment(1) === 'bookings' ? ' active' : '' }}" href="{{ secure_url('bookings') }}"><i class="fas fa-credit-card fa-fw"></i>Bookings</a>@endcan
                        @can('View: Contacts')<a class="fa-left dropdown-item{{ Request::segment(1) === 'contacts' ? ' active' : '' }}" href="{{ secure_url('contacts') }}"><i class="far fa-address-card fa-fw"></i>Contacts</a>@endcan
                        @can('View: Guests')<a class="fa-left dropdown-item{{ Request::segment(1) === 'guests' ? ' active' : '' }}" href="{{ secure_url('guests') }}"><i class="fas fa-users fa-fw"></i>Guests</a>@endcan
                        @can('View: Newsletters')<a class="fa-left dropdown-item{{ Request::segment(1) === 'newsletters' ? ' active' : '' }}" href="{{ secure_url('newsletters') }}"><i class="fas fa-envelope fa-fw"></i>Newsletters</a>@endcan
                        @can('View: SMS')<a class="fa-left dropdown-item{{ Request::segment(1) === 'sms' ? ' active' : '' }}" href="{{ secure_url('sms') }}"><i class="fas fa-sms fa-fw"></i>SMS</a>@endcan
                        @can('View: Tasks')<a class="fa-left dropdown-item{{ Request::segment(1) === 'tasks' ? ' active' : '' }}" href="{{ secure_url('tasks') }}"><i class="fas fa-tasks fa-fw"></i>Tasks</a>@endcan
                        @can('View: Websites')<a class="fa-left dropdown-item{{ Request::segment(1) === 'websites' ? ' active' : '' }}" href="{{ secure_url('websites') }}"><i class="fab fa-internet-explorer fa-fw"></i>Websites</a>@endcan
                    </div>
                </li>
            @endcan

            @can('View: Reports')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ (Request::segment(1) == 'reports' && (!Request::segment(2) || in_array(Request::segment(2), ['dashboard', 'agent-commissions', 'apartments', 'cancellations', 'clients', 'closing-dates', 'conversion-rate', 'discount', 'investors', 'subagent-commissions', 'sales', 'targets', 'tasks', 'viewings']))) ? ' active' : '' }}" href="#" id="navbarDropdownReports" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-chart-line fa-lg m-auto fa-fw"></i>Reports</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownReports">
                        @can('View: Reports Dashboard')<a class="fa-left dropdown-item{{ Request::segment(1) == 'reports' && (!Request::segment(2) || Request::segment(2) === 'dashboard') ? ' active' : '' }}" href="{{ secure_url('reports') }}"><i class="fas fa-chart-line fa-fw"></i>Dashboard</a>@endcan
                        @can('View: Agent Commissions Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'agent-commissions' ? ' active' : '' }}" href="{{ secure_url('reports/agent-commissions') }}"><i class="fas fa-chart-line fa-fw"></i>Agent Commissions</a>@endcan
                        @can('View: Apartments Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'apartments' ? ' active' : '' }}" href="{{ secure_url('reports/apartments') }}"><i class="fas fa-chart-line fa-fw"></i>Apartments</a>@endcan
                        @can('View: Cancellations Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'cancellations' ? ' active' : '' }}" href="{{ secure_url('reports/cancellations') }}"><i class="fas fa-chart-line fa-fw"></i>Cancellations</a>@endcan
                        @can('View: Clients Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'clients' ? ' active' : '' }}" href="{{ secure_url('reports/clients') }}"><i class="fas fa-chart-line fa-fw"></i>Clients</a>@endcan
                        @can('View: Closing Dates Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'closing-dates' ? ' active' : '' }}" href="{{ secure_url('reports/closing-dates') }}"><i class="fas fa-chart-line fa-fw"></i>Closing Dates</a>@endcan
                        @can('View: Conversion Rate Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'conversion-rate' ? ' active' : '' }}" href="{{ secure_url('reports/conversion-rate') }}"><i class="fas fa-chart-line fa-fw"></i>Conversion Rate</a>@endcan
                        @can('View: Discount Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'discount' ? ' active' : '' }}" href="{{ secure_url('reports/discount') }}"><i class="fas fa-chart-line fa-fw"></i>Discount</a>@endcan
                        @can('View: Investors Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'investors' ? ' active' : '' }}" href="{{ secure_url('reports/investors') }}"><i class="fas fa-chart-line fa-fw"></i>Investors</a>@endcan
                        @can('View: Sub-Agent Commissions Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'subagent-commissions' ? ' active' : '' }}" href="{{ secure_url('reports/subagent-commissions') }}"><i class="fas fa-chart-line fa-fw"></i>Sub-Agent Commissions</a>@endcan
                        @can('View: Sales Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'sales' ? ' active' : '' }}" href="{{ secure_url('reports/sales') }}"><i class="fas fa-chart-line fa-fw"></i>Sales</a>@endcan
                        @can('View: Targets Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'targets' ? ' active' : '' }}" href="{{ secure_url('reports/targets') }}"><i class="fas fa-chart-line fa-fw"></i>Targets</a>@endcan
                        @can('View: Tasks Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'tasks' ? ' active' : '' }}" href="{{ secure_url('reports/tasks') }}"><i class="fas fa-chart-line fa-fw"></i>Tasks</a>@endcan
                        @can('View: Viewings Report')<a class="fa-left dropdown-item{{ Request::segment(2) === 'viewings' ? ' active' : '' }}" href="{{ secure_url('reports/viewings') }}"><i class="fas fa-chart-line fa-fw"></i>Viewings</a>@endcan
                    </div>
                </li>
            @endcan

            @can('View: Settings')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ in_array(Request::segment(1), ['activities', 'categories', 'countries', 'departments', 'domains', 'features', 'fund-size', 'investment-range', 'payment-methods', 'permissions', 'roles', 'sources', 'statuses', 'tags']) ? ' active' : '' }}" href="#" id="navbarDropdownSettings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-cog fa-lg m-auto fa-fw"></i>Settings</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownSettings">
                        @can('View: Activities')<a class="fa-left dropdown-item{{ Request::segment(1) === 'activities' ? ' active' : '' }}" href="{{ secure_url('activities') }}"><i class="far fa-calendar-alt fa-fw"></i>Activities</a>@endcan
                        @can('View: Categories')<a class="fa-left dropdown-item{{ Request::segment(1) === 'categories' ? ' active' : '' }}" href="{{ secure_url('categories') }}"><i class="fas fa-sitemap fa-fw"></i>Categories</a>@endcan
                        @can('View: Countries')<a class="fa-left dropdown-item{{ Request::segment(1) === 'countries' ? ' active' : '' }}" href="{{ secure_url('countries') }}"><i class="far fa-compass fa-fw"></i>Countries</a>@endcan
                        @can('View: Departments')<a class="fa-left dropdown-item{{ Request::segment(1) === 'departments' ? ' active' : '' }}" href="{{ secure_url('departments') }}"><i class="fas fa-building fa-fw"></i>Departments</a>@endcan
                        @can('View: Domains')<a class="fa-left dropdown-item{{ Request::segment(1) === 'domains' ? ' active' : '' }}" href="{{ secure_url('domains') }}"><i class="fas fa-globe fa-fw"></i>Domains</a>@endcan
                        @can('View: Fund Size')<a class="fa-left dropdown-item{{ Request::segment(1) === 'fund-size' ? ' active' : '' }}" href="{{ secure_url('fund-size') }}"><i class="far fa-money-bill-alt fa-fw"></i>Fund Size</a>@endcan
                        @can('View: Investment Range')<a class="fa-left dropdown-item{{ Request::segment(1) === 'investment-range' ? ' active' : '' }}" href="{{ secure_url('investment-range') }}"><i class="fas fa-donate fa-fw"></i>Investment Range</a>@endcan
                        @can('View: Payment Methods')<a class="fa-left dropdown-item{{ Request::segment(1) === 'payment-methods' ? ' active' : '' }}" href="{{ secure_url('payment-methods') }}"><i class="far fa-credit-card fa-fw"></i>Payment Methods</a>@endcan
                        @can('View: Permissions')<a class="fa-left dropdown-item{{ Request::segment(1) === 'permissions' ? ' active' : '' }}" href="{{ secure_url('permissions') }}"><i class="fas fa-lock fa-fw"></i>Permissions</a>@endcan
                        @can('View: Project Features')<a class="fa-left dropdown-item{{ Request::segment(1) === 'features' ? ' active' : '' }}" href="{{ secure_url('features') }}"><i class="fas fa-check-square fa-fw"></i>Project Features</a>@endcan
                        @can('View: Roles')<a class="fa-left dropdown-item{{ Request::segment(1) === 'roles' ? ' active' : '' }}" href="{{ secure_url('roles') }}"><i class="far fa-id-badge fa-fw"></i>Roles</a>@endcan
                        @can('View: Sources')<a class="fa-left dropdown-item{{ Request::segment(1) === 'sources' ? ' active' : '' }}" href="{{ secure_url('sources') }}"><i class="far fa-dot-circle fa-fw"></i>Sources</a>@endcan
                        @can('View: Statuses')<a class="fa-left dropdown-item{{ Request::segment(1) === 'statuses' ? ' active' : '' }}" href="{{ secure_url('statuses') }}"><i class="far fa-lightbulb fa-fw"></i>Statuses</a>@endcan
                        @can('View: Tags')<a class="fa-left dropdown-item{{ Request::segment(1) === 'tags' ? ' active' : '' }}" href="{{ secure_url('tags') }}"><i class="fas fa-tags fa-fw"></i>Tags</a>@endcan
                    </div>
                </li>
            @endcan

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{{ in_array(Request::segment(1), ['profile']) ? ' active' : '' }}" href="#" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-{{ Auth::user() && in_array(Auth::user()->gender, ['male', 'female']) ? Auth::user()->gender : 'user' }} fa-lg m-auto fa-fw"></i>{{ Auth::user() ? Auth::user()->name : null }}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                    @can('View: Profile')<a class="fa-left dropdown-item{{ Request::segment(1) === 'profile' ? ' active' : '' }}" href="{{ secure_url('profile') }}"><i class="fas fa-user fa-fw"></i>Profile</a>@endcan
                    <a class="fa-left dropdown-item" data-ajax href="{{ Helper::route('logout') }}"><i class="fas fa-sign-out-alt fa-fw"></i>@lang('buttons.logout')</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
