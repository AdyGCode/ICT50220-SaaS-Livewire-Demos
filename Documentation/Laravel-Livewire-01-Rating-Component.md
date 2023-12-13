# Livewire Rating Component Tutorial

In this tutorial, we we create a mini library with users and books and add the ability for users to rate the books.

The application hosting the Rating component is a regular [Laravel](https://laravel.com/) application that  
uses [Breeze](https://laravel.com/docs/8.x/starter-kits#laravel-breeze) for authentication. I’m  
using [TailwindCSS](https://tailwindcss.com/) for styling.

This demo was built using:
- Laravel 10
- Livewire 3
- TailwindCSS v3

## Overview of steps
- Create project, install requirements, set-up Models and Seeders, Migrate 
  - See [Laravel-Livewire-Setting-Up-For-Demos.md](Laravel-Livewire-00-Setting-Up-For-Demos.md)
- Create simple Books interface
- Create Rating component

## Create Project

See [Laravel-Livewire-Setting-Up-For-Demos.md](Laravel-Livewire-00-Setting-Up-For-Demos.md)


## Create a Simple Books interface

For this we will use the `BooksController` and create a `books/index.blade.php` file that uses the app layout (which needs you to log in to access the views).

Create the `books` folder in the `resources/views` folder:

Windows:
```shell
mkdir resources\views\books
```

MacOS/Linux:
```shell
mkdir resources/views/books
```

Create a new `index.blade.php` file in this new folder and add:
```php
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
```

Add a new route to the `web.php` routes file:

```php
Route::middleware('auth')->group(function () {  
    Route::resource('books', BookController::class);  
});
```

## Create Rating component

In the index page, we have a new element: `<livewire:rating :book="$book" :key="$book->id"/>  `. 

This is Livewire's way of indicating we will include the component at this point. The component name is after the `livewire:` part of the element. In our case, `rating`.

Now we need to create the livewire component which consists of two parts:
- component controller
- component blade file

Use the command below to create the new component:

```shell
php artisan livewire:make Rating
```

### Rating Blade file
Open the `resources/livewire/rating.blade.php` file and add the required code.

There are three sections.
- Show the current average rating
- If the user has rated, show coloured stars for the rating
- Add the remaining stars as gray ones.

```php
<div>  
    {{-- Success is as dangerous as failure. --}}  

    {{-- Create the "Average rating display". --}}  
    <div class="text-sm flex justify-between mt-2">  
        <span>Average: </span>  
        <span class="text-lg text-white font-extrabold rounded-md bg-blue-600 px-2">  
            {{ $avgRating }}  
        </span>  
    </div>  
  
    {{-- Display coloured stars if the book has been rated by the current user --}}  
    <div class="flex items-center mt-0">  
        <span class="text-sm">Your rating:</span>  
        <div class="flex items-center ml-2">  
            @for ($stars = 0; $stars < $this->rating; $stars++)  
                <svg wire:click="setRating({{ $stars+1 }})" class="w-3 h-3 fill-current text-yellow-600"  
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">  
                    <path  
                        d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z">  
                    </path>                </svg>  
            @endfor  

    {{-- Add Grey stars to make a total of 5 stars --}} 
            @for ($stars = $this->rating; $stars < 5; $stars++)  
                <svg wire:click="setRating({{ $stars+1 }})" class="w-3 h-3 fill-current text-gray-400"  
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">  
                    <path  
                        d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z">  
                    </path>                </svg>  
            @endfor  
        </div>  
    </div>  
</div>
```

Important in this component is that we use a new 'attribute' in the SVG called `wire:`. This connects the component to the livewire rating controller which we will create in a moment.

This attribute ties the 'click' event to the "set rating" method in the Livewire rating controller. It contains an argument which is the index of the star (0-4) plus one to give a rating of 1 to 5.

When a star is clicked the livewire routing takes over and directs the event to the set rating method.

But how does this work?

### Livewire Rating Controller

So we now have a way of displaying the rating on the page, and, but we need to now make the component interactive.

Open the `app/Livewire/Rating.php` file.

By default it only contains the render method that will display the livewire component.

To make this useful we first add the component attributes:

```php
public $rating;  
public $avgRating;  
public $book;
```

These are public so that the component may interact with them.

Next we write a method to "mount" the component - in other words activate it.

```php
public function mount($book)  
{  
    $this->book = $book;  
    $userRating = $this->book->users()  
        ->where('user_id', auth()->user()->id)->first();  
  
    if (!$userRating){  
        $this->rating=0;  
    }else {  
        $this->rating=$userRating->pivot->rating;  
    }  
  
    $this  -> calculateAverageRating();  
}
```

This takes the book that is passed as part of the `<livewire:rating :book="$book" :key="$book->id"/>` element in the `index.blade.php` file.

It saves the book details, then uses this book to look up data in the 'book' model, retrieving the first record for the currently logged in user.

Next it checks if there are any ratings for the user on the book. If not then the book's rating is set to 0. If a rating has been made by this user then it is retrieved by going from the `userRating` collection into the `pivot` table to get the rating.

Final step is to calculate the average rating.

Next we add the `calcualteAverageRating` method:

```php
private function calculateAverageRating()  
{  
    $this->avgRating =  round($this->book->users()->avg('rating'),1);  
}
```

This performs a calculation on the ratings made by users and returns the average of the ratings to one decimal place.

The final step before the unsaved `render` method is to set the rating when a user clicks on one of the stars.

```php
public function setRating($val){  
    if($this->rating==$val){  
        $this->rating=0;  
    }else{  
        $this->rating=$val;  
    }  
  
    $userId=auth()->user()->id;  
    $userRating=$this->book->users()->where('user_id',$userId)->first();  
  
    if(!$userRating){  
    $userRating=$this->book->users()->attach($userId,['rating'=>$val]);  
    }  
    else {  
        $userRating = $this->book->users()->updateExistingPivot($userId, ['rating' => $val], false);  
    }  
    $this->calculateAverageRating();  
}
```

## References
This tutorial is based on [Creating a Rating Component With Livewire Laravel | by Pietro Iglio | Geek Culture | Medium](https://medium.com/geekculture/creating-a-rating-component-with-livewire-laravel-c76fe333ae33). Retrieved 2023-11-09
