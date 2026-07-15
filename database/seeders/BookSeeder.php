<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'description' => '吾輩は猫であるは、夏目漱石による日本の小説で、1905年から1906年にかけて雑誌に連載されました。物語は、名前のない猫の視点から描かれ、人間社会や人間の愚かさを風刺しています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
                'genres' => ['小説'],
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'description' => '人を動かすは、D・カーネギーによるビジネス・心理学の著作で、1936年に出版されました。人間関係やコミュニケーションについての洞察が得られます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'description' => 'リーダブルコードは、ソフトウェア開発におけるコードの可読性についての著作で、2012年に出版されました。良いコードを書くための原則やベストプラクティスが紹介されています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'description' => '7つの習慣は、スティーブン・R・コヴィーによる自己啓発の著作で、2013年に出版されました。個人の成長や成功のための原則が紹介されています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'description' => '坊っちゃんは、夏目漱石による日本の小説で、1906年に出版されました。物語は、少年の視点から描かれ、人間社会や人間の愚かさを風刺しています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'description' => 'サピエンス全史は、ユヴァル・ノア・ハラリによる歴史学の著作で、2016年に出版されました。人間の歴史や文明の発展について考察されています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'description' => 'Clean Codeは、ソフトウェア開発におけるコードの品質についての著作で、2017年に出版されました。良いコードを書くための原則やベストプラクティスが紹介されています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'description' => '嫌われる勇気は、岸見一郎と古賀史健による自己啓発の著作で、2013年に出版されました。人間関係や自己理解についての洞察が得られます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'description' => '火花は、又吉直樹による日本の小説で、2015年に出版されました。物語は、少年の視点から描かれ、人間社会や人間の愚かさを風刺しています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'description' => 'FACTFULNESSは、ハンス・ロスリングによるビジネス・科学の著作で、2019年に出版されました。世界の現状についての洞察が得られます。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'description' => 'コンテナ物語は、マルク・レビンソンによるビジネス・歴史の著作で、2007年に出版されました。世界の物流や経済について考察されています。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        $user = User::firstOrFail();

        foreach ($books as $book) {
            $genreNames = $book['genres'];

            unset($book['genres']);

            $book['user_id'] = $user->id;

            $createdBook = Book::firstOrCreate(['isbn' => $book['isbn']], $book);

            $genreIds = Genre::whereIn('name', $genreNames)->pluck('id');

            $createdBook->genres()->sync($genreIds);
        }
    }
}
