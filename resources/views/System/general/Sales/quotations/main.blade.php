@extends("System/layouts/main")

@section("content")
    <div id="app"></div>
    <script>
        window.__BR_QUOTATIONS_PAGE__ = @json($pageMode ?? "list");
    </script>
    @vite("resources/js/System/Pages/Sales/quotations/main.js")
@endsection
