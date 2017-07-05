<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstagramController extends Controller
{
    public function getMedia (Request $request)
    {
        //validate post variables
    	$this->validate($request, [
    		'access_token' => 'required'
    	]);

        //make get request to instagram api
    	$client = new \GuzzleHttp\Client();
        $url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token='.$request->access_token;
        $res = $client->get($url);

        //return response
        return json_decode($res->getBody())->data;
    }

    public function saveMedia ($data)
    {
        foreach ($data as $d) {
            $image_url = $d->images->standard_resolution->url;
            $src = __DIR__ . '/../../../public/images/'.$d->user->username.'/'.$d->id.'.jpg';
            //copy($image_url, $src);

            //Get the file
            $content = file_get_contents($image_url, false, stream_context_create(['ssl' => ['verify_peer' => false]]));
            //Store in the filesystem.

            if (!is_dir(__DIR__ . '/../../../public/images/'.$d->user->username.'/')) {
                mkdir(__DIR__ . '/../../../public/images/'.$d->user->username.'/', 0777, true);

                $fp = fopen($src, "w");
                fwrite($fp, $content);
                fclose($fp);
            }

            $d->src = $src;
        }

        return $data;
    }
}
