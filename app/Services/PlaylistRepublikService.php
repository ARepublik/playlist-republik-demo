<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PlaylistRepublikService extends GuzzleRequestService
{
    private $client;

    public function __construct()
    {
        if (Cache::has('access_token')) {
            $token_data = Cache::get('access_token');
        } else {
            $token_client = $this->_createClient('http://api.playlistrepublik.test/');
            $token_data = $this->_doRequest($token_client, 'oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => config('services.playlist-republik.client_id'),
                'client_secret' => config('services.playlist-republik.secret'),
                'scope' => '*',
                'username' => config('services.playlist-republik.username'),
                'password' => config('services.playlist-republik.password'),
            ]);
            Cache::put('access_token', $token_data, $token_data->expires_in);
        }
        $this->client = $this->_createClient('http://api.playlistrepublik.test/api/v1/', [
            'Authorization' => 'Bearer ' . $token_data->access_token
        ]);
    }

    public function getPlaylists()
    {
        return $this->_doRequest($this->client, 'curator/playlist/playlists');
    }

    public function createUserTrack(array $data)
    {
        return $this->_doRequest($this->client, 'curator/user-track', 'POST', [], $data);
    }

    public function createOrder(array $data)
    {
        return $this->_doRequest($this->client, 'curator/order', 'POST', [], $data);
    }

    public function createWebhook(array $data)
    {
        return $this->_doRequest($this->client, 'api-client/webhook', 'POST', [], $data);
    }

    public function updatePayment(string $key)
    {
        return $this->_doRequest($this->client, 'payment/' . $key, 'PUT');
    }
}
