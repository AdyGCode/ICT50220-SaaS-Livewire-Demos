<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Books') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="container max-w-7xl mx-auto pb-10 flex flex-wrap">
            @foreach($books as $book)
                <div class="w-full sm:w-1/3 md:w-1/4 lg:w-1/6 p-2 mb-4 shadow-sm">
                    <a href="{{$book->url}}" target="_blank">
                        <img src="{{ $book->cover_url }}" class="h-80 rounded-lg" alt="{{ $book->title }}">
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
        <div class="container max-w-7xl mx-auto pb-10 ">{{ $books->links() }}</div>
    </div>
</x-app-layout>
