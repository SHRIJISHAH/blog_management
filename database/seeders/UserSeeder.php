<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => Hash::make('Test@123'),
        ]);

        // Create additional users
        $users = User::factory()->count(5)->create();
        
        // Add the test user to the collection
        $allUsers = $users->push($testUser);

        // Create blogs for each user
        foreach ($allUsers as $user) {
            $blogTitles = [
                'Blog 1',
                'Blog 2',
                'Blog 3',
                'Blog 4',
                'Blog 5',
                'Blog 6'
            ];


            $blogDescriptions = [
                'This is blog 1.',
                'This is blog 2.',
                'This is blog 3.',
                'This is blog 4.',
                'This is blog 5.',
                'This is blog 6.'
            ];

            $numBlogs = rand(2, 3);
            for ($i = 0; $i < $numBlogs; $i++) {
                $randomIndex = array_rand($blogTitles);
                Blog::create([
                    'user_id' => $user->id,
                    'title' => $blogTitles[$randomIndex] . " - " . $user->name,
                    'description' => $blogDescriptions[$randomIndex],
                ]);
                
                unset($blogTitles[$randomIndex]);
                unset($blogDescriptions[$randomIndex]);
                $blogTitles = array_values($blogTitles);
                $blogDescriptions = array_values($blogDescriptions);
            }
        }

        $blogs = Blog::all();
        foreach ($blogs as $blog) {
            $likeCount = rand(0, 4);
            $randomUsers = $allUsers->random(min($likeCount, $allUsers->count()));
            
            foreach ($randomUsers as $user) {
                // Avoid self-likes and duplicate likes
                if ($user->id !== $blog->user_id && 
                    !$blog->likes()->where('user_id', $user->id)->exists()) {
                    $blog->likes()->create(['user_id' => $user->id]);
                }
            }
        }
    }
}