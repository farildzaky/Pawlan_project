<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\GymClass;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@fitflow.test',
            'password' => Hash::make('password'),
            'phone' => '081200000001',
            'role' => 'admin',
        ]);

        // Trainers (master)
        $t1 = Trainer::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@fitflow.test',
            'phone' => '081200000010',
            'specialization' => 'Strength Training',
            'years_experience' => 6,
            'certification' => 'NSCA-CPT, ACE Personal Trainer',
            'bio' => 'Spesialis kekuatan otot dan body recomposition.',
            'hourly_rate' => 150000,
            'is_active' => true,
        ]);
        $t2 = Trainer::create([
            'name' => 'Sari Wulandari',
            'email' => 'sari@fitflow.test',
            'phone' => '081200000011',
            'specialization' => 'Yoga & Pilates',
            'years_experience' => 8,
            'certification' => 'RYT-500, Pilates Mat Certified',
            'bio' => 'Instruktur yoga & pilates berpengalaman.',
            'hourly_rate' => 175000,
            'is_active' => true,
        ]);
        $t3 = Trainer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@fitflow.test',
            'phone' => '081200000012',
            'specialization' => 'HIIT & Cardio',
            'years_experience' => 4,
            'certification' => 'ACSM-CPT',
            'bio' => 'Coach cardio dan HIIT.',
            'hourly_rate' => 130000,
            'is_active' => true,
        ]);

        // Trainer user accounts
        User::create([
            'name' => $t1->name,
            'email' => 'trainer.andi@fitflow.test',
            'password' => Hash::make('password'),
            'phone' => $t1->phone,
            'role' => 'trainer',
            'trainer_id' => $t1->id,
        ]);
        User::create([
            'name' => $t2->name,
            'email' => 'trainer.sari@fitflow.test',
            'password' => Hash::make('password'),
            'phone' => $t2->phone,
            'role' => 'trainer',
            'trainer_id' => $t2->id,
        ]);

        // Classes
        $c1 = GymClass::create([
            'name' => 'HIIT Burn',
            'description' => 'Latihan high-intensity interval untuk membakar kalori cepat.',
            'category' => 'cardio',
            'difficulty_level' => 'intermediate',
            'duration_minutes' => 45, 'max_capacity' => 15, 'price' => 75000, 'is_active' => true,
        ]);
        $c2 = GymClass::create([
            'name' => 'Power Lifting Basics',
            'description' => 'Pengenalan teknik squat, deadlift, dan bench press.',
            'category' => 'strength',
            'difficulty_level' => 'beginner',
            'duration_minutes' => 60, 'max_capacity' => 10, 'price' => 100000, 'is_active' => true,
        ]);
        $c3 = GymClass::create([
            'name' => 'Vinyasa Yoga Flow',
            'description' => 'Aliran yoga dinamis untuk fleksibilitas dan ketenangan.',
            'category' => 'mind-body',
            'difficulty_level' => 'beginner',
            'duration_minutes' => 60, 'max_capacity' => 20, 'price' => 80000, 'is_active' => true,
        ]);
        $c4 = GymClass::create([
            'name' => 'Stretch & Mobility',
            'description' => 'Sesi peregangan untuk meningkatkan range of motion.',
            'category' => 'flexibility',
            'difficulty_level' => 'beginner',
            'duration_minutes' => 30, 'max_capacity' => 18, 'price' => 50000, 'is_active' => true,
        ]);

        // Pivot
        $c1->trainers()->sync([$t3->id, $t1->id]);
        $c2->trainers()->sync([$t1->id]);
        $c3->trainers()->sync([$t2->id]);
        $c4->trainers()->sync([$t2->id, $t3->id]);

        // Sessions
        $sd = [
            ['c' => $c1, 't' => $t3, 'd' => 1, 's' => '07:00', 'e' => '07:45', 'l' => 'Studio A'],
            ['c' => $c2, 't' => $t1, 'd' => 1, 's' => '17:00', 'e' => '18:00', 'l' => 'Weight Room'],
            ['c' => $c3, 't' => $t2, 'd' => 2, 's' => '06:30', 'e' => '07:30', 'l' => 'Yoga Studio'],
            ['c' => $c4, 't' => $t3, 'd' => 3, 's' => '18:30', 'e' => '19:00', 'l' => 'Studio B'],
            ['c' => $c1, 't' => $t1, 'd' => 4, 's' => '07:00', 'e' => '07:45', 'l' => 'Studio A'],
            ['c' => $c3, 't' => $t2, 'd' => 5, 's' => '17:30', 'e' => '18:30', 'l' => 'Yoga Studio'],
        ];
        $created = [];
        foreach ($sd as $s) {
            $created[] = ClassSession::create([
                'class_id' => $s['c']->id,
                'trainer_id' => $s['t']->id,
                'session_date' => now()->addDays($s['d'])->toDateString(),
                'start_time' => $s['s'],
                'end_time' => $s['e'],
                'location' => $s['l'],
                'capacity' => $s['c']->max_capacity,
                'price' => $s['c']->price,
                'status' => 'scheduled',
            ]);
        }

        // Members
        $m1 = User::create([
            'name' => 'Rina Member',
            'email' => 'member@fitflow.test',
            'password' => Hash::make('password'),
            'phone' => '081200000020',
            'role' => 'member',
        ]);
        User::create([
            'name' => 'Doni Member',
            'email' => 'doni@fitflow.test',
            'password' => Hash::make('password'),
            'phone' => '081200000021',
            'role' => 'member',
        ]);

        // Sample booking
        Booking::create([
            'user_id' => $m1->id,
            'session_id' => $created[0]->id,
            'booking_code' => 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'booking_date' => now(),
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'total_price' => $created[0]->price,
        ]);
        $created[0]->increment('booked_count');
    }
}
