<!DOCTYPE html>

@php
    $ownerApp = config("app.owner_app");
    $user     = Auth::user();
    $company  = $user->company;
    $role     = $user->role;
    $sections = Cache::get("active_sections_{$company->id}");
    $preferences = $user->formatted_preferences;

    // Cache data
    $hasActiveSections  = Cache::get("has_active_sections_{$company->id}");
    $lastActiveSections = Cache::get("last_active_sections_{$company->id}");
@endphp

<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact br-html-brand"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../System/assets/"
    data-template="vertical-menu-template-starter">
    <head>
        @include("System.layouts.partials.up")
        <script>
            window.ownerApp = @json($ownerApp ?? null);
            window.user     = @json($user ?? null);
            window.company  = @json($company ?? null);
            window.sections = @json($sections ?? []);
            window.preferences = @json($preferences ?? []);
        </script>
    </head>
    <body class="br-layout">
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme br-menu-brand">
                    <ul class="menu-inner mb-3 mt-2">
                        <li class="menu-header br-sidebar-profile-wrap text-center">
                            <div class="br-sidebar-profile">
                                <div class="br-sidebar-profile-avatar mx-auto mb-3">
                                    <div class="avatar avatar-lg bg-white rounded-circle overflow-hidden br-brand-avatar">
                                        <img src="{{ asset($company->logomark ? 'storage/'.$company->logomark : $ownerApp->assets->img->logomark) }}" class="w-100 h-100 object-fit-cover" alt="Logo de {{ $company->commercial_name }}"/>
                                    </div>
                                </div>
                                <p class="br-sidebar-profile-company mb-1" title="{{ $company->commercial_name }}">
                                    {{ Str::limit($company->commercial_name, 22) }}
                                </p>
                                <p class="br-sidebar-profile-user mb-1" title="{{ $user->name }}">
                                    {{ Str::limit($user->name, 24) }}
                                </p>
                                <div class="br-sidebar-profile-role-head mb-1">
                                    <p class="br-sidebar-profile-role br-sidebar-profile-role--accent mb-0" title="{{ $role->name }}">
                                        <span class="br-sidebar-profile-role-name text-uppercase">{{ Str::limit($role->name, 22) }}</span>
                                    </p>
                                </div>
                            </div>
                        </li>
                        @php
                            $subSectionIds    = $sections->pluck("subSections")->flatten()->pluck("id")->toArray();
                            $valuePreferences = $preferences["config_companies_sub_sections"]->sub_sections ?? [];

                            $favoritePreferences = collect($valuePreferences)->filter(fn($e) => $e->is_favorite)->pluck("sub_section_id")->toArray();
                            $visiblePreferences  = collect($valuePreferences)->filter(fn($e) => $e->visible_in_menu)->pluck("sub_section_id")->toArray();

                            $favoriteCounter = 0;
                        @endphp
                        <li class="menu-header br-sidebar-admin-wrap px-4 py-2">
                            <a href="{{ route('home.index') }}" class="br-sidebar-admin-link br-sidebar-admin-link--compact {{ request()->routeIs('home.index') ? 'br-sidebar-admin-link--active' : '' }}" @if(request()->routeIs('home.index')) aria-current="page" @endif title="Configura tus favoritos (atajos en el panel).">
                                <span class="br-sidebar-admin-link__icon" aria-hidden="true">
                                    <i class="fa-solid fa-star"></i>
                                </span>
                                <span class="br-sidebar-admin-link__body">
                                    <span class="br-sidebar-admin-link__title ms-1">Configurar favoritos</span>
                                </span>
                                <i class="fa-solid fa-chevron-right br-sidebar-admin-link__chevron" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li class="menu-header pt-1">
                            <span class="menu-header-text text-uppercase">Favoritos</span>
                        </li>
                        @foreach($sections as $section)
                            @php
                                $subSectionsFiltered = $section->subSections->whereIn("id", $favoritePreferences);

                                $reference = $subSectionsFiltered->first();

                                if(!$reference) {
                                    continue;
                                }

                                $favoriteCounter++;
                            @endphp
                            <li class="{{ $section->has_sub_menu ? 'menu-header pe-none pt-1' : ('menu-item '.$section->dom_id) }}">
                                <a href="{{ $section->has_sub_menu ? 'javascript:void(0);' : $reference->dom_route_url }}" class="{{ $section->has_sub_menu ? 'fw-regular' : 'fw-bold' }} menu-link">
                                    <i class="{{ $section->dom_icon }} br-icon-accent me-3"></i>
                                    <div>{{ $section->dom_label }}</div>
                                </a>
                            </li>
                            @if($section->has_sub_menu)
                                <li class="menu-item open">
                                    <ul class="menu-sub py-0">
                                        @foreach($subSectionsFiltered as $subSection)
                                            <li class="menu-item {{ $subSection->dom_id }}" id="{{ $subSection->dom_id }}">
                                                <a href="{{ $subSection->dom_route_url }}" class="fw-bold menu-link py-1">
                                                    <div class="text-truncate">{{ $subSection->dom_label }}</div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                        @if($favoriteCounter === 0)
                            <li class="menu-header pt-1 pb-0 text-center">
                                <span class="menu-header-text text-uppercase text-white">Sin favoritos</span>
                            </li>
                        @endif
                        <li class="menu-header pt-2">
                            <span class="menu-header-text text-uppercase">Menú</span>
                        </li>
                        @foreach($sections as $section)
                            @php
                                $allPreferences = collect($valuePreferences)->pluck("sub_section_id")->toArray();
                                $allFiltered    = collect($subSectionIds)->filter(fn($e) => !in_array($e, $allPreferences))->toArray();

                                $sectionFiltered     = count($valuePreferences) > 0 ? array_merge($allFiltered, $visiblePreferences) : $subSectionIds;
                                $subSectionsFiltered = $section->subSections->whereIn("id", $sectionFiltered);

                                $reference = $subSectionsFiltered->first();

                                if(!$reference) {
                                    continue;
                                }
                            @endphp
                            <li class="menu-item {{ $section->dom_id }}" id="{{ $section->dom_id }}">
                                <a href="{{ $section->has_sub_menu ? 'javascript:void(0);' : $reference->dom_route_url }}" class="{{ $section->has_sub_menu ? 'menu-link menu-toggle' : 'menu-link' }}">
                                    <i class="{{ $section->dom_icon }} br-icon-accent me-3"></i>
                                    <div>{{ $section->dom_label }}</div>
                                </a>
                                @if($section->has_sub_menu)
                                    <ul class="menu-sub">
                                        @foreach($subSectionsFiltered as $subSection)
                                            <li class="menu-item {{ $subSection->dom_id }}" id="{{ $subSection->dom_id }}">
                                                <a href="{{ $subSection->dom_route_url }}" class="menu-link py-1">
                                                    <div class="text-truncate">{{ $subSection->dom_label }}</div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                        <li class="menu-item d-none">
                            <a href="javascript:void(0)" class="menu-link">
                                <i class="fa fa-check me-3"></i>
                                <div class="text-white">{{ $hasActiveSections }}</div>
                            </a>
                        </li>
                        <li class="menu-item d-none">
                            <a href="javascript:void(0)" class="menu-link">
                                <i class="fa fa-eye me-3"></i>
                                <div class="text-white">{{ $lastActiveSections }}</div>
                            </a>
                        </li>
                        <li class="menu-item br-menu-logout-item">
                            <a href="javascript:void(0)" class="menu-link br-menu-logout" onclick="$('#logout').submit();" role="button">
                                <span class="br-menu-logout__icon-wrap" aria-hidden="true">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </span>
                                <span class="br-menu-logout__label">Cerrar sesión</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" id="logout">
                                @csrf
                            </form>
                        </li>
                        <li class="menu-header invisible">
                            <small class="menu-header-text text-uppercase"></small>
                        </li>
                    </ul>
                </aside>
                <div class="layout-page br-layout-page">
                    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme br-layout-navbar" id="layout-navbar">
                        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" aria-label="Abrir menú">
                                <i class="ti ti-menu-2 ti-sm"></i>
                            </a>
                        </div>
                        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                            <div class="navbar-nav align-items-center">
                                <div class="nav-item navbar-search-wrapper mb-0">
                                    <a class="nav-item nav-link search-toggler d-flex align-items-center px-0 br-navbar-brand-link" href="{{ $ownerApp->web }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ asset($ownerApp->assets->img->logotype) }}" class="d-none d-md-block" width="80"/>
                                        <img src="{{ asset($ownerApp->assets->img->logomark) }}" class="d-block d-md-none" width="50"/>
                                    </a>
                                </div>
                            </div>
                            <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <li class="nav-item">
                                    <a class="nav-link px-0" href="javascript:void(0);" onclick='generateMyUrl(@json($company), true, "my_web")'>
                                        <span class="br-btn br-btn-primary br-btn-sm rounded-pill">
                                            <i class="fa fa-globe"></i>
                                            <span class="ms-2">Visitar mi página</span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <div class="content-wrapper br-layout-content">
                        <div class="container-xxl flex-grow-1 container-p-y">
                            @yield('content')
                        </div>
                        <div class="content-backdrop fade"></div>
                    </div>
                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
            {{-- <div class="drag-target"></div> --}}
        </div>

        @include("System.layouts.partials.down")
    </body>
</html>
