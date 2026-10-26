<?php

return [
    // Never permit simulation mutations in a production environment.
    'enabled' => env('CHAIN_SIMULATION_ENABLED', true),
    'price' => 100000,
    'rewards' => [1 => 50000, 2 => 25000, 3 => 10000, 4 => 5000],
    'minimum_withdrawal' => 50000,
];
