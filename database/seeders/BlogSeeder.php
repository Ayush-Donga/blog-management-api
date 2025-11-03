<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Blog;
use App\Models\Like;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        for ($i = 1; $i <= 10; $i++) { // 10 blogs for testing
            $blog = Blog::create([
                'title' => "Blog Title $i",
                'description' => "Description for blog $i. This is a test blog.",
                'user_id' => $user->id,
            ]);

            // Add random likes (for most liked filter testing)
            if ($i % 2 === 0) {
                Like::create([
                    'likeable_id' => $blog->id,
                    'likeable_type' => Blog::class,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}