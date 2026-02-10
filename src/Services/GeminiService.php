<?php

namespace Wilfreedi\ApiFinder\Services;

use GuzzleHttp\RequestOptions;
use Wilfreedi\ApiFinder\Exceptions\ApiException;
use Wilfreedi\ApiFinder\Exceptions\AuthenticationException;

class GeminiService extends AbstractService
{
    /**
     * Отправляет запрос на генерацию контента (чат, текст, vision).
     *
     * @param string $model Название модели (например, 'gemini-1.5-pro' или 'gemini-1.5-flash')
     * @param array $params Тело запроса ('contents', 'generationConfig', etc.)
     * @return array Ответ API
     * @throws ApiException | AuthenticationException
     */
    public function generateContent(string $model, array $params): array
    {
        // Gemini использует синтаксис с двоеточием для действий
        $endpoint = "gemini/v1beta/models/{$model}:generateContent";

        return $this->client->request('POST', $endpoint, [
            RequestOptions::JSON => $params
        ]);
    }

    /**
     * Загружает файл через File API (для использования в промптах).
     *
     * @param string|resource|\Psr\Http\Message\StreamInterface $file Путь к файлу или ресурс
     * @param string $mimeType MIME-тип файла (например, 'image/jpeg', 'application/pdf')
     * @param string|null $displayName Отображаемое имя файла (опционально)
     * @return array Ответ с uri файла
     * @throws ApiException | AuthenticationException | \InvalidArgumentException
     */
    public function uploadFile($file, ?string $mimeType = null, ?string $displayName = null): array
    {
        // Эндпоинт для загрузки файлов
        $endpoint = 'gemini/v1beta/upload/files';

        // 1. Подготовка файла (логика идентична твоему OpenAIService)
        $fileContent = null;
        if (is_string($file) && file_exists($file)) {
            // Если mime_type не передан, определяем его сами
            if ($mimeType === null) {
                $mimeType = mime_content_type($file);
            }
            $fileContent = fopen($file, 'r');
        } elseif (is_resource($file) || $file instanceof \Psr\Http\Message\StreamInterface) {
            $fileContent = $file;
        } else {
            throw new \InvalidArgumentException('Некорректный тип файла. Ожидается путь, ресурс или StreamInterface.');
        }

        // Если все еще null (например, передан ресурс, но тип не указан), ставим дефолтный
        if ($mimeType === null) {
            $mimeType = 'application/octet-stream';
        }

        if (!$fileContent) {
            throw new \InvalidArgumentException('Не удалось открыть файл для загрузки.');
        }

        // 2. Метаданные для Gemini (отправляются как JSON в поле 'metadata' или 'file')
        // В зависимости от реализации прокси, часто достаточно отправить просто файл и параметры
        $fileMetadata = ['mime_type' => $mimeType];
        if ($displayName) {
            $fileMetadata['display_name'] = $displayName;
        }

        // В multipart запросе к Gemini обычно отправляют сам файл и (опционально) метаданные.
        // Google API специфичен, но через прокси часто работает упрощенная схема.
        // Стандартный подход - отправить файл в поле 'file'.

        $multipartData = [];

        $multipartData[] = [
            'name'     => 'file',
            'contents' => $fileContent,
            'headers'  => ['Content-Type' => $mimeType] // Важно указать mime-type в заголовке части
        ];

        // Если API прокси поддерживает передачу метаданных отдельным полем
        $multipartData[] = [
            'name' => 'file', // Иногда требуется json с метаданными под тем же именем или 'metadata'
            'contents' => json_encode(['file' => $fileMetadata]),
            'headers' => ['Content-Type' => 'application/json']
        ];

        return $this->client->request('POST', $endpoint, [
            RequestOptions::MULTIPART => $multipartData
        ]);
    }

    /**
     * Получение информации о конкретной модели.
     */
    public function getModel(string $model): array
    {
        $endpoint = "gemini/v1beta/models/{$model}";

        return $this->client->request('GET', $endpoint);
    }

    /**
     * Список доступных моделей.
     */
    public function listModels(): array
    {
        return $this->client->request('GET', 'gemini/v1beta/models');
    }
}