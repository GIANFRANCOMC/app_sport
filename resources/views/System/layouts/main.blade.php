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

                            $favoritePreferences = collect($valuePreferences)
                                                        ->filter(fn($preference) => data_get($preference, "is_favorite", false))
                                                        ->pluck("sub_section_id")
                                                        ->map(fn($id) => (int) $id)
                                                        ->unique()
                                                        ->values()
                                                        ->toArray();

                            $visiblePreferences = collect($valuePreferences)
                                                        ->filter(fn($preference) => data_get($preference, "visible_in_menu", true))
                                                        ->pluck("sub_section_id")
                                                        ->map(fn($id) => (int) $id)
                                                        ->unique()
                                                        ->values()
                                                        ->toArray();

                            $favoriteMenuGroups = collect();

                            foreach($sections as $section) {

                                $subSectionsFab = $section->subSections->whereIn("id", $favoritePreferences);

                                if(!$subSectionsFab->first()) {

                                    continue;

                                }

                                $favoriteMenuGroups->push([
                                    "section" => $section->dom_label,
                                    "icon" => $section->dom_icon,
                                    "items" => $subSectionsFab->map(fn($subSection) => [
                                        "label" => $subSection->dom_label,
                                        "description" => $subSection->description,
                                        "url" => $subSection->dom_route_url
                                    ])->values()
                                ]);

                            }
                        @endphp
                        <li class="menu-item {{ request()->routeIs('home.index') ? 'active' : '' }}" title="Configura tus favoritos (atajos en el panel).">
                            <a href="{{ route('home.index') }}" class="menu-link" @if(request()->routeIs('home.index')) aria-current="page" @endif>
                                <i class="menu-icon fa-solid fa-star br-icon-favorites me-3" aria-hidden="true"></i>
                                <div>Favoritos</div>
                            </a>
                        </li>
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
                            {{-- <div class="navbar-nav align-items-center">
                                <div class="nav-item navbar-search-wrapper mb-0">
                                    <a class="nav-item nav-link search-toggler d-flex align-items-center px-0 br-navbar-brand-link" href="{{ $ownerApp->web }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ asset($ownerApp->assets->img->logotype) }}" class="d-none d-md-block" width="80"/>
                                        <img src="{{ asset($ownerApp->assets->img->logomark) }}" class="d-block d-md-none" width="50"/>
                                    </a>
                                </div>
                            </div> --}}
                            {{-- <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <li class="nav-item">
                                    <a class="nav-link px-0" href="javascript:void(0);" onclick='generateMyUrl(@json($company), true, "my_web")'>
                                        <span class="br-btn br-btn-primary br-btn-sm rounded-pill">
                                            <i class="fa fa-globe"></i>
                                            <span class="ms-2">Visitar mi página</span>
                                        </span>
                                    </a>
                                </li>
                            </ul> --}}
                            <div class="br-fab-favorites br-navbar-favorites me-auto" id="brFabFavorites" data-open="0">
                                <div class="br-fab-favorites__backdrop" id="brFabFavoritesBackdrop" aria-hidden="true"></div>
                                <div class="br-fab-favorites__panel" id="brFabFavoritesPanel" role="region" aria-labelledby="brFabFavoritesTitle" aria-hidden="true">
                                    <div class="br-fab-favorites__head">
                                        <span id="brFabFavoritesTitle" class="br-fab-favorites__title">Favoritos</span>
                                        <button type="button" class="br-fab-favorites__close" id="brFabFavoritesClose" aria-label="Cerrar favoritos" title="Cerrar favoritos">
                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="br-fab-favorites__body" id="brFabFavoritesBody">
                                        @if($favoriteMenuGroups->isEmpty())
                                            <p class="br-fab-favorites__empty mb-0" data-favorites-empty>
                                                Aún no tienes favoritos. Puedes configurarlos desde
                                                <a href="{{ route('home.index') }}" class="fw-semibold">Favoritos</a>.
                                            </p>
                                        @else
                                            <ul class="br-fab-favorites__list list-unstyled mb-0" data-favorites-list>
                                                @foreach($favoriteMenuGroups as $group)
                                                    <li class="br-fab-favorites__group">
                                                        <div class="br-fab-favorites__group-head">
                                                            <i class="{{ $group['icon'] }} br-fab-favorites__group-icon" aria-hidden="true"></i>
                                                            <span>{{ $group['section'] }}</span>
                                                        </div>
                                                        <ul class="br-fab-favorites__group-items list-unstyled mb-0">
                                                            @foreach($group['items'] as $favoriteItem)
                                                                <li class="br-fab-favorites__item">
                                                                    <a href="{{ $favoriteItem['url'] }}" class="br-fab-favorites__link">
                                                                        <span class="br-fab-favorites__item-content">
                                                                            <span class="br-fab-favorites__item-label">{{ $favoriteItem['label'] }}</span>
                                                                            @if($favoriteItem['description'])
                                                                                <span class="br-fab-favorites__item-description">{{ $favoriteItem['description'] }}</span>
                                                                            @endif
                                                                        </span>
                                                                        <i class="fa-solid fa-arrow-right br-fab-favorites__item-arrow" aria-hidden="true"></i>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="br-fab-favorites__btn br-navbar-favorites__btn"
                                    id="brFabFavoritesToggle"
                                    aria-label="Abrir favoritos"
                                    aria-expanded="false"
                                    aria-controls="brFabFavoritesPanel">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    <span class="br-navbar-favorites__label">Favoritos</span>
                                    <span class="br-navbar-favorites__count" id="brFabFavoritesCount">{{ $favoriteMenuGroups->sum(fn($group) => $group['items']->count()) }}</span>
                                </button>
                            </div>
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

        <script>
            (function () {

                var root = document.getElementById("brFabFavorites");

                if (!root) return;

                var btn = document.getElementById("brFabFavoritesToggle");
                var panel = document.getElementById("brFabFavoritesPanel");
                var closeBtn = document.getElementById("brFabFavoritesClose");
                var backdrop = document.getElementById("brFabFavoritesBackdrop");
                var body = document.getElementById("brFabFavoritesBody");
                var count = document.getElementById("brFabFavoritesCount");
                var homeUrl = @json(route("home.index"));

                function setOpen(open) {

                    root.setAttribute("data-open", open ? "1" : "0");
                    panel.classList.toggle("br-fab-favorites__panel--open", open);
                    panel.setAttribute("aria-hidden", open ? "false" : "true");
                    btn.setAttribute("aria-expanded", open ? "true" : "false");
                    btn.setAttribute("aria-label", open ? "Cerrar favoritos" : "Abrir favoritos");

                }

                function getFavoriteItems(preferences) {

                    var preferenceConfig = preferences?.config_companies_sub_sections;
                    var favoriteIds = new Set(
                        (preferenceConfig?.sub_sections || [])
                            .filter(function (preference) { return preference?.is_favorite; })
                            .map(function (preference) { return Number(preference.sub_section_id); })
                    );
                    var groups = [];

                    (window.sections || []).forEach(function (section) {

                        var subSections = section?.sub_sections || section?.subSections || [];
                        var items = [];

                        subSections.forEach(function (subSection) {

                            if (!favoriteIds.has(Number(subSection.id))) return;

                            items.push({
                                label: subSection.dom_label,
                                description: subSection.description || "",
                                url: subSection.dom_route_url,
                            });

                        });

                        if(items.length > 0) {

                            groups.push({
                                section: section.dom_label,
                                icon: section.dom_icon,
                                items: items
                            });

                        }

                    });

                    return groups;

                }

                function renderFavoriteItems(preferences) {

                    var groups = getFavoriteItems(preferences);
                    var totalItems = groups.reduce(function (total, group) {
                        return total + group.items.length;
                    }, 0);

                    count.textContent = String(totalItems);
                    body.replaceChildren();

                    if (totalItems === 0) {

                        var empty = document.createElement("p");
                        var link = document.createElement("a");

                        empty.className = "br-fab-favorites__empty mb-0";
                        empty.append("Aún no tienes favoritos. Puedes configurarlos desde ");
                        link.href = homeUrl;
                        link.className = "fw-semibold";
                        link.textContent = "Favoritos";
                        empty.append(link, ".");
                        body.appendChild(empty);

                        return;

                    }

                    var list = document.createElement("ul");
                    list.className = "br-fab-favorites__list list-unstyled mb-0";

                    groups.forEach(function (group) {

                        var groupItem = document.createElement("li");
                        var groupHead = document.createElement("div");
                        var groupIcon = document.createElement("i");
                        var groupTitle = document.createElement("span");
                        var groupList = document.createElement("ul");

                        groupItem.className = "br-fab-favorites__group";
                        groupHead.className = "br-fab-favorites__group-head";
                        groupIcon.className = (group.icon || "") + " br-fab-favorites__group-icon";
                        groupIcon.setAttribute("aria-hidden", "true");
                        groupTitle.textContent = group.section;
                        groupList.className = "br-fab-favorites__group-items list-unstyled mb-0";
                        groupHead.append(groupIcon, groupTitle);

                        group.items.forEach(function (item) {

                            var listItem = document.createElement("li");
                            var link = document.createElement("a");
                            var content = document.createElement("span");
                            var label = document.createElement("span");
                            var description = document.createElement("span");
                            var arrow = document.createElement("i");

                            listItem.className = "br-fab-favorites__item";
                            link.className = "br-fab-favorites__link";
                            link.href = item.url;
                            content.className = "br-fab-favorites__item-content";
                            label.className = "br-fab-favorites__item-label";
                            label.textContent = item.label;
                            description.className = "br-fab-favorites__item-description";
                            description.textContent = item.description;
                            arrow.className = "fa-solid fa-arrow-right br-fab-favorites__item-arrow";
                            arrow.setAttribute("aria-hidden", "true");
                            content.appendChild(label);

                            if(item.description) {

                                content.appendChild(description);

                            }

                            link.append(content, arrow);
                            listItem.appendChild(link);
                            groupList.appendChild(listItem);

                        });

                        groupItem.append(groupHead, groupList);
                        list.appendChild(groupItem);

                    });

                    body.appendChild(list);

                }

                btn.addEventListener("click", function (e) {

                    e.stopPropagation();
                    setOpen(!panel.classList.contains("br-fab-favorites__panel--open"));

                });

                closeBtn.addEventListener("click", function () { setOpen(false); });

                if(backdrop) backdrop.addEventListener("click", function () { setOpen(false); });

                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape" && root.getAttribute("data-open") === "1") { setOpen(false); btn.focus(); }
                });

                document.addEventListener("click", function (e) {
                    if (root.getAttribute("data-open") === "1" && !root.contains(e.target)) { setOpen(false); }
                });

                window.addEventListener("br:preferences-updated", function (event) {
                    renderFavoriteItems(event.detail?.preferences || window.preferences || {});
                });

            })();
        </script>

        @include("System.layouts.partials.down")
    </body>
</html>
