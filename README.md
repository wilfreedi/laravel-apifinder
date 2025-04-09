<h1 align="center">
  <a href="https://github.com/wilfreedi/laravel-apifinder">
    API Finder
  </a>
</h1>
<p align="center">
  <a href="LICENSE"><img alt="Packagist License" src="https://img.shields.io/packagist/l/wilfreedi/laravel-apifinder"></a>
  <a href="https://packagist.org/packages/wilfreedi/laravel-apifinder"><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/wilfreedi/laravel-apifinder"></a>
  <a href="https://packagist.org/packages/wilfreedi/laravel-apifinder"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/wilfreedi/laravel-apifinder"></a>
</p>
<p align="center">
PHP клиент для взаимодействия с <a target="_blank" href="https://apifinder.ru">API Finder</a>.
</p>

## Установка

```composer require wilfreedi/laravel-apifinder```

## Публикация файла настроек

```php artisan vendor:publish --provider="Wilfreedi\ApiFinder\Providers\ApiFinderServiceProvider" --tag="config"```

### Переменные .env

```
APIFINDER_BASE_URL=https://apifinder.ru/api/v1
APIFINDER_TOKEN=your_secure_bearer_token_here
APIFINDER_TIMEOUT=60
```

## Использование

### Вне Laravel

```php
use Wilfreedi\ApiFinder\ApiFinderClient;

$baseUrl = 'https://apifinder.ru';
$apiToken = 'your_secure_bearer_token_here';
$timeout = 60; // опционально
$guzzleOptions = [ // опционально
    // 'proxy' => 'http://user:pass@host:port'
];

$client = new ApiFinderClient($baseUrl, $apiToken, $timeout, $guzzleOptions);

// Дальнейшее использование
$openaiService = $client->openAI();
// $deepseekService = $client->deepSeek();
```

### Laravel (с фасадом)

```php
use Wilfreedi\ApiFinder\Facades\ApiFinder;

// --- OpenAI Chat ---
try {
    $params = [
        'model'    => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ];
    $response = ApiFinder::openAI()->chat($params);
    print_r($response);
} catch (\Wilfreedi\ApiFinder\Exceptions\ApiException $e) {
    // Обработка ошибок API
    echo "API Error: " . $e->getMessage() . " (Status: " . $e->getCode() . ")\n";
}

// --- OpenAI Transcribe ---
try {
    $filePath = '/path/to/your/audio.mp3';
    $params = [
        'model'    => 'whisper',
        'language' => 'en' // опционально
    ];
    $response = ApiFinder::openAI()->transcribe($filePath, basename($filePath), $params);
    echo "Transcription: " . $response['text'] . "\n";
} catch (\Wilfreedi\ApiFinder\Exceptions\ApiException $e) {
    // Обработка ошибок
     echo "API Error: " . $e->getMessage() . " (Status: " . $e->getCode() . ")\n";
} catch (\InvalidArgumentException $e) {
     // Ошибка файла
     echo "File Error: " . $e->getMessage() . "\n";
}
```
### Laravel (Dependency Injection)

```php
use Illuminate\Http\Request;
use Wilfreedi\ApiFinder\ApiFinderClient;
use Wilfreedi\ApiFinder\Services\OpenAIService; // Если внедрять напрямую

class SomeController
{
    protected ApiFinderClient $apiFinderClient;
    // Или protected OpenAIService $openaiService;

    public function __construct(ApiFinderClient $apiFinderClient /* или OpenAIService $openaiService */) {
        $this->apiFinderClient = $apiFinderClient;
        // $this->openaiService = $openaiService;
    }

    public function handleOpenAI(Request $request) {
        $openaiService = $this->apiFinderClient->openAI();
        try {
            $response = $openaiService->chat($request->input('params'));
            // ...
        } catch (\Wilfreedi\ApiFinder\Exceptions\ApiException $e) {
            // ...
        }
    }
}
```

## Сообщить о проблеме

Если вы обнаружите ошибку или у вас есть предложения по улучшению библиотеки,
пожалуйста [напишите нам](https://github.com/wilfreedi/laravel-apifinder/issues/new/choose)