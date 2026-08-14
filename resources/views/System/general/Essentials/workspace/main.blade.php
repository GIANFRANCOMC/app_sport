@extends("System/layouts/main")

@section("content")
    <main class="br-workspace" aria-labelledby="workspace-title">
        <header class="br-workspace__hero">
            <div>
                <span class="br-workspace__eyebrow">Mi espacio de trabajo</span>
                <h1 id="workspace-title">Hola, {{ Str::before(Auth::user()->name, " ") }}</h1>
                <p>Continúa donde lo dejaste o abre una de las funciones que utilizas con mayor frecuencia.</p>
            </div>
            <span class="br-workspace__hero-icon" aria-hidden="true">
                <i class="fa-solid fa-compass"></i>
            </span>
        </header>

        <div class="br-workspace__grid">
            <section class="br-workspace__panel" aria-labelledby="workspace-recent-title">
                <div class="br-workspace__panel-head">
                    <div>
                        <h2 id="workspace-recent-title">Vistos recientemente</h2>
                        <p>Tus últimas 10 rutas, ordenadas desde la más reciente.</p>
                    </div>
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                </div>

                <div class="br-workspace__links">
                    @forelse($workspace["recent"] as $item)
                        <a href="{{ $item["url"] }}" class="br-workspace__link">
                            <span class="br-workspace__link-icon" aria-hidden="true"><i class="{{ $item["icon"] }}"></i></span>
                            <span class="br-workspace__link-copy">
                                <strong>{{ $item["label"] }}</strong>
                                <small>{{ $item["section"] }}</small>
                            </span>
                            <i class="fa-solid fa-arrow-right br-workspace__link-arrow" aria-hidden="true"></i>
                        </a>
                    @empty
                        <div class="br-workspace__empty">
                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                            <div>
                                <strong>Aún no hay rutas recientes</strong>
                                <span>Aparecerán aquí a medida que recorras el sistema.</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="br-workspace__panel" aria-labelledby="workspace-popular-title">
                <div class="br-workspace__panel-head">
                    <div>
                        <h2 id="workspace-popular-title">Más utilizados</h2>
                        <p>Accesos recomendados según tu uso acumulado.</p>
                    </div>
                    <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                </div>

                <div class="br-workspace__links">
                    @forelse($workspace["popular"] as $item)
                        <a href="{{ $item["url"] }}" class="br-workspace__link">
                            <span class="br-workspace__link-icon" aria-hidden="true"><i class="{{ $item["icon"] }}"></i></span>
                            <span class="br-workspace__link-copy">
                                <strong>{{ $item["label"] }}</strong>
                                <small>{{ $item["section"] }}</small>
                            </span>
                            <span class="br-workspace__visits">{{ $item["visit_count"] }} {{ $item["visit_count"] === 1 ? "visita" : "visitas" }}</span>
                        </a>
                    @empty
                        <div class="br-workspace__empty">
                            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                            <div>
                                <strong>Aún estamos aprendiendo tus preferencias</strong>
                                <span>Las recomendaciones se ordenarán automáticamente con tu uso.</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        @if($workspace["suggested"]->isNotEmpty())
            <section class="br-workspace__suggested" aria-labelledby="workspace-suggested-title">
                <div class="br-workspace__suggested-head">
                    <div>
                        <h2 id="workspace-suggested-title">Puedes empezar aquí</h2>
                        <p>Accesos disponibles para tu perfil.</p>
                    </div>
                </div>
                <div class="br-workspace__suggested-links">
                    @foreach($workspace["suggested"] as $item)
                        <a href="{{ $item["url"] }}">
                            <i class="{{ $item["icon"] }}" aria-hidden="true"></i>
                            <span>{{ $item["label"] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
@endsection
