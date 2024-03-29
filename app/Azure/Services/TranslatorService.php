<?php

namespace App\Azure\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TranslatorService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.cognitive.microsofttranslator.com',
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * @param string $text
     * @param string $targetLanguage
     * @return string
     */
    public function translate(string $text, string $targetLanguage): string
    {
        try {
            $response = $this->client->post('/translate?api-version=3.0&to=' . $targetLanguage, [
                'headers' => [
                    'Ocp-Apim-Subscription-Key' => config('azure.translator.subscription_key'),
                    'Ocp-Apim-Subscription-Region' => config('azure.translator.region'),
                ],
                'json' => [['text' => $text]],
            ]);
            $body = json_decode(mb_convert_encoding($response->getBody()->getContents(), 'UTF-8', 'UTF-8'), true);

            return $body[0]['translations'][0]['text'];
        } catch (\Throwable $e) {
            Log::alert('Client: {client} | Text: {text} | Target language: {target_language}', [
                'client' => $this->client,
                'text' => $text,
                'targetLanguage' => $targetLanguage,
            ]);

            return 'Something went wrong... Please, try later.';
        }
    }
}
