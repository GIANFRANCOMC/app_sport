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
                        <li class="menu-header br-sidebar-profile-wrap text-start">
                            <div class="br-sidebar-profile">
                                <div class="br-sidebar-profile-avatar flex-shrink-0">
                                    <div class="avatar avatar-lg bg-white rounded-circle overflow-hidden br-brand-avatar">
                                        <img src="{{ asset($company->logomark ? 'storage/'.$company->logomark : $ownerApp->assets->img->logomark) }}" class="w-100 h-100 object-fit-cover" alt="Logo de {{ $company->commercial_name }}"/>
                                    </div>
                                </div>
                                <div class="br-sidebar-profile-meta">
                                    <p class="br-sidebar-profile-user mb-1" title="{{ $user->name }}">
                                        {{ Str::limit($user->name, 28) }}
                                    </p>
                                    <p class="br-sidebar-profile-company mb-1 d-none" title="{{ $company->commercial_name }}">
                                        {{ Str::limit($company->commercial_name, 28) }}
                                    </p>
                                    <div class="br-sidebar-profile-role-head mb-0">
                                        <p class="br-sidebar-profile-role mb-0" title="{{ $role->name }}">
                                            <span class="br-sidebar-profile-role-name">{{ Str::limit($role->name, 26) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @php
                            $subSectionIds    = $sections->pluck("subSections")->flatten()->pluck("id")->toArray();
                            $valuePreferences = $preferences["config_companies_sub_sections"]->sub_sections ?? [];

                            $favoritePreferences = collect($valuePreferences)->filter(fn($e) => $e->is_favorite)->pluck("sub_section_id")->toArray();
                            $visiblePreferences  = collect($valuePreferences)->filter(fn($e) => $e->visible_in_menu)->pluck("sub_section_id")->toArray();

                            $favoriteCounter = 0;

                            foreach($sections as $section) {

                                $favSubs = $section->subSections->whereIn("id", $favoritePreferences);

                                if(!$favSubs->first()) {

                                    continue;

                                }

                                $favoriteCounter++;

                            }

                            $fabFavoriteItems = collect();

                            foreach($sections as $section) {

                                $subSectionsFab = $section->subSections->whereIn("id", $favoritePreferences);

                                if(!$subSectionsFab->first()) {

                                    continue;

                                }

                                if($section->has_sub_menu) {

                                    foreach($subSectionsFab as $subSection) {

                                        $fabFavoriteItems->push([
                                            "label" => $section->dom_label." › ".$subSection->dom_label,
                                            "url"   => $subSection->dom_route_url,
                                            "icon"  => $section->dom_icon,
                                        ]);

                                    }

                                } else {

                                    $refFab = $subSectionsFab->first();

                                    $fabFavoriteItems->push([
                                        "label" => $section->dom_label,
                                        "url"   => $refFab->dom_route_url,
                                        "icon"  => $section->dom_icon,
                                    ]);

                                }

                            }
                        @endphp
                        <li class="menu-item {{ request()->routeIs('home.index') ? 'active' : '' }}" title="Configura tus favoritos (atajos en el panel).">
                            <a href="{{ route('home.index') }}" class="menu-link" @if(request()->routeIs('home.index')) aria-current="page" @endif>
                                <i class="menu-icon fa-solid fa-star br-icon-favorites me-3" aria-hidden="true"></i>
                                <div>Favoritos</div>
                            </a>
                        </li>
                        @if($favoriteCounter > 0)
                            <li class="menu-header divider py-0 d-none">
                                <span class="menu-header-text text-uppercase divider-text">Favoritos</span>
                            </li>
                            @foreach($sections as $section)
                                @php
                                    $subSectionsFiltered = $section->subSections->whereIn("id", $favoritePreferences);

                                    $reference = $subSectionsFiltered->first();

                                    if(!$reference) {

                                        continue;

                                    }
                                @endphp
                                <li class="d-none {{ $section->has_sub_menu ? 'menu-header pe-none pt-1' : ('menu-item '.$section->dom_id) }}">
                                    <a href="{{ $section->has_sub_menu ? 'javascript:void(0);' : $reference->dom_route_url }}" class="fw-semibold menu-link">
                                        <i class="{{ $section->dom_icon }} br-icon-accent me-3"></i>
                                        <div>{{ $section->dom_label }}</div>
                                    </a>
                                </li>
                                @if($section->has_sub_menu)
                                    <li class="menu-item open d-none">
                                        <ul class="menu-sub py-0">
                                            @foreach($subSectionsFiltered as $subSection)
                                                <li class="menu-item {{ $subSection->dom_id }}" id="{{ $subSection->dom_id }}">
                                                    <a href="{{ $subSection->dom_route_url }}" class="fw-regular menu-link py-1">
                                                        <div class="text-truncate">{{ $subSection->dom_label }}</div>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                        <li class="menu-header divider py-0">
                            <span class="menu-header-text text-uppercase divider-text">Menú</span>
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
                                <a href="{{ $section->has_sub_menu ? 'javascript:void(0);' : $reference->dom_route_url }}" class="{{ $section->has_sub_menu ? 'menu-link menu-toggle fw-semibold' : 'menu-link fw-semibold' }}">
                                    <i class="{{ $section->dom_icon }} br-icon-accent me-3"></i>
                                    <div>{{ $section->dom_label }}</div>
                                </a>
                                @if($section->has_sub_menu)
                                    <ul class="menu-sub">
                                        @foreach($subSectionsFiltered as $subSection)
                                            <li class="menu-item {{ $subSection->dom_id }}" id="{{ $subSection->dom_id }}">
                                                <a href="{{ $subSection->dom_route_url }}" class="fw-regular menu-link py-1">
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
                            <a href="javascript:void(0)" class="menu-link br-menu-logout" onclick="$('#logout').submit();" role="button" aria-label="Cerrar sesión" title="Cerrar sesión">
                                <span class="br-menu-logout__inner">
                                    <span class="br-menu-logout__icon-wrap" aria-hidden="true">
                                        <i class="fa-solid fa-power-off"></i>
                                    </span>
                                    <span class="br-menu-logout__label">Cerrar sesión</span>
                                </span>
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

        <div class="br-fab-favorites" id="brFabFavorites" data-open="0">
            <div class="br-fab-favorites__backdrop" id="brFabFavoritesBackdrop" aria-hidden="true"></div>
            <div class="br-fab-favorites__panel" id="brFabFavoritesPanel" role="region" aria-labelledby="brFabFavoritesTitle" aria-hidden="true">
                <div class="br-fab-favorites__head">
                    <span id="brFabFavoritesTitle" class="br-fab-favorites__title">Favoritos</span>
                    <button type="button" class="br-fab-favorites__close" id="brFabFavoritesClose" aria-label="Cerrar panel de favoritos">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="br-fab-favorites__body">
                    @if($fabFavoriteItems->isEmpty())
                        <p class="br-fab-favorites__empty mb-0">
                            Aún no marcas favoritos. Entra al <a href="{{ route('home.index') }}" class="fw-semibold">inicio</a> y elige accesos para acortar tu día.
                        </p>
                    @else
                        <ul class="br-fab-favorites__list list-unstyled mb-0">
                            @foreach($fabFavoriteItems as $fab)
                                <li class="br-fab-favorites__item">
                                    <a href="{{ $fab['url'] }}" class="br-fab-favorites__link">
                                        <i class="{{ $fab['icon'] }} br-fab-favorites__item-icon" aria-hidden="true"></i>
                                        <span class="br-fab-favorites__item-label">{{ $fab['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <button type="button" class="br-fab-favorites__btn btn rounded-circle shadow" id="brFabFavoritesToggle" title="Favoritos" aria-expanded="false" aria-controls="brFabFavoritesPanel">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                <span class="visually-hidden">Abrir favoritos</span>
            </button>
        </div>

        <script>
            (function () {
                var root = document.getElementById("brFabFavorites");
                if (!root) return;
                var btn = document.getElementById("brFabFavoritesToggle");
                var panel = document.getElementById("brFabFavoritesPanel");
                var closeBtn = document.getElementById("brFabFavoritesClose");
                var backdrop = document.getElementById("brFabFavoritesBackdrop");

                function setOpen(open) {
                    root.setAttribute("data-open", open ? "1" : "0");
                    panel.classList.toggle("br-fab-favorites__panel--open", open);
                    panel.setAttribute("aria-hidden", open ? "false" : "true");
                    btn.setAttribute("aria-expanded", open ? "true" : "false");
                }

                btn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    setOpen(!panel.classList.contains("br-fab-favorites__panel--open"));
                });
                closeBtn.addEventListener("click", function () { setOpen(false); });
                if (backdrop) {
                    backdrop.addEventListener("click", function () { setOpen(false); });
                }
                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape" && root.getAttribute("data-open") === "1") { setOpen(false); btn.focus(); }
                });
                document.addEventListener("click", function (e) {
                    if (root.getAttribute("data-open") === "1" && !root.contains(e.target)) { setOpen(false); }
                });
            })();
        </script>

        @include("System.layouts.partials.down")
    </body>
</html>
