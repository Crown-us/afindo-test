<?php

use App\Enums\UserRole;
use App\Models\User;

test('guest bisa membuka beranda & katalog, tapi diarahkan ke login untuk dashboard dan admin', function () {
    $this->get('/')->assertOk()->assertSee('Daftar gratis');
    $this->get('/courses')->assertOk();
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/admin')->assertRedirect('/login');
});

test('user yang sudah login tetap bisa kembali ke beranda', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertOk()->assertDontSee('Daftar gratis');
});

test('admin bisa membuka area admin dan halaman publik', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/courses')->assertOk()->assertSee('Kelola Course');
});

test('student bisa membuka katalog tapi ditolak dari area admin', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/courses')->assertOk();
    $this->actingAs($student)->get('/admin')->assertForbidden();
});

test('dashboard mengarahkan user ke halaman awal sesuai role', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/dashboard')->assertRedirect(route('admin.dashboard'));

    $this->actingAs(User::factory()->create())
        ->get('/dashboard')->assertRedirect(route('courses.index'));
});

test('login admin langsung masuk ke area admin', function () {
    $admin = User::factory()->admin()->create();

    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
    $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
});

test('register selalu membuat student dan field role dari request diabaikan', function () {
    $this->post('/register', [
        'name' => 'Penyusup',
        'email' => 'penyusup@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    expect(User::where('email', 'penyusup@example.com')->first()->role)
        ->toBe(UserRole::Student);
});
