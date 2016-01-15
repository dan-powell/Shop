<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(DanPowell\Shop\Models\Option::class, function (Faker\Generator $faker) {
    return [
        'label' => $faker->word,
        'price_modifer' => $faker->randomElement([0.00, $faker->randomFloat(2, -50, 50)]),
    ];
});