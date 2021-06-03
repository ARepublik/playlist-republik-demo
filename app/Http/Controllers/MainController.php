<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\PlaylistRepublikService;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class MainController extends Controller
{

    public function index()
    {
        $playlist_service = new PlaylistRepublikService();

        $playlists = $playlist_service->getPlaylists()->data;

        shuffle($playlists);
        $playlists = (array) array_slice($playlists, 0, 3);

        foreach ($playlists as &$playlist) {
            $playlist->our_price = $playlist->price + rand(1, 4);
        }

        $track_data = [
            'name' => 'Test Track',
            'url' => 'https://open.spotify.com/track/3t5CI2xqUBytrGJGsBqmUV?si=GeRun24USKiGZ3oFyp5LKw',
            'genre_id' => 12,
            'external_user_id' => 1
        ];
        $track = $playlist_service->createUserTrack($track_data);

        $selected_playlists = array_reduce($playlists, function ($accum, $playlist) {
            $accum[] = $playlist->id;
            return $accum;
        }, []);

        $order_data = [
            'email' => 'test@test.com',
            'user_track_uuid' => $track->data->uuid,
            'playlists' => $selected_playlists,
        ];
        $order = $playlist_service->createOrder($order_data);

        $amount = array_reduce($playlists, function ($accum, $playlist) {
            $accum += $playlist->our_price;
            return $accum;
        }, 0);
        $fee = array_reduce($playlists, function ($accum, $playlist) {
            $accum += $playlist->price;
            return $accum;
        }, 0);

        Stripe::setApiKey('xxxx');
        $intent_data = [
            'payment_method_types' => ['card'],
            'amount' => $amount * 100,
            'currency' => 'usd',
            'application_fee_amount' => $fee * 100,
            'on_behalf_of' => 'acct_xxxx',
            'transfer_data' => ['destination' => 'acct_xxxx'],
            'metadata[api_client]' => config('services.playlist-republik.username'),
            'metadata[service]' => 'playlist-republik',
            'metadata[order]' => $order->data->uuid
        ];
        $payment_intent = PaymentIntent::create($intent_data);

        return view('welcome', [
            'payment_intent_secret' => $payment_intent->secret,
            'stripe_key' => 'xxxx',
        ]);
    }

    public function setupWebhooks()
    {
        $playlist_service = new PlaylistRepublikService();

        $webhook_data = [
            'url' => 'http://playlistrepubliktest.test/api/playlist-republik-webhook',
            'events' => ['order-started', 'order-updated'],
        ];
        $webhook = $playlist_service->createWebhook($webhook_data);
        dd($webhook);
    }

    public function webhook(Request $request)
    {
        Log::info('--Webhook Recieved--');
        Log::info($request->event);
        Log::info($request->order);
        Log::info('Send Email...');
    }
}


