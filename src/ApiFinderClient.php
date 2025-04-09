<?php

namespace Wilfreedi\ApiFinder;

use Wilfreedi\ApiFinder\Services\DeepSeekService;
use Wilfreedi\ApiFinder\Services\OpenAIService;

class ApiFinderClient
{
    protected Client $client;

    /**
     * @param string $baseUrl Базовый URL API агрегатора
     * @param string $apiToken Bearer токен
     * @param int $timeout Таймаут запроса
     * @param array $guzzleOptions Доп. опции Guzzle
     */
    public function __construct(
        string $baseUrl,
        string $apiToken,
        int    $timeout = 30,
        array  $guzzleOptions = []
    ) {
        $this->client = new Client($baseUrl, $apiToken, $timeout, $guzzleOptions);
    }

    /**
     * Возвращает сервис для работы с OpenAI API через агрегатор.
     */
    public function openAI(): OpenAIService {
        return new OpenAIService($this->client);
    }

    /**
     * Возвращает сервис для работы с DeepSeek API через агрегатор.
     */
    public function deepSeek(): DeepSeekService {
        return new DeepSeekService($this->client);
    }

    /**
     * Позволяет получить доступ к основному HTTP клиенту, если нужно сделать кастомный запрос.
     */
    public function getClient(): Client {
        return $this->client;
    }
}