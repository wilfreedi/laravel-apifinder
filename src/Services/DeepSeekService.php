<?php

namespace Wilfreedi\ApiFinder\Services;

use GuzzleHttp\RequestOptions;
use Wilfreedi\ApiFinder\Exceptions\ApiException;
use Wilfreedi\ApiFinder\Exceptions\AuthenticationException;

class DeepSeekService extends AbstractService
{
    /**
     * Отправляет запрос Chat Completion для DeepSeek через API агрегатор.
     *
     * @param array $params Параметры запроса
     * @return array Ответ от API агрегатора
     * @throws ApiException | AuthenticationException
     */
    public function chat(array $params): array {
        $endpoint = '/deepseek/v1/chat/completions';

        return $this->client->request('POST', $endpoint, [
            RequestOptions::JSON => $params
        ]);
    }

}