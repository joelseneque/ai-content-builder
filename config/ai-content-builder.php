<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    |
    | Your OpenAI API key for authenticating requests.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Model
    |--------------------------------------------------------------------------
    |
    | The OpenAI model to use for content generation.
    |
    */

    'model' => env('OPENAI_MODEL', 'gpt-4'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoint
    |--------------------------------------------------------------------------
    |
    | The OpenAI API endpoint for chat completions.
    |
    */

    'endpoint' => env('OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),

];
