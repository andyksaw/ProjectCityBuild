<?php

use App\Modules\Servers\Models\Server;
use App\Modules\Servers\GameTypeEnum;
use App\Modules\Servers\Models\ServerCategory;

/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Server::class, function (Faker\Generator $faker) {
    return [
        'name'          => $faker->sentence(),
        'ip'            => $faker->ipv4(),
        'port'          => $faker->numberBetween(20, 8000),
        'display_order' => $faker->numberBetween(1, 15),
        'game_type'     => $faker->randomElement(GameTypeEnum::getValues()),
        'is_port_visible' => true,
        'is_visible'    => true,
        'is_querying'   => true,
    ];
});

$factory->state(Server::class, 'withCategory', function (Faker\Generator $faker) {
    return [
        'server_category_id' => function () {
            return factory(ServerCategory::class)->create()->server_category_id;
        },
    ];
});
