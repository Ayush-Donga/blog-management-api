<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Blog;
use App\Models\Like;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: 6 Users
        $users = [
            ['name' => 'Ayush Donga', 'email' => 'ayush@example.com'],
            ['name' => 'Bhumi Donga', 'email' => 'bhumi@example.com'],
            ['name' => 'Admin', 'email' => 'admin@example.com'],
            ['name' => 'Raj Donga', 'email' => 'raj@example.com'],
            ['name' => 'Nimisha Donga', 'email' => 'nimisha@example.com'],
            ['name' => 'Ashish Donga', 'email' => 'ashish@example.com'],
        ];

        $createdUsers = collect();
        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => bcrypt('password'),
                ]
            );
            $createdUsers->push($user);
        }

        $this->command->info('6 Users created!');

        // Step 2: 16 Blogs
        for ($i = 1; $i <= 16; $i++) {
            $randomUser = $createdUsers->random();

            $blog = Blog::create([
                'title' => "Blog $i: " . Str::random(6),
                'description' => "This is blog number $i. Created for testing filters, search, and likes.",
                'user_id' => $randomUser->id,
                'image_path' => "blogs/dummy_$i.jpg", // Fake path
            ]);

            // Step 3: Random Likes
            if ($i % 3 === 0) {
                $likers = $createdUsers->random(rand(1, 4));
                foreach ($likers as $liker) {
                    Like::firstOrCreate([
                        'user_id' => $liker->id,
                        'likeable_id' => $blog->id,
                        'likeable_type' => Blog::class,
                    ]);
                }
            }
        }

        $this->command->info('16 Blogs + Likes seeded!');
    }
}