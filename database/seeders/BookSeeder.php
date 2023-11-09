<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $bookAuthors = ['OL1973725W','OL7497444A','OL7168845A','OL1608836A','OL5674374A'];

        foreach ($bookAuthors as $author) {
            $maxCount = 50;
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
}
