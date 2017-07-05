<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpotifyController extends Controller
{
    public function recommendations (Request $request)
    {
    	//validate post variables
    	$this->validate($request, [
    		'access_token' => 'required',
    		'features' => 'required'
    	]);

    	$authorization = 'Bearer '.$request->access_token;

    	//make get request to spotify api
    	$client = new \GuzzleHttp\Client();
        $url = 'https://api.spotify.com/v1/recommendations?seed_artists=7Ln80lUS6He07XvHI8qqHH,6vWDO969PvNqNYHIOW5v0m&seed_tracks=60RWYBk24Z6lHxMcWD0oh0,3JI2mIJto0JuYbrq87aFqu,5VjyvjotqBBwknYvYKGayj&target_energy='.$request->features['energy'].'&target_acousticness='.$request->features['acousticness'].'&target_valence='.$request->features['valence'].'&target_danceability='.$request->features['danceability'].'&limit=1';
        //$res = $client->get($url);
        $res = $client->request('GET', $url, [
        	'headers' => [
        		'Authorization' => $authorization
        	]
        ]);
        $tracks = json_decode($res->getBody())->tracks;

        return $res->getBody();
    }

    public function playTracks (Request $request) {

    	$this->validate($request, [
    		'tracks' => 'required',
    		'access_token' => 'required'
    	]);

    	$uris = [];

    	foreach ($request->tracks as $t) {
    		array_push($uris, 'spotify:track:'.$t['id']);
    	}
    	
    	$url = 'https://api.spotify.com/v1/me/player/play';
    	$authorization = 'Bearer '.$request->access_token;

    	$client = new \GuzzleHttp\Client();
    	$res = $client->request('PUT', $url, [
    		'headers' => [
    			'Authorization' => $authorization
    		],
    		'json' => ["uris" => $uris, "offset" => ["position" => 0]]
    	]);
    }

    public function player (Request $request)
    {
    	$this->validate($request, [
    		'access_token' => 'required',
    		'action' => 'required'
    	]);

    	$authorization = 'Bearer '.$request->access_token;
    	$url = 'https://api.spotify.com/v1/me/player/'.$request->action;
    	if ($request->action == 'play' || $request->action == 'pause') {
    		$method = 'PUT';
    	} else {
    		$method = 'POST';
    	}

    	$client = new \GuzzleHttp\Client();
    	$res = $client->request($method, $url, [
    		'headers' => [
    			'Authorization' => $authorization
    		]
    	]);

    	return $res->getBody();
    }

    public function seek (Request $request)
    {
    	$this->validate($request, [
    		'access_token' => 'required',
    		'position' => 'required',
    	]);

    	$authorization = 'Bearer '.$request->access_token;
    	$url = 'https://api.spotify.com/v1/me/player/seek?position_ms=45000';
    	$client = new \GuzzleHttp\Client();
    	$res = $client->request('PUT', $url, [
    		'headers' => [
    			'Authorization' => $authorization
    		]
    	]);

    	return $res->getBody();
    }
}
