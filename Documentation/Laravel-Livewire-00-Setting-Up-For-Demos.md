# Setting Up for Component Demos/Tutorials

Livewire is a great way to overcome the need to write JavaScript, React, or other 
code and provide an interactive dynamic interface.

This tutorial goes through setting up a set of base controllers, models and other 
items for other tutorials to use.

This application a regular [Laravel](https://laravel.com/) application that  
uses [Breeze](https://laravel.com/docs/8.x/starter-kits#laravel-breeze) for 
authentication. We are using [TailwindCSS](https://tailwindcss.com/) for styling, and 
[Laravel Livewire](https://laravel-livewire.com/) to provide interactive and 
dynamic components on the web pages.

This demo was built using:
- Laravel 10
- Livewire 3
- TailwindCSS v3
- PHP 8.2+
- MariaDB

## Overview of steps
- Create project
- Install Livewire and Blade
- Create Book and Book Rating Model, et al
- Create Seeder for books using OpenLibrary API

## Create Project
For this tutorial, we start by creating a new Laravel project.

For this we are using the `composer create-project` command.

Before you start make sure you are in the correct folder where your projects are kept. 
At TAFE we keep them in the `%userprofile%\Source\Repos` folder. Get there using:

##### Windows
```shell
cd %userprofile%\Source\Repos
```

##### MacOS/Linux
```shell
cd ~/Source/Repos
```

Next create the new project:

```shell
composer create-project laravel/laravel ICT50220-SaaS-Livewire-Demos  
cd ICT50220-SaaS-Livewire-Demos
```

### Database

For this example we are presuming you are using a MariaDB or MySQL database. You 
will need to update the Database settings in the `.env` for other databases.

> **Aside**: 
> It is possible, when using Docker, to create a full base application using the
> following command:
> ```shell
> curl -s "https://laravel.build/test-instructions?with=mariadb,minio,mailpit" | bash```
> ```
> 
> This includes just MariaDB, Laravel, MailPit and Minio in the containers.
> 
> Once you have used this, in place of using `php artisan` or similar you use
> 'sail artisan', 'sail npm' etc.


## Install Livewire and Blade

Once the new Laravel project is created, we are now able to add the Livewire and 
Breeze packages using composer: 

```shell
composer require livewire/livewire  
composer require laravel/breeze --dev  
```

We will now install the Breeze package, selecting the following options:
- 

```shell
php artisan breeze:install
```

Finally, we perform the migrations to create the user table and other base tables.

```shell  
php artisan migrate
```

Once the core Livewire and Blade packages are installed open a second terminal and 
run the `npm` application to install and live reload pages as they have their 
content and CSS classes updated and saved. The `npm update` section is optional.

```shell
npm install
npm update
npm run dev
```

> Aside: You may run these three commands consecutively by entering 
> `npm install && npm update && npm run dev` on a single line and pressing enter.

We will publish some settings so that if you want to tailor the code and application
in a particular way, you are able to do so:

```
php artisan livewire:publish --config
```

Edit the `.env` file from your application and update/add the `APP_URL` line, and add 
the `LIVEWIRE_ASSET_URL` line as shown below:

```env
APP_URL=http://ict50220-saas-livewire-rating.test/public  
LIVEWIRE_ASSET_URL=http://ict50220-saas-livewire-rating.test/public
```

> Note: These two lines will need to be updated for a live production version of this project.

## Create Book and Book Rating

We will be creating two migrations to create the `books` and `book_rating` tables respectively.

Starting with the books we will create the Eloquent model, the migration, and other components for the books table.

```shell
php artisan make:model Book --migration --seed --controller --resource --requests --pest
```

This could be shortened to `php artisan make:model Book -ars --pest`. 😊

We will also create the required migration for the `book_user` table which will store the rating that the user gives each individual book:

```shell
php artisan make:migration create_book_user_table
```

Now we are able to add the table structures to the `yyyy_mm-dd_hhmmss_create_books_table.php` and the  `yyyy_mm-dd_hhmmss_create_books_user_table.php`.

#### Books Table
```php
Schema::create('books', function (Blueprint $table) {  
    $table->id();  
    $table->string('title');  
    $table->string('author');  
    $table->unsignedSmallInteger('year')->nullable();  
    $table->string('cover_url')->nullable();  
    $table->string('first_sentence')->nullable();  
    $table->string('url');  
    $table->timestamps();  
});
```

#### Book User Table
```php
Schema::create('book_user', function (Blueprint $table) {  
    $table->id();  
    $table->foreignId('book_id')->references('id')->on('books');  
    $table->foreignId('user_id')->references('id')->on('users');  
    $table->integer('rating')->nullable();  
    $table->timestamps();  
});
```

This book-user table is the pivot we use to store the ratings.

The `rating` field will hold the individual user's rating for the book.

We will be able to access this using:
```php
$book->users()->where(…)->first()->pivot->rating
```

### Create User Seeder

As we need to be able to test this, we will use the following code to create a 
group of uses with email addresses at the 'black hole' "example.com" domain.

```shell
php artisan make:seeder UserSeeder
```

Now edit this new file...

```php
function run(): void  
{  
    $seedUsers = [  
        [  
            'name' => 'Ad Ministrator',  
            'email' => 'ad.ministrator@example.com',  
            'password' => 'Password1',  
        ],  
        [  
            'name' => 'Annie Wun',  
            'email' => 'annie.wun@example.com',  
            'password' => 'Password1',  
        ],  
        [  
            'name' => 'Andy Mann',  
            'email' => 'andy.mann@example.com',  
            'password' => 'Password1',  
        ],  
    ];  
  
    foreach ($seedUsers as $newUser) {  
        $newUser['password'] = Hash::make($newUser['password']);  
        $user = User::create([  
            'name' => $newUser['name'],  
            'email' => $newUser['email'],  
            'password' => $newUser['password'],  
        ]);  
    }  
}
```

## Create Seeder for books using OpenLibrary API

We already have a seeder for the Books, we just need to make it do something.

To do so we are going to interact with the [Open Library API](https://openlibrary.org/developers/api) to retrieve books by a small number of authors. We limit this to at most 5 (`$maxCount`) books per author.

The `$bookAuthors` is an array of OpenLibrary Author Identifiers.

```php
public function run(): void  
{  
    $bookAuthors = ['OL1973725W','OL7497444A','OL7168845A','OL1608836A','OL5674374A'];  
  
    foreach ($bookAuthors as $author) {  
        $maxCount = 5;  
        $res = Http::withOptions([  
            'redirect.disable' => true  
        ])->withHeaders([  
            'Accept' => 'application/json',  
            'Content-Type' => 'application/x-www-form-urlencoded',  
            'User-Agent' => 'openlibrary.php/0.0.1'  
        ])->get('http://openlibrary.org/search.json', [  
            'author' => $author,  
            'limit' => $maxCount,  
        ]);  
  
        foreach ($res['docs'] as $doc) {  
            $workPath = $doc['key'];  
            $coverKey = $doc['cover_i']??null;  
            $firstSentence = $doc['first_sentence'][0] ?? '';  
  
            DB::table('books')->insert([  
                'title' => $doc['title'],  
                'author' => $doc['author_name'][0],  
                'year' => $doc['first_publish_year']??null,  
                'cover_url' => "http://covers.openlibrary.org/b/id/$coverKey-M.jpg",  
                'first_sentence' =>Str::limit($firstSentence, 192, $end="…"),  
                'url' => 'http://openlibrary.org/' . $doc['key'],  
            ]);  
  
            if ($maxCount-- == 0) break;  
        }  
    }  
}
```

Next edit the `DatabaseSeeder` file and add the required calls to the seeder classes:

```php
public function run(): void  
{  
    $this->call([  
       UserSeeder::class,  
       BookSeeder::class,  
    ]);  
}
```

Run the migration and seed in one as this is a demo:
```shell
php artisan migrate:fresh --seed --step
```

Remember that you would not use the `:fresh` option when on a production site!


## References
This tutorial is based on [Creating a Rating Component With Livewire Laravel | by Pietro Iglio | Geek Culture | Medium](https://medium.com/geekculture/creating-a-rating-component-with-livewire-laravel-c76fe333ae33). Retrieved 2023-11-09
