<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーは読書計画一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.index');

        $response->assertViewHas('readingPlans');
        $response->assertViewHas('currentStatus');
    }

    /** @test */
    public function 自分の読書計画だけを表示できる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ReadingPlan::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($readingPlans) {
            return $readingPlans->count() === 2;
        });
    }

    /** @test */
    public function ステータスで読書計画を絞り込みできる(): void
    {
        $user = User::factory()->create();

        ReadingPlan::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index', [
                'status' => ReadingPlanStatus::Completed->value,
            ]));

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($readingPlans) {
            return $readingPlans->count() === 3;
        });
    }

    /** @test */
    public function 読書計画を期日の新しい順で表示できる(): void
    {
        $user = User::factory()->create();

        $oldPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => '2026-08-10',
        ]);

        $newPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => '2026-08-20',
        ]);

        $middlePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => '2026-08-15',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($readingPlans) use ($oldPlan, $newPlan, $middlePlan) {
            return $readingPlans->pluck('id')->all() === [
                $newPlan->id,
                $middlePlan->id,
                $oldPlan->id,
            ];
        });
    }

    /** @test */
    public function 認証済みユーザーは読書計画登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.create');

        $response->assertViewHas('books');
    }

    /** @test */
    public function 進行中の読書計画がある書籍は登録画面に表示されない(): void
    {
        $user = User::factory()->create();

        $registeredBook = Book::factory()->create();

        $unregisteredBook = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $registeredBook->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($registeredBook, $unregisteredBook) {
            return ! $books->contains($registeredBook)
                && $books->contains($unregisteredBook);
        });
    }

    /** @test */
    public function 完了済みや期限切れの読書計画がある書籍は登録画面に表示される(): void
    {
        $user = User::factory()->create();

        $completedBook = Book::factory()->create();
        $expiredBook = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $expiredBook->id,
            'target_date' => today()->subDay(),
            'status' => ReadingPlanStatus::Expired,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($completedBook, $expiredBook) {
            return $books->contains($completedBook)
                && $books->contains($expiredBook);
        });
    }

    /** @test */
    public function 未登録の書籍をタイトル順で表示できる(): void
    {
        $user = User::factory()->create();

        $thirdBook = Book::factory()->create([
            'title' => 'さしすせそ',
        ]);

        $firstBook = Book::factory()->create([
            'title' => 'あいうえお',
        ]);

        $secondBook = Book::factory()->create([
            'title' => 'かきくけこ',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($firstBook, $secondBook, $thirdBook) {
            return $books->pluck('id')->all() === [
                $firstBook->id,
                $secondBook->id,
                $thirdBook->id,
            ];
        });
    }

    /** @test */
    public function 認証済みユーザーは読書計画を登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => today()->toDateString(),
        ];

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), $data);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を作成しました。');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $data['target_date'],
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    /** @test */
    public function 同一ユーザーの同じ書籍に進行中の読書計画があると登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => today()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['book_id' => 'この書籍は既に進行中の読書計画が存在します。']);

        $this->assertDatabaseCount('reading_plans', 1);
    }

    /** @test */
    public function 同じ書籍の読書計画が完了済みなら新しい計画を登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => today()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseCount('reading_plans', 2);

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    /** @test */
    public function 同じ書籍の読書計画が期限切れなら新しい計画を登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDay(),
            'status' => ReadingPlanStatus::Expired,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => today()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseCount('reading_plans', 2);

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    /** @test */
    public function 読書計画登録時に書籍が未選択だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => '',
                'target_date' => today()->toDateString(),
            ]);

        $response->assertSessionHasErrors('book_id');
    }

    /** @test */
    public function 読書計画登録時に書籍idが文字列だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => 'あ',
                'target_date' => today()->toDateString(),
            ]);

        $response->assertSessionHasErrors('book_id');
    }

    /** @test */
    public function 読書計画登録時に存在しない書籍だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => 999999,
                'target_date' => today()->toDateString(),
            ]);

        $response->assertSessionHasErrors('book_id');
    }

    /** @test */
    public function 読書計画登録時に期日が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => '',
            ]);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 読書計画登録時に期日が不正な値だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => '不正な値',
            ]);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 読書計画登録時に期日が存在しない日付だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => '2026-02-30',
            ]);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 読書計画登録時に期日が過去日だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => today()->subDay()->toDateString(),
            ]);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した読書計画の編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertOk();
        $response->assertViewIs('reading-plans.edit');

        $response->assertViewHas('readingPlan', $readingPlan);
    }

    /** @test */
    public function 他人が登録した読書計画の編集画面にアクセスすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertForbidden();
    }

    /** @test */
    public function 存在しない読書計画idで編集画面にアクセスすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーは自分の読書計画を更新できる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->toDateString(),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $data = [
            'target_date' => today()->addDay()->toDateString(),
        ];

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), $data);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を更新しました。');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $data['target_date'],
        ]);
    }

    /** @test */
    public function 同じ書籍に別の進行中の計画があると期限切れ計画を更新できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $expiredPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDay(),
            'status' => ReadingPlanStatus::Expired,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $expiredPlan), [
                'target_date' => today()->toDateString(),
            ]);

        $response->assertSessionHasErrors([
            'target_date' => 'この書籍は既に進行中の読書計画が存在します。',
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredPlan->id,
            'target_date' => today()->subDay()->toDateString(),
            'status' => ReadingPlanStatus::Expired->value,
        ]);
    }

    /** @test */
    public function 期限切れ計画の期日を更新すると進行中へ戻る(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->subDay()->toDateString(),
            'status' => ReadingPlanStatus::Expired,
        ]);

        $data = [
            'target_date' => today()->addDay()->toDateString(),
        ];

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), $data);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $data['target_date'],
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    /** @test */
    public function 完了済み計画は更新しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->toDateString(),
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => today()->addDay()->toDateString(),
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => today()->toDateString(),
            'status' => ReadingPlanStatus::Completed->value,
        ]);
    }

    /** @test */
    public function 他人が登録した読書計画を更新しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'target_date' => today()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => today()->addDay()->toDateString(),
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $readingPlan->target_date,
        ]);
    }

    /** @test */
    public function 存在しない読書計画idで更新しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', 999999), [
                'target_date' => today()->toDateString(),
            ]);

        $response->assertNotFound();
    }

    /** @test */
    public function 読書計画更新時に期日が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => '',
            ]);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 読書計画更新時に期日が過去日だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => today()->subDay()->toDateString(),
            ]);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した読書計画を削除できる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を削除しました。');

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /** @test */
    public function 読書計画を削除すると関連通知だけが削除される(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification($readingPlan, 'three_days_before')
        );

        $user->notify(
            new ReadingPlanReminderNotification($otherPlan, 'three_days_before')
        );

        $this->assertDatabaseHas('notifications', [
            'data->plan_id' => $readingPlan->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'data->plan_id' => $readingPlan->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'data->plan_id' => $otherPlan->id,
        ]);
    }

    /** @test */
    public function 他人が登録した読書計画を削除しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /** @test */
    public function 存在しない読書計画idで削除しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した読書計画を完了できる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を完了しました。');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        $readingPlan->refresh();

        $this->assertNotNull($readingPlan->completed_at);
    }

    /** @test */
    public function 他人が登録した読書計画を完了しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
            'completed_at' => null,
        ]);
    }

    /** @test */
    public function 存在しない読書計画idで完了しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', 999999));

        $response->assertNotFound();
    }
}
