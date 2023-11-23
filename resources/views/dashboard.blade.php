<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 ">
        <div class="z-10 max-w-7xl mx-auto sm:px-6 lg:px-8 flex gap-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 grow">
                {{ __("You're logged in!") }}
            </div>


            <div class="items-center mt-6 lg:mt-0 overflow-visible">
                <livewire:autocomplete-dropdown />
            </div>
        </div>

        <div class="py-12 bg-white mt-6 z-0">
            <div class="container max-w-7xl mx-auto pb-10 flex flex-wrap">
                @foreach($books as $book)
                    <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 p-3 mb-4 shadow-sm">
                        <a href="{{$book->url}}" target="_blank">
                            <img src="{{ $book->cover_url??'No-Cover-Image.png' }}" class="h-80 rounded-lg" alt="{{ $book->title }}">
                        </a>
                        <h2 class="text-xl py-3">
                            <a href="{{$book->url}}" target="_blank" class="text-black no-underline">
                                {{ $book->title }}
                            </a>
                        </h2>

                        <livewire:rating :book="$book" :key="$book->id"/>

                        <p class="text-xs leading-normal">{{ $book->first_sentence }}</p>
                    </div>
                @endforeach
            </div>
        </div>
</x-app-layout>
