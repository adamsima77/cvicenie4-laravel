<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('notes')->insert([
            [
                'user_id' => 2,
                'title' => 'Laravel Seeder',
                'body' => 'Ako vytvoriť seeder v Laraveli?',
                'status' => 'published',
                'is_pinned' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 3,
                'title' => 'Shopping List',
                'body' => 'Mlieko, chlieb, vajcia',
                'status' => 'draft',
                'is_pinned' => false,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 2,
                'title' => 'Project Idea',
                'body' => 'Nápad na nový startup...',
                'status' => 'archived',
                'is_pinned' => false,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 4,
                'title' => 'Meeting Notes',
                'body' => 'Poznámky zo stretnutia tímu.',
                'status' => 'published',
                'is_pinned' => false,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 5,
                'title' => 'Study Plan',
                'body' => 'Pripraviť sa na skúšku z databáz.',
                'status' => 'draft',
                'is_pinned' => false,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 6,
                'title' => 'Workout Routine',
                'body' => 'Beh 5 km a cvičenie doma.',
                'status' => 'published',
                'is_pinned' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 7,
                'title' => 'Travel Ideas',
                'body' => 'Navštíviť Tatry cez leto.',
                'status' => 'draft',
                'is_pinned' => false,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'user_id' => 8,
                'title' => 'Book Notes',
                'body' => 'Poznámky z prečítanej knihy.',
                'status' => 'archived',
                'is_pinned' => false,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);
    }
}
