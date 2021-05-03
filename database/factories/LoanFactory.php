<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Loan;
use Faker\Generator as Faker;

$factory->define(Loan::class, function (Faker $faker) {
    return [
        'borrower_id' => 1,
        'description' => $faker->text,
        'amount'=> 10000,
        'terms'=> 4,
        'interest_rate'=> 5
    ];
});
