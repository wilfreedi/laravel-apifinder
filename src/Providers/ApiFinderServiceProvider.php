<?php

namespace Wilfreedi\ApiFinder\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Wilfreedi\ApiFinder\ApiFinderClient;

class ApiFinderServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/apifinder.php', 'apifinder'
        );

        $this->app->singleton(ApiFinderClient::class, function ($app) {
            $config = $app['config']['apifinder'];

            if(empty($config['base_url']) || empty($config['api_token'])) {
                throw new \InvalidArgumentException('API Base URL или API Token не настроены.');
            }

            return new ApiFinderClient(
                $config['base_url'],
                $config['api_token'],
                $config['timeout'] ?? 30,
                $config['guzzle_options'] ?? []
            );
        });

        $this->app->alias(ApiFinderClient::class, 'apifinder.api');

        // $this->app->singleton(Services\OpenAIService::class, function ($app) {
        //    return $app->make(ApiFinderClient::class)->openAI();
        // });
        // $this->app->singleton(Services\DeepSeekService::class, function ($app) {
        //    return $app->make(ApiFinderClient::class)->deepSeek();
        // });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        if($this->app->runningInConsole()) {
            $this->publishes([
                                 __DIR__ . '/../../config/apifinder.php' => config_path('apifinder.php'),
                             ], 'config');
        }
    }

    /**
     * Get the services provided by the provider.
     * Указываем, какие сервисы провайдер регистрирует (для DeferrableProvider).
     *
     * @return array
     */
    public function provides() {
        return [
            ApiFinderClient::class,
            'apifinder.api',
            // Алиас
            // Services\OpenAIService::class, // Если регистрировали отдельно
            // Services\DeepSeekService::class, // Если регистрировали отдельно
        ];
    }
}