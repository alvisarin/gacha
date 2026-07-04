<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gacha.com',
            'password' => Hash::make('password'),
            'coins' => 10000,
            'is_admin' => true,
        ]);

        // Create Regular User
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@gacha.com',
            'password' => Hash::make('password'),
            'coins' => 500,
            'is_admin' => false,
        ]);

        // Create Active Event
        $event = Event::create([
            'title' => 'Summer Festival Gacha',
            'is_active' => true,
        ]);

        // Create Rewards (Total = 100%)
        Reward::create([
            'event_id' => $event->id,
            'name' => 'Legendary Dragon',
            'drop_rate' => 1.00,
        ]);

        Reward::create([
            'event_id' => $event->id,
            'name' => 'Rare Phoenix',
            'drop_rate' => 19.00,
        ]);

        Reward::create([
            'event_id' => $event->id,
            'name' => 'Common Slime',
            'drop_rate' => 80.00,
        ]);

        // Create another event for variety
        $event2 = Event::create([
            'title' => 'Winter Wonderland Gacha',
            'is_active' => true,
        ]);

        Reward::create([
            'event_id' => $event2->id,
            'name' => 'Ice Queen (SSR)',
            'drop_rate' => 0.50,
        ]);

        Reward::create([
            'event_id' => $event2->id,
            'name' => 'Frost Knight (SR)',
            'drop_rate' => 4.50,
        ]);

        Reward::create([
            'event_id' => $event2->id,
            'name' => 'Snow Fairy (R)',
            'drop_rate' => 20.00,
        ]);

        Reward::create([
            'event_id' => $event2->id,
            'name' => 'Ice Crystal (C)',
            'drop_rate' => 75.00,
        ]);

        $this->command->info('Seeding completed!');
        $this->command->info('Admin: admin@gacha.com / password');
        $this->command->info('User: user@gacha.com / password');
    }
}
