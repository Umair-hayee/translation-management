<?php

namespace Database\Seeders;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Umair Hayee',
        //     'email' => 'umair@example.com',
        // ]);

        User::create([
            'name' => 'Umair Hayee',
            'password' => Hash::make('12345678'),
            'email' => 'umair@sdf.com'
        ]);

        //
        Locale::create(['code' => 'en', 'name' => 'English']);
        Locale::create(['code' => 'fr', 'name' => 'French']);
        Locale::create(['code' => 'es', 'name' => 'Spanish']);

        Tag::create(['name' => 'mobile']);
        Tag::create(['name' => 'desktop']);
        Tag::create(['name' => 'web']);
    }
}
