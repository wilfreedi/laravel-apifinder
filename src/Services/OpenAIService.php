<?php

namespace Wilfreedi\ApiFinder\Services;

use GuzzleHttp\RequestOptions;
use Wilfreedi\ApiFinder\Exceptions\ApiException;
use Wilfreedi\ApiFinder\Exceptions\AuthenticationException;

class OpenAIService extends AbstractService
{
    /**
     * Отправляет запрос Chat Completion через API агрегатор.
     *
     * @param array $params Параметры запроса ('model', 'messages', 'temperature', etc.)
     * @return array Ответ от API агрегатора (уже обработанный Client::handleResponse)
     * @throws ApiException | AuthenticationException
     */
    public function chat(array $params): array {
        $endpoint = 'openai/v1/chat/completions';
        return $this->client->request('POST', $endpoint, [
            RequestOptions::JSON => $params
        ]);
    }

    /**
     * Отправляет запрос на транскрипцию аудио через API агрегатор.
     *
     * @param string|resource|\Psr\Http\Message\StreamInterface $file Путь к файлу, ресурс или поток
     * @param string $filename Имя файла (важно для multipart)
     * @param array $params Другие параметры ('model', 'language', etc.)
     * @return array Ответ от API агрегатора
     * @throws ApiException | AuthenticationException | \InvalidArgumentException
     */
    public function transcribe($file, string $filename, array $params): array {
        $endpoint = 'openai/v1/audio/transcriptions';

        $multipartData = [];

        $fileContent = null;
        if(is_string($file) && file_exists($file)) {
            $fileContent = fopen($file, 'r');
        } elseif(is_resource($file) || $file instanceof \Psr\Http\Message\StreamInterface) {
            $fileContent = $file;
        } else {
            throw new \InvalidArgumentException('Некорректный тип файла. Ожидается путь, ресурс или StreamInterface.');
        }

        if(!$fileContent) {
            throw new \InvalidArgumentException('Не удалось открыть файл для транскрипции.');
        }

        $multipartData[] = [
            'name'     => 'file',
            'contents' => $fileContent,
            'filename' => $filename
        ];

        foreach ($params as $key => $value) {
            if($value !== null) {
                $multipartData[] = [
                    'name'     => $key,
                    'contents' => (string)$value
                ];
            }
        }

        return $this->client->request('POST', $endpoint, [
            RequestOptions::MULTIPART => $multipartData
        ]);
    }
}