<!DOCTYPE html>

<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../System/assets/"
    data-template="vertical-menu-template-starter">
    <head>
        @include("System.layouts.partials.up")
        <link rel="stylesheet" href="{{ asset('System/assets/css/br-login.css') }}" />
    </head>
    <body>
        {{ $slot }}
        @include("System.layouts.partials.down")
    </body>
</html>
