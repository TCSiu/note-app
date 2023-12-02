<!DOCTYPE html>
<html lang="en">
@includeIf('layouts.head')
<body>
<div class="wrapper">
    @includeIf('layouts.sidebar')
        <div class="main">
            @includeIf('layouts.navbar')
            @hasSection('content')
                @yield('content')
            @endif
            @includeIf('layouts.footer')
        </div>
    </div>
    @includeIf('layouts.scripts')
</body>
</html>