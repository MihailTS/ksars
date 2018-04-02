<?php

use App\Site;
use Faker\Generator as Faker;

$factory->define(Site::class, function (Faker $faker) {

    return [
        'url' => 'adizes.me'
    ];
});
