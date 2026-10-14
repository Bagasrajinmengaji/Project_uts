<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->tech = Category::firstOrCreate(['slug' => 'teknologi'], ['name' => 'Teknologi']);
    $this->business = Category::firstOrCreate(['slug' => 'bisnis'], ['name' => 'Bisnis']);
});

test('public can view categories', function () {
    $response = $this->getJson('/api/categories');

    $response->assertOk()
        ->assertJsonStructure(['categories' => [['id', 'name', 'slug']]]);
});

test('public can view published posts and filter by category or search', function () {
    $author = User::factory()->create();

    Post::create([
        'user_id' => $author->id,
        'category_id' => $this->tech->id,
        'title' => 'Belajar Vue 3 Terkini',
        'slug' => 'belajar-vue-3-terkini',
        'content' => 'Konten panduan belajar Vue 3 composition API.',
        'status' => 'published',
    ]);

    Post::create([
        'user_id' => $author->id,
        'category_id' => $this->business->id,
        'title' => 'Bisnis Digital 2026',
        'slug' => 'bisnis-digital-2026',
        'content' => 'Tren pasar digital di masa kini.',
        'status' => 'published',
    ]);

    Post::create([
        'user_id' => $author->id,
        'category_id' => $this->tech->id,
        'title' => 'Draft Artikel Rahasia',
        'slug' => 'draft-artikel-rahasia',
        'content' => 'Ini masih draft dan tidak boleh terlihat di publik.',
        'status' => 'draft',
    ]);

    // Test list all published
    $res = $this->getJson('/api/posts');
    $res->assertOk();
    $data = $res->json('data');
    expect(collect($data)->pluck('slug'))->toContain('belajar-vue-3-terkini')
        ->and(collect($data)->pluck('slug'))->toContain('bisnis-digital-2026')
        ->and(collect($data)->pluck('slug'))->not->toContain('draft-artikel-rahasia');

    // Test filter category
    $resCategory = $this->getJson('/api/posts?category=teknologi');
    $resCategory->assertOk();
    $catData = $resCategory->json('data');
    expect(collect($catData)->pluck('slug'))->toContain('belajar-vue-3-terkini')
        ->and(collect($catData)->pluck('slug'))->not->toContain('bisnis-digital-2026');

    // Test search keyword
    $resSearch = $this->getJson('/api/posts?search=panduan');
    $resSearch->assertOk();
    $searchData = $resSearch->json('data');
    expect(collect($searchData)->pluck('slug'))->toContain('belajar-vue-3-terkini');
});

test('public can view post detail by slug', function () {
    $author = User::factory()->create();
    $post = Post::create([
        'user_id' => $author->id,
        'category_id' => $this->tech->id,
        'title' => 'Detail Artikel Spesifik',
        'slug' => 'detail-artikel-spesifik',
        'content' => 'Isi detail artikel berita lengkap.',
        'status' => 'published',
    ]);

    $response = $this->getJson("/api/posts/{$post->slug}");

    $response->assertOk()
        ->assertJsonPath('post.title', 'Detail Artikel Spesifik')
        ->assertJsonPath('post.slug', 'detail-artikel-spesifik');
});

test('user can register, login, access me and logout', function () {
    // 1. Register
    $registerRes = $this->postJson('/api/register', [
        'name' => 'Mahasiswa Test',
        'email' => 'mahasiswa_test@domain.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registerRes->assertCreated()
        ->assertJsonStructure(['token', 'user']);

    // 2. Login
    $loginRes = $this->postJson('/api/login', [
        'email' => 'mahasiswa_test@domain.com',
        'password' => 'password123',
    ]);

    $loginRes->assertOk()
        ->assertJsonStructure(['token', 'user']);

    $token = $loginRes->json('token');

    // 3. Me
    $meRes = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/me');
    $meRes->assertOk()
        ->assertJsonPath('user.email', 'mahasiswa_test@domain.com');

    // 4. Logout
    $logoutRes = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');
    $logoutRes->assertOk();
});

test('authenticated user can create post with optional image upload', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->create('berita.jpg', 100, 'image/jpeg');

    $response = $this->postJson('/api/posts', [
        'title' => 'Berita Dengan Gambar Sampul',
        'category_id' => $this->tech->id,
        'content' => 'Konten pengujian upload file gambar.',
        'image' => $file,
        'status' => 'published',
    ]);

    $response->assertCreated()
        ->assertJsonPath('post.title', 'Berita Dengan Gambar Sampul')
        ->assertJsonPath('post.status', 'published');

    $post = Post::where('title', 'Berita Dengan Gambar Sampul')->first();
    expect($post)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image);
});

test('owner can update and delete their own post, but unauthorized user receives 403 Forbidden', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $post = Post::create([
        'user_id' => $owner->id,
        'category_id' => $this->tech->id,
        'title' => 'Judul Awal',
        'slug' => 'judul-awal',
        'content' => 'Konten awal.',
        'status' => 'published',
    ]);

    // Unauthorized user attempts update -> 403
    Sanctum::actingAs($otherUser);
    $unauthUpdate = $this->putJson("/api/posts/{$post->id}", [
        'title' => 'Judul Dibajak',
        'category_id' => $this->tech->id,
        'content' => 'Mencoba membajak.',
        'status' => 'published',
    ]);
    $unauthUpdate->assertForbidden();

    // Owner updates successfully
    Sanctum::actingAs($owner);
    $ownerUpdate = $this->putJson("/api/posts/{$post->id}", [
        'title' => 'Judul Telah Direvisi',
        'category_id' => $this->tech->id,
        'content' => 'Konten telah diperbarui dengan benar.',
        'status' => 'published',
    ]);
    $ownerUpdate->assertOk()
        ->assertJsonPath('post.title', 'Judul Telah Direvisi');

    // Unauthorized user attempts delete -> 403
    Sanctum::actingAs($otherUser);
    $unauthDelete = $this->deleteJson("/api/posts/{$post->id}");
    $unauthDelete->assertForbidden();

    // Owner deletes successfully
    Sanctum::actingAs($owner);
    $ownerDelete = $this->deleteJson("/api/posts/{$post->id}");
    $ownerDelete->assertOk();

    expect(Post::find($post->id))->toBeNull();
});
