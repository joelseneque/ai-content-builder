<?php

namespace Pqd\AiContentBuilder;

use Statamic\Fieldtypes\Bard;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'hotFile' => __DIR__.'/../vite.hot',
        'publicDirectory' => 'resources/dist',
        'input' => [
            'resources/js/addon.js',
        ],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function bootAddon(): void
    {
        $this
            ->bootConfig()
            ->bootBardFields()
            ->bootProvideToScripts();
    }

    protected function bootConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai-content-builder.php', 'ai-content-builder');

        $this->publishes([
            __DIR__.'/../config/ai-content-builder.php' => config_path('ai-content-builder.php'),
        ], 'ai-content-builder-config');

        return $this;
    }

    protected function bootBardFields(): static
    {
        Bard::appendConfigFields([
            'ai_prompt' => [
                'display' => __('AI System Prompt'),
                'instructions' => __('System prompt that defines how the AI should generate content for this field. This sets the context and behavior for the AI.'),
                'type' => 'textarea',
                'width' => 100,
            ],
            'ai_instructions' => [
                'display' => __('AI User Instructions'),
                'instructions' => __('Instructions shown to users when using the AI Content Builder. Helps guide users on how to use this feature.'),
                'type' => 'textarea',
                'width' => 100,
            ],
            'ai_word_limit' => [
                'display' => __('Max AI Word Response Limit'),
                'instructions' => __('Maximum words users can request from AI (0-1000). Users can choose any amount up to this limit. Set to 0 for no limit.'),
                'type' => 'integer',
                'default' => 250,
                'width' => 50,
            ],
        ]);

        return $this;
    }

    protected function bootProvideToScripts(): static
    {
        Statamic::provideToScript([
            'aiContentBuilder' => [
                'enabled' => ! empty(config('ai-content-builder.api_key')),
            ],
        ]);

        return $this;
    }
}
