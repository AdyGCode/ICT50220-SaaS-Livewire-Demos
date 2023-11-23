<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Books') }}
        </h2>
    </x-slot>

        <div class="py-12">

        <div class="container max-w-7xl mx-auto pb-10 flex flex-wrap">
            @foreach($books as $book)
                <div class="w-full sm:w-1/4 md:w-1/6 lg:w-1/8 p-2 mb-4 shadow-sm">
                    <a href="{{$book->url}}" target="_blank">
                        @if(is_null($book->cover_url))
                        <img src="No-Cover-Image.png" class="h-80 rounded-lg" alt="No Cover Image Available">
                        @else
                        <img src="{{ $book->cover_url }}" class="h-80 rounded-lg" alt="{{ $book->title }}">
                        @endif
                    </a>
                    <h2 class="text-xl py-3">
                        <a href="{{$book->url}}" target="_blank" class="text-black no-underline">
                            {{ $book->title }}
                        </a> <span class="text-sm">({{ $book->year }})</span>
                    </h2>

                    <livewire:rating :book="$book" :key="$book->id"/>

                    <p class="text-xs leading-normal">{{ $book->first_sentence }}</p>
                </div>
            @endforeach
        </div>
        <div class="container max-w-7xl mx-auto pb-10 ">{{ $books->links() }}</div>
    </div>
</x-app-layout>
