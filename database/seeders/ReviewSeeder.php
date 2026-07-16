<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id', 'email');
        $books = Book::pluck('id', 'isbn');

        $reviews = [
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784101010014'],
                'rating' => 5,
                'comment' => '猫の視点が新鮮でとても面白かったです。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784101010014'],
                'rating' => 4,
                'comment' => 'ユーモアがあり、読みやすい作品でした。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784101010014'],
                'rating' => 3,
                'comment' => '平均的な本で、あまり魅力的ではなかった。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784422100524'],
                'rating' => 5,
                'comment' => '人との接し方について多くの学びがあり、仕事にも活かせそうです。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784422100524'],
                'rating' => 4,
                'comment' => '人間関係の基本を改めて考えさせられる一冊でした。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784422100524'],
                'rating' => 5,
                'comment' => 'シンプルな内容ですが、とても実践的で参考になりました。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784873115658'],
                'rating' => 5,
                'comment' => 'コードの可読性について理解が深まり、すぐに実践したい内容でした。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784873115658'],
                'rating' => 4,
                'comment' => 'エンジニアなら一度は読んでおきたい良書だと思います。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784873115658'],
                'rating' => 5,
                'comment' => 'サンプルコードが分かりやすく、とても勉強になりました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784863940246'],
                'rating' => 5,
                'comment' => '日々の考え方や行動を見直すきっかけになりました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784863940246'],
                'rating' => 4,
                'comment' => '内容は少し難しいですが、多くの気付きがありました。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784863940246'],
                'rating' => 5,
                'comment' => '長く読み継がれている理由がよく分かる一冊でした。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784101010021'],
                'rating' => 5,
                'comment' => '主人公のまっすぐな性格が魅力的で楽しく読めました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784101010021'],
                'rating' => 4,
                'comment' => 'テンポが良く、最後まで飽きずに読めました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784101010021'],
                'rating' => 3,
                'comment' => '少し時代背景は古いですが、面白さは十分に伝わりました。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784309226712'],
                'rating' => 5,
                'comment' => '人類の歴史を新しい視点で学ぶことができ、とても興味深かったです。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784309226712'],
                'rating' => 5,
                'comment' => 'ボリュームはありますが、最後まで引き込まれました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784309226712'],
                'rating' => 4,
                'comment' => '歴史だけでなく社会についても考えさせられる内容でした。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784048930598'],
                'rating' => 5,
                'comment' => '良いコードを書くための考え方がよく理解できました。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784048930598'],
                'rating' => 4,
                'comment' => '実務でも役立つ内容が多く、参考になりました。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784048930598'],
                'rating' => 5,
                'comment' => 'ソフトウェア開発者にはぜひ読んでほしい一冊です。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784478025819'],
                'rating' => 5,
                'comment' => '考え方が大きく変わるきっかけになりました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784478025819'],
                'rating' => 4,
                'comment' => '対話形式で読みやすく、理解しやすかったです。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784478025819'],
                'rating' => 3,
                'comment' => '内容には賛否が分かれそうですが、参考になる部分も多かったです。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784163902302'],
                'rating' => 5,
                'comment' => '登場人物の心情描写が丁寧で、とても引き込まれました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784163902302'],
                'rating' => 4,
                'comment' => '芸人という世界を新鮮な視点で知ることができました。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784163902302'],
                'rating' => 3,
                'comment' => '独特な雰囲気でしたが、最後まで楽しめました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784822289607'],
                'rating' => 5,
                'comment' => '思い込みを見直すきっかけになり、とても勉強になりました。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784822289607'],
                'rating' => 4,
                'comment' => 'データをもとに世界を見る大切さを学べました。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784822289607'],
                'rating' => 5,
                'comment' => '読み終えた後に物事の見方が変わる一冊でした。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784822251468'],
                'rating' => 4,
                'comment' => '普段意識しないコンテナの重要性を知ることができました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784822251468'],
                'rating' => 3,
                'comment' => '専門的な内容もありましたが、最後まで面白く読めました。',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
