<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('trainers')->insert([
            ['id' => 1, 'name' => 'Andi Pratama', 'email' => 'andi@fitflow.test', 'phone' => '081200000010', 'specialization' => 'Strength Training', 'years_experience' => 6, 'certification' => 'NSCA-CPT, ACE Personal Trainer', 'bio' => 'Spesialis kekuatan otot dan body recomposition.', 'hourly_rate' => 150000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Sari Wulandari', 'email' => 'sari@fitflow.test', 'phone' => '081200000011', 'specialization' => 'Yoga & Pilates', 'years_experience' => 8, 'certification' => 'RYT-500, Pilates Mat Certified', 'bio' => 'Instruktur yoga & pilates berpengalaman.', 'hourly_rate' => 175000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Budi Santoso', 'email' => 'budi@fitflow.test', 'phone' => '081200000012', 'specialization' => 'HIIT & Cardio', 'years_experience' => 4, 'certification' => 'ACSM-CPT', 'bio' => 'Coach cardio dan HIIT.', 'hourly_rate' => 130000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('classes')->insert([
            ['id' => 1, 'name' => 'HIIT Burn', 'description' => 'Latihan high-intensity interval untuk membakar kalori cepat.', 'category' => 'cardio', 'difficulty_level' => 'intermediate', 'duration_minutes' => 45, 'max_capacity' => 15, 'price' => 75000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Power Lifting Basics', 'description' => 'Pengenalan teknik squat, deadlift, dan bench press.', 'category' => 'strength', 'difficulty_level' => 'beginner', 'duration_minutes' => 60, 'max_capacity' => 10, 'price' => 100000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Vinyasa Yoga Flow', 'description' => 'Aliran yoga dinamis untuk fleksibilitas dan ketenangan.', 'category' => 'mind-body', 'difficulty_level' => 'beginner', 'duration_minutes' => 60, 'max_capacity' => 20, 'price' => 80000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Stretch & Mobility', 'description' => 'Sesi peregangan untuk meningkatkan range of motion.', 'category' => 'flexibility', 'difficulty_level' => 'beginner', 'duration_minutes' => 30, 'max_capacity' => 18, 'price' => 50000.00, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('class_trainer')->insert([
            ['class_id' => 1, 'trainer_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['class_id' => 1, 'trainer_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['class_id' => 2, 'trainer_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['class_id' => 3, 'trainer_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['class_id' => 4, 'trainer_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['class_id' => 4, 'trainer_id' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);


        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Super Admin', 'email' => 'admin@fitflow.test', 'password' => '$2y$12$l3aACjLzp/bZ2nkhBHsS2uQCiDd/hYEsKwPJ7asPLbBB8XHPju/4S', 'phone' => '081200000001', 'role' => 'admin', 'trainer_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Andi Pratama', 'email' => 'trainer.andi@fitflow.test', 'password' => '$2y$12$ECXvhKR9qh.3Z9K0NpLLpuE1Jq9Q.pTEJhIlOSV5FDQxSHhghlPBO', 'phone' => '081200000010', 'role' => 'trainer', 'trainer_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Sari Wulandari', 'email' => 'trainer.sari@fitflow.test', 'password' => '$2y$12$1oEfNIFftXaDG1z1Mkv3uub7IpbPXydOzXg7cW/gK8rhtcWMtYEnC', 'phone' => '081200000011', 'role' => 'trainer', 'trainer_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Rina Member', 'email' => 'member@fitflow.test', 'password' => '$2y$12$BGQmIEu8zV.v1lqC1DxihOSBNsBnqyQ8NtysPjOgxYfruLMjJHKBC', 'phone' => '081200000020', 'role' => 'member', 'trainer_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Doni Member', 'email' => 'doni@fitflow.test', 'password' => '$2y$12$94GdIEhAhHildwZiBGW8qefygQXFALSz0oWHXCg./YSxvVRazkOyO', 'phone' => '081200000021', 'role' => 'member', 'trainer_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Hilmy', 'email' => 'hilmyraihankindy@gmail.com', 'password' => '$2y$12$m.R390dDEYO77.ualixpq.Uxc4CA990dBKxlsOYbXIVryoN0ssVOe', 'phone' => '081259621505', 'role' => 'member', 'trainer_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('class_sessions')->insert([
            ['id' => 1, 'class_id' => 1, 'trainer_id' => 3, 'session_date' => '2026-05-04', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'location' => 'Studio A', 'capacity' => 15, 'booked_count' => 1, 'price' => 75000.00, 'status' => 'scheduled', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'class_id' => 2, 'trainer_id' => 1, 'session_date' => '2026-05-04', 'start_time' => '17:00:00', 'end_time' => '18:00:00', 'location' => 'Weight Room', 'capacity' => 10, 'booked_count' => 0, 'price' => 100000.00, 'status' => 'scheduled', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'class_id' => 3, 'trainer_id' => 2, 'session_date' => '2026-05-05', 'start_time' => '06:30:00', 'end_time' => '07:30:00', 'location' => 'Yoga Studio', 'capacity' => 20, 'booked_count' => 0, 'price' => 80000.00, 'status' => 'scheduled', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'class_id' => 4, 'trainer_id' => 3, 'session_date' => '2026-05-06', 'start_time' => '18:30:00', 'end_time' => '19:00:00', 'location' => 'Studio B', 'capacity' => 18, 'booked_count' => 0, 'price' => 50000.00, 'status' => 'scheduled', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'class_id' => 1, 'trainer_id' => 1, 'session_date' => '2026-05-07', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'location' => 'Studio A', 'capacity' => 15, 'booked_count' => 0, 'price' => 75000.00, 'status' => 'scheduled', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'class_id' => 3, 'trainer_id' => 2, 'session_date' => '2026-05-08', 'start_time' => '17:30:00', 'end_time' => '18:30:00', 'location' => 'Yoga Studio', 'capacity' => 20, 'booked_count' => 0, 'price' => 80000.00, 'status' => 'scheduled', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('bookings')->insert([
            ['id' => 1, 'user_id' => 4, 'session_id' => 1, 'booking_code' => 'BK-20260503-SPQR', 'booking_date' => '2026-05-03 04:28:25', 'status' => 'confirmed', 'payment_method' => 'transfer', 'payment_status' => 'paid', 'total_price' => 75000.00, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
