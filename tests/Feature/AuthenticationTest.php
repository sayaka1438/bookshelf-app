<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストユーザーは会員登録画面を表示できる(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function ゲストユーザーは会員登録ができる(): void
    {
        $data = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $data);

        $response->assertRedirect(route('books.index'));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    /** @test */
    public function 会員登録時に名前が空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function 会員登録時に名前が1文字でも登録できる(): void
    {
        $data = [
            'name' => 'あ',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $data);

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    /** @test */
    public function 会員登録時に名前は255文字まで登録できる(): void
    {
        $data = [
            'name' => str_repeat('あ', 255),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $data);

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    /** @test */
    public function 会員登録時に名前が256文字以上だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => str_repeat('あ', 256),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function 会員登録時にメールアドレスが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => '山田太郎',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 会員登録時にメールアドレスが不正な値だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => '山田太郎',
            'email' => '不正な値',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 会員登録時にすでに登録されているメールアドレスだとバリデーションエラーになる(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->post(route('register'), [
            'name' => '山田太郎',
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 会員登録時にパスワードが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 会員登録時にパスワードが7文字以下だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 会員登録時に確認用パスワードが不一致だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function ゲストユーザーはログイン画面を表示できる(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function 正しい認証情報でログインできる(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('books.index'));

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function ログイン時にメールアドレスが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 存在しないメールアドレスではログインできない(): void
    {
        User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => 'notfound@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /** @test */
    public function 間違ったパスワードではログインできない(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /** @test */
    public function 認証済みユーザーはログアウトできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect('/');

        $this->assertGuest();
    }

    /** @test */
    public function 認証済みユーザーは会員登録画面にアクセスするとリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('register'));

        $response->assertRedirect(route('books.index'));

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function 認証済みユーザーはログイン画面にアクセスするとリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('login'));

        $response->assertRedirect(route('books.index'));

        $this->assertAuthenticatedAs($user);
    }
}
