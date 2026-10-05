@auth
    <script>
        window.__chamseOnboarding = {
            seen: @json(auth()->user()->tours_seen ?? []),
            endpoint: "{{ route('admin.onboarding.seen') }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>
    @vite(['resources/js/onboarding/index.js'])
@endauth
