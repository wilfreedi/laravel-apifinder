<?php

namespace Wilfreedi\ApiFinder\Facades;

use Illuminate\Support\Facades\Facade;
use Wilfreedi\ApiFinder\ApiFinderClient;
use Wilfreedi\ApiFinder\Client;
use Wilfreedi\ApiFinder\Services\DeepSeekService;
use Wilfreedi\ApiFinder\Services\OpenAIService;

/**
 * @method static OpenAIService openAI()
 * @method static DeepSeekService deepSeek()
 * @method static Client getClient()
 *
 * @see \Wilfreedi\ApiFinder\ApiFinderClient
 */
class ApiFinder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return ApiFinderClient::class; // Или алиас 'aggregator.api'
    }
}