<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Demo User
        $user = User::firstOrCreate(
            ['email' => 'penulis@berita.com'],
            [
                'name' => 'Bagas Penulis',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'editor@berita.com'],
            [
                'name' => 'Siti Editor',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Categories
        $categoriesData = [
            ['name' => 'Teknologi', 'slug' => 'teknologi'],
            ['name' => 'Bisnis & Ekonomi', 'slug' => 'bisnis-ekonomi'],
            ['name' => 'Hiburan', 'slug' => 'hiburan'],
            ['name' => 'Olahraga', 'slug' => 'olahraga'],
            ['name' => 'Gaya Hidup', 'slug' => 'gaya-hidup'],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], ['name' => $cat['name']]);
        }

        // 3. Create Sample Posts
        $samplePosts = [
            [
                'user_id' => $user->id,
                'category_id' => $categories['teknologi']->id,
                'title' => 'Perkembangan Kecerdasan Buatan dan Dampaknya di Masa Depan',
                'slug' => 'perkembangan-kecerdasan-buatan-dan-dampaknya-di-masa-depan',
                'content' => 'Kecerdasan Buatan (Artificial Intelligence) terus berkembang dengan pesat di berbagai sektor industri. Dari otomatisasi tugas rutin hingga penciptaan solusi medis terkini, AI membuka peluang besar sekaligus tantangan etika baru dalam dunia kerja modern.',
                'status' => 'published',
            ],
            [
                'user_id' => $user->id,
                'category_id' => $categories['bisnis-ekonomi']->id,
                'title' => 'Strategi Startup Lokal Meraih Pendanaan di Era Digital',
                'slug' => 'strategi-startup-lokal-meraih-pendanaan-di-era-digital',
                'content' => 'Ekosistem startup di Indonesia menunjukkan ketahanan yang luar biasa di tengah dinamika pasar global. Fokus pada profitabilitas berkelanjutan dan unit ekonomi yang sehat menjadi kunci utama menarik investor ventura.',
                'status' => 'published',
            ],
            [
                'user_id' => $user2->id,
                'category_id' => $categories['olahraga']->id,
                'title' => 'Persiapan Timnas Menjelang Kualifikasi Piala Dunia',
                'slug' => 'persiapan-timnas-menjelang-kualifikasi-piala-dunia',
                'content' => 'Skuad Garuda menggelar pemusatan latihan intensif guna mempersiapkan laga krusial mendatang. Pelatih menggarisbawahi pentingnya disiplin taktik dan kebugaran fisik pemain.',
                'status' => 'published',
            ],
            [
                'user_id' => $user->id,
                'category_id' => $categories['gaya-hidup']->id,
                'title' => 'Tips Menjaga Keseimbangan Work-Life di Era Remote Work',
                'slug' => 'tips-menjaga-keseimbangan-work-life-di-era-remote-work',
                'content' => 'Bekerja dari rumah menawarkan fleksibilitas tinggi, namun tanpa batasan yang jelas, burnout bisa mengintai. Menetapkan jadwal kerja rutin dan istirahat berkualitas menjadi solusi efektif.',
                'status' => 'draft',
            ],
        ];

        foreach ($samplePosts as $postData) {
            Post::firstOrCreate(['slug' => $postData['slug']], $postData);
        }
    }
}
