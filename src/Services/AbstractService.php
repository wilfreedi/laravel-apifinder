<?php

namespace Wilfreedi\ApiFinder\Services;

use Wilfreedi\ApiFinder\Client;

abstract class AbstractService
{
    protected Client $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }
}