<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => '1',
            'date' => '2023-05-01',
            'start_rest' => '2023-05-01 12:00:00',
            'end_rest' => '2023-05-01 12:30:00',
            'created_at' => '2023-05-01 12:00:00',
            'updated_at' => '2023-05-01 12:30:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '2',
            'date' => '2023-05-01',
            'start_rest' => '2023-05-01 12:00:00',
            'end_rest' => '2023-05-01 12:30:00',
            'created_at' => '2023-05-01 12:00:00',
            'updated_at' => '2023-05-01 12:30:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '3',
            'date' => '2023-05-01',
            'start_rest' => '2023-05-01 12:00:00',
            'end_rest' => '2023-05-01 12:20:00',
            'created_at' => '2023-05-01 12:00:00',
            'updated_at' => '2023-05-01 12:20:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '4',
            'date' => '2023-05-01',
            'start_rest' => '2023-05-01 12:00:00',
            'end_rest' => '2023-05-01 12:10:00',
            'created_at' => '2023-05-01 12:00:00',
            'updated_at' => '2023-05-01 12:10:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '5',
            'date' => '2023-05-01',
            'start_rest' => '2023-05-01 12:00:00',
            'end_rest' => '2023-05-01 12:30:00',
            'created_at' => '2023-05-01 12:00:00',
            'updated_at' => '2023-05-01 12:30:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '6',
            'date' => '2023-05-01',
            'start_rest' => '2023-05-01 12:00:00',
            'end_rest' => '2023-05-01 12:20:00',
            'created_at' => '2023-05-01 12:00:00',
            'updated_at' => '2023-05-01 12:20:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2023-05-02',
            'start_rest' => '2023-05-02 12:00:00',
            'end_rest' => '2023-05-02 12:30:00',
            'created_at' => '2023-05-02 12:00:00',
            'updated_at' => '2023-05-02 12:30:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '2',
            'date' => '2023-05-02',
            'start_rest' => '2023-05-02 12:00:00',
            'end_rest' => '2023-05-02 12:30:00',
            'created_at' => '2023-05-02 12:00:00',
            'updated_at' => '2023-05-02 12:30:00'
        ];
        DB::table('rests')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2023-05-03',
            'start_rest' => '2023-05-03 12:00:00',
            'end_rest' => '2023-05-03 12:30:00',
            'created_at' => '2022-05-03 12:00:00',
            'updated_at' => '2022-05-03 12:30:00'
        ];
        DB::table('rests')->insert($param);
    }
}
