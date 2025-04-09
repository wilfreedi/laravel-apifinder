<?php

namespace Wilfreedi\ApiFinder;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Wilfreedi\ApiFinder\Exceptions\ApiException;
use Wilfreedi\ApiFinder\Exceptions\AuthenticationException;

class Client
{
    protected GuzzleClient $httpClient;
    protected string $baseUrl;
    protected string $apiToken;
    protected int $timeout;

    /**
     * @param string $baseUrl Базовый URL https://apifinder.ru
     * @param string $apiToken Bearer токен для аутентификации
     * @param int $timeout Таймаут запроса в секундах
     * @param array $guzzleOptions Дополнительные опции для Guzzle (например, 'proxy', 'verify')
     */
    public function __construct(
        string $baseUrl,
        string $apiToken,
        int    $timeout = 30,
        array  $guzzleOptions = []
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiToken = $apiToken;
        $this->timeout = $timeout;

        $defaultOptions = [
            'base_uri'                  => $this->baseUrl,
            RequestOptions::TIMEOUT     => $this->timeout,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::HEADERS     => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json'
            ],
        ];

        $this->httpClient = new GuzzleClient(array_merge($defaultOptions, $guzzleOptions));
    }

    /**
     * Выполняет запрос к API
     *
     * @param string $method HTTP метод (GET, POST, PUT, DELETE)
     * @param string $endpoint Конечная точка API (e.g., '/openai/v1/chat/completions')
     * @param array $options Опции запроса Guzzle (e.g., ['json' => ...], ['multipart' => ...])
     * @return array Декодированный JSON ответ
     * @throws ApiException | AuthenticationException
     */
    public function request(string $method, string $endpoint, array $options = []): array {
        if(isset($options[RequestOptions::JSON]) && !isset($options[RequestOptions::HEADERS]['Content-Type'])) {
            $options[RequestOptions::HEADERS]['Content-Type'] = 'application/json';
        }

        try {
            $response = $this->httpClient->request($method, ltrim($endpoint, '/'), $options);
            return $this->handleResponse($response);
        } catch (GuzzleRequestException $e) {
            $message = "Ошибка сети при запросе к {$this->baseUrl}{$endpoint}: " . $e->getMessage();
            if($e->hasResponse()) {
                try {
                    return $this->handleResponse($e->getResponse());
                } catch (ApiException $apiException) {
                    throw $apiException;
                }
            }
            throw new ApiException($message, $e->getCode(), $e);
        } catch (\Throwable $th) {
            throw new ApiException("Неожиданная ошибка при запросе к {$endpoint}: " . $th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * Обрабатывает ответ от API.
     *
     * @param ResponseInterface $response
     * @return array
     * @throws ApiException | AuthenticationException
     */
    protected function handleResponse(ResponseInterface $response): array {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Не удалось декодировать ответ API (Статус: {$statusCode}). Тело ответа: " . $body, $statusCode);
        }

        $isSuccess = $data['success'] ?? null; // Или другой флаг успеха
        if($statusCode >= 400 || $isSuccess === false) {
            $errorMessage = $data['message'] ?? 'Неизвестная ошибка API';

            if($statusCode === 401 || $statusCode === 403) {
                throw new AuthenticationException($errorMessage, $statusCode);
            }

            $errors = $data['errors'] ?? $data['data'] ?? null;
            if(is_array($errors) && !empty($errors)) {
                $errorMessage .= ': ' . json_encode($errors);
            }

            throw new ApiException($errorMessage, $statusCode);
        }

        if(isset($data['success']) && $isSuccess === true && isset($data['data'])) {
            return $data['data'];
        }

        return $data;
    }
}