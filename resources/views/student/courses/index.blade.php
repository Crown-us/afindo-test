<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Course</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-900">
                {{-- Placeholder: diganti katalog course pada tahap berikutnya. --}}
                Halo {{ auth()->user()->name }}, katalog course akan tampil di sini.
            </div>
        </div>
    </div>
</x-app-layout>
