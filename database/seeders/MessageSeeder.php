<?php

namespace Database\Seeders;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Message::factory()->count(500)->create();

        $i = 1;
        $date = Carbon::parse('2024-04-31 04:48:24');
        while ($i < 101) {
            $date->addMinute($i);
            Message::create([
                'room_id' => 3,
                'user_id' => 1,
                'content' => "page editors now use Lorem Ipsum as their default model text, and a search for 'lorem i $i",
                'created_at' => $date,
                'file_path' => null
            ]);
            $i++;
        }
    }
}
