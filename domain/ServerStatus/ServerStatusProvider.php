<?php

namespace Domain\ServerStatus;

use Illuminate\Support\ServiceProvider;

class ServerStatusProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        ServerQueryAdapterFactoryContract::class, ServerQueryAdapterFactory::class,
    ];
}
