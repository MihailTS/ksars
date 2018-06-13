<?php

use App\Site;
use Faker\Generator as Faker;

$factory->define(Site::class, function (Faker $faker) {

    return [
        'url' => 'http://cryptoniya.ru'
    ];
});
