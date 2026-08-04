<x-mcp::app :title="$appName">
    <x-slot:head>
        <base href="{{ $frontendUrl }}/">

        @foreach ($entryCss as $css)
            <link rel="stylesheet" href="{{ $frontendUrl }}/{{ $css }}">
        @endforeach
        @if ($entryJs)
            <script type="module" src="{{ $frontendUrl }}/{{ $entryJs }}" crossorigin="anonymous"></script>
        @endif
    </x-slot:head>

    <script type="module">
        createMcpApp(async (app) => {
            app.autoResize();
        });
    </script>
</x-mcp::app>
