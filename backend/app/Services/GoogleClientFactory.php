<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use RuntimeException;

class GoogleClientFactory
{
    public function make(): Client
    {
        $clientId = config('google.client_id');
        $clientSecret = config('google.client_secret');
        $refreshToken = config('google.refresh_token');

        if (! $clientId || ! $clientSecret || ! $refreshToken) {
            throw new RuntimeException(
                'Google API credentials are missing. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REFRESH_TOKEN in .env'
            );
        }

        $client = new Client;
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE,
            Drive::DRIVE_FILE,
        ]);

        $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($token['error'])) {
            throw new RuntimeException(
                'Google OAuth refresh failed: '.($token['error_description'] ?? $token['error']).
                '. Re-generate GOOGLE_REFRESH_TOKEN via OAuth Playground.'
            );
        }

        if (empty($token['access_token'])) {
            throw new RuntimeException(
                'Google OAuth did not return an access token. Check client ID/secret and refresh token.'
            );
        }

        $client->setAccessToken($token);

        return $client;
    }

    public function sheets(): Sheets
    {
        return new Sheets($this->make());
    }

    public function drive(): Drive
    {
        return new Drive($this->make());
    }
}
