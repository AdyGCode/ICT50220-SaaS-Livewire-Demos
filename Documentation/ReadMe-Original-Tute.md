# Creating a Rating Component With Livewire Laravel

[![Pietro Iglio](https://miro.medium.com/v2/resize:fill:88:88/1*Gjw-ujpxRo0Lkh6FtmlO4Q.jpeg)](https://medium.com/@igliop?source=post_page-----c76fe333ae33--------------------------------)[![Geek
Culture](https://miro.medium.com/v2/resize:fill:48:48/1*bWAVaFQmpmU6ePTjNIje_A.jpeg)](https://medium.com/geekculture?source=post_page-----c76fe333ae33--------------------------------)

[Pietro Iglio](https://medium.com/@igliop?source=post_page-----c76fe333ae33--------------------------------)

[Creating a Rating Component With Livewire Laravel | by Pietro Iglio | Geek Culture | Medium](https://medium.com/geekculture/creating-a-rating-component-with-livewire-laravel-c76fe333ae33)

[Load Balancer vs. Reverse Proxy vs. API Gateway | by Arslan Ahmad | Geek Culture | Medium](https://medium.com/geekculture/load-balancer-vs-reverse-proxy-vs-api-gateway-e9ec5809180c)

[Building a serverless application with Laravel, DynamoDB and React | by Pietro Iglio | Medium](https://igliop.medium.com/building-a-serverless-application-with-laravel-react-and-aws-lambda-d1f978a69fde)

[How to handle JSON Data in Laravel with Eloquent and JSON Columns: Complete guide 2023 | by Chimeremze Prevail Ejimadu | Sep, 2023 | Medium](https://medium.com/@prevailexcellent/how-to-handle-json-data-in-laravel-with-eloquent-and-json-columns-complete-guide-2023-480741120059)

[Free Tutorial - Learn Livewire v3 by building a CRUD App from Scratch | Udemy](https://www.udemy.com/course/learn-livewire-v3-by-building-a-crud-app-from-scratch/)

[Stop using Integer ID’s in your Database | by Tom Jay | Medium](https://medium.com/@thomasjay200/stop-using-integer-ids-in-your-database-5e5126a25dbe)

[Don’t Just LeetCode; Follow the Coding Patterns Instead | by Arslan Ahmad | Level Up Coding (gitconnected.com)](https://levelup.gitconnected.com/dont-just-leetcode-follow-the-coding-patterns-instead-4beb6a197fdb)

[JSON is incredibly slow: Here’s What’s Faster! | by Vaishnav Manoj | DataX Journal | Sep, 2023 | Medium](https://medium.com/data-science-community-srm/json-is-incredibly-slow-heres-what-s-faster-ca35d5aaf9e8)

[12 Microservices Patterns I Wish I Knew Before the System Design Interview | by Arslan Ahmad | Level Up Coding (gitconnected.com)](https://levelup.gitconnected.com/12-microservices-pattern-i-wish-i-knew-before-the-system-design-interview-5c35919f16a2)

In this post I’ll show how to build a Rating component using Laravel
Livewire. [Laravel Livewire](https://laravel-livewire.com/) is a framework on top of Laravel to build dynamic
applications without leaving the “comfort” of Laravel. The framework takes care of updating the web page by making AJAX
requests behind the scenes.

For testing purposes, I’m building a simple book catalogue in Laravel that uses the Rating component. This is a preview
of the complete application:

![](https://miro.medium.com/v2/resize:fit:700/1*QT9xthW9X9HyutZzIxpAhw.png)

In the red box I’ve highlighted the Rating component:

![](https://miro.medium.com/v2/resize:fit:360/1*2JnOWczOblOSWzUvNvQ5Ow.png)

You can click on the stars to give your rating, and both your rating and the average rating of all users is updated and
displayed. The interesting thing is that the component is reactive, i.e. without any page reload, and without using a
single line of Javascript.

The application hosting the Rating component is a regular [Laravel](https://laravel.com/) application that
uses [Breeze](https://laravel.com/docs/8.x/starter-kits#laravel-breeze) for authentication. I’m
using [Tailwindcss](https://tailwindcss.com/) for styling.

# Source Code

You can find the complete source code
on [my Github repository](https://github.com/code-runner-2017/livewire-rating-demo). Please add a star if you find it
useful.

# Environment Setup

I’m working on Windows, so some commands might need minor changes on MacOS/Linux. Assuming that you have
already [installed Laravel](https://laravel.com/docs/8.x/installation), let’s create a new Laravel application with
Livewire and Breeze:

```
laravel new livewire-rating-demo
cd livewire-rating-demo
composer require livewire/livewire
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run dev
```

*If you’re running the Laravel application under the web server root, as recommended, you can jump to the next section.
Otherwise, you can still get Laravel and Livewire working following these steps:*

```
php artisan livewire:publish --config
```

Edit your `.env` file so that `APP_URL` and `LIVEWIRE_ASSET_URL` point to the public folder of your Laravel app. Here is
an example with my local configuration that you should change according to your environment:

```
APP_URL=http://other.test.test/Laravel/livewire-rating-demo/public
LIVEWIRE_ASSET_URL=http://other.test/Laravel/livewire-rating-demo/public
```

Finally, edit `config/livewire.php` replacing:

```
'asset_url' => null,
```

with:

```
'asset_url'  => env('LIVEWIRE_ASSET_URL'),
```

# Database Setup

The quickest way to get a database running is by using SQLite. Now, let’s create an empty SQLite database. The command
on Windows is:

```
copy nul database\database.sqlite
```

or `touch database/database.sqlite` if you are a Linux/Mac user.

Next, open your `.env` file, and delete all lines starting with `DB_...`, then add:

```
DB_CONNECTION=sqlite
```

Now you should be able to run your migrations. Here is the command for Windows:

```
php artisan migrate
```

# Adding the Database Tables

We need two tables:

* `books`, to store all the book fields (title, author, etc.),
* `book_user`, an associative table between books and users that stores the rating users give to books.

Let’s create our Eloquent model, along with migration and seeder:

```
php artisan make:model Book --migration --seed
```

Open the books migration under `database/migrations` to define the structure of the two tables:

![](https://miro.medium.com/v2/resize:fit:700/1*6WbDz3s41ZOPXjM4BHN9TQ.png)

As you can see, I’ve added the`rating` column to the associative table `book_user` to store user ratings.

I’ve extended the Book model, located under `app/Models/Book.php`, to access all the users that voted for the book,
along with their rating (Eloquent pivot column):

![](https://miro.medium.com/v2/resize:fit:700/1*wF-BA_iYuR-TydN7bfrAOg.png)

The `rating` pivot column can be now be accessed with:

```
$book->users()->where(…)->first()->pivot->rating
```

# Seeding the Database

To populate the book catalogue, I created a database seeder that takes inputs from
the [Open Library](https://openlibrary.org/) public API and saves them into the database. Here’s the code that downloads
10 books from the great John Steinbeck:

![](https://miro.medium.com/v2/resize:fit:700/1*-iOkOOakSaCjlAlU0xbeMA.png)

I’m not entering into the details of the Open Library API. If you want to use a different author, you must change:

```
'author' => 'OL25788A'
```

with the code of your favorite writer. Just access the regular website and search for an author.

Now, you can feed your database:

```
php artisan db:seed --class BookSeeder
```

If you open the database with any client that supports SQLite, such
as [DB Browser for SQLite](https://sqlitebrowser.org/), you should see the book table full of data.

# Displaying Books

Let’s change the home page `resources/views/dashboard.blade.php` to display all the books in the database:

![](https://miro.medium.com/v2/resize:fit:700/1*A90YXu4MCxxZi2u7q9j5Ig.png)

The page is iterating over the `$books` collection. Next, edit `routes/web.php` to pass the books to the page:

![](https://miro.medium.com/v2/resize:fit:700/1*ybA0FIBCM55oXRbZLjA4yw.png)

Let’s try: access the home page of your app and register by clicking on the “Register” link on the top right of the
page. You should see the retrieved books (cover, title, etc.) without the Rating component, that we’re going to build in
the next section.

# Creating the Livewire Rating component

First, enable your pages to host Livewire components editing `resources/views/layouts/app.blade.php` and adding:

* `<livewire:styles />` before `</head>`
* `<livewire:scripts />` before `</body>`

Now, you can create the Rating component:

```
php artisan livewire:make Rating
```

The Rating component is split into a Blade template and a server-side component. First, we include the new component in
the `dashboard.blade.php` view:

![](https://miro.medium.com/v2/resize:fit:700/1*UUkAcGIBkPTAQSfpj1TOxQ.png)

Here’s how the Blade component `resources/views/livewire/rating.blade.php`looks:

![](https://miro.medium.com/v2/resize:fit:700/1*H3pEGtiuExvaBVP_tMF-GQ.png)

and the server-side component: `app/Http/Livewire/Rating.php`:

![](https://miro.medium.com/v2/resize:fit:700/1*9d76M_P1P3GFSt8TqKAPXg.png)

The `mount()` method is calculated when the component is created, so this is the right place to initialize properties
accessed by the Blade template.

In the Blade template, I’ve bound the “click” event on each star icon to the server-side method `setRating()`. Here’s
how the method in the `Rating.php` class looks:

![](https://miro.medium.com/v2/resize:fit:700/1*4jVhYTDzn0Us9Wg46N5L9A.png)

We’re done! When you click on the rating button, your rating is stored on the server-side, and the template gets an
updated average rating calculated from all user ratings. The interaction is dynamic — your view gets updated without
having to reload the entire page — and all without a single line of Javascript!

Try registering multiple users to see how the average rating changes.

# Conclusions and Final Thoughts

In my opinion, Livewire is a very interesting framework. Whenever a new framework shows up, the community is divided
into those for and those against. However, it is preferable to focus on what it’s good for, and when it’s better to use
a more traditional approach based on React/Vue.

If you’re already organized with front end and back end developers working on different teams, you’d probably want to
stick with React/Vue. If you’re a full stack developer and you’re familiar with Laravel, or you’re willing to learn
Laravel, you should seriously consider Livewire, as it can really shorten your development time. For example, if you’re
in a prototyping phase, or you need to build something on a tight schedule and keep a reactive behavior, Livewire can
really make a difference.

Keep in mind that a Livewire component is an end-to-end component, whereas a React/Vue component needs an API to
interact with the back end. While this could be considered a drawback from a purist’s point of view, this means that you
can build or download libraries of components that you can easily reuse among projects.

Of course, before using Livewire with real-life projects, I recommend that you learn it deeply and experiment quite a
bit. Because of the higher abstraction level, you’ve less control of what’s going on. You should definitively learn how
Livewire works behind the scenes.

As usual, I’m open to suggestions and feedback.

[Livewire](https://medium.com/tag/livewire?source=post_page-----c76fe333ae33---------------livewire-----------------)

[Laravel](https://medium.com/tag/laravel?source=post_page-----c76fe333ae33---------------laravel-----------------)

[PHP](https://medium.com/tag/php?source=post_page-----c76fe333ae33---------------php-----------------)

[Programming](https://medium.com/tag/programming?source=post_page-----c76fe333ae33---------------programming-----------------)

[Software Development](https://medium.com/tag/software-development?source=post_page-----c76fe333ae33---------------software_development-----------------)

130

**2**

[![Pietro Iglio](https://miro.medium.com/v2/resize:fill:144:144/1*Gjw-ujpxRo0Lkh6FtmlO4Q.jpeg)](https://medium.com/@igliop?source=post_page-----c76fe333ae33--------------------------------)[![Geek
Culture](https://miro.medium.com/v2/resize:fill:64:64/1*bWAVaFQmpmU6ePTjNIje_A.jpeg)](https://medium.com/geekculture?source=post_page-----c76fe333ae33--------------------------------)

[Written by **Pietro
Iglio**](https://medium.com/@igliop?source=post_page-----c76fe333ae33--------------------------------)

[125 Followers](https://medium.com/@igliop/followers?source=post_page-----c76fe333ae33--------------------------------)

**·**Writer
for [Geek Culture](https://medium.com/geekculture?source=post_page-----c76fe333ae33--------------------------------)

Follow
