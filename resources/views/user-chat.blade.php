<x-app-layout>
    {{-- <h1>{{ Auth::id() }}</h1> <!-- Displaying the authenticated user's ID --> --}}
    @livewire('chat', ['userId' => $userId])
</x-app-layout>

