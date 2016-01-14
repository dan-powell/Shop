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
        'title' => $faker->word,
        'type' => $faker->sentence(rand(1, 3)),
        'description' => $faker->paragraph(3),
        'config' => $faker->paragraph(1),
    ];
});