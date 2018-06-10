<?php

use App\Site;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $sites=[
            'http://cryptoniya.ru'
        ];

        Site::truncate();

        foreach ($sites as $site) {
            factory(Site::class)->create(['url' => $site]);
        }
    }
}
