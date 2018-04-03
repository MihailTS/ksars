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
        $sites=['google.com','yandex.ru','mail.ru', 'adizes.me'];

        Site::truncate();

        foreach ($sites as $site) {
            factory(Site::class)->create(['url' => $site]);
        }
    }
}
