<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LineProController extends Controller
{
    private const LINE_OAUTH_URI = 'https://access.line.me/oauth2/v2.1/authorize?';
    private const LINE_TOKEN_API_URI = 'https://api.line.me/oauth2/v2.1/';
    private const LINE_PROFILE_API_URI = 'https://api.line.me/v2/';
    private $client_id;
    private $client_secret;
    private $callback_url;

    public function __construct() {
        $this->client_id = Config('line.client_id');
        $this->client_secret = Config('line.client_secret');
        $this->callback_url = Config('line.callback_url');
    }

    // line認証のコールバック時にクエリーで得られたcodeからtoken情報を取得し返す処理のapi
    public function get_lineUser(Request $request) {
        try {

            $code = $request->input('code');
            $token_info = $this->fetchTokenInfo($code);

            $result = [
                "result" => "ok",
                "code" => $code,
                "token" => $token_info
            ];

        }
        catch(\Exception $e) {
            $result = [
                'result' => "No",
                'error' => [
                    'messages' => [$e->getMessage()]
                ],
            ];
        }

        return response()->json($result);
    } 

    private function fetchTokenInfo($code)
    {
        $base_uri = ['base_uri' => self::LINE_TOKEN_API_URI];
        $method = 'POST';
        $path = 'token';
        $headers = ['headers' => 
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];
        $form_params = ['form_params' => 
            [
                'code'          => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri'  => $this->callback_url,
                'grant_type'    => 'authorization_code'
            ]
        ];
        $token_info = $this->sendRequest($base_uri, $method, $path, $headers, $form_params);
        return $token_info;
    }

    private function sendRequest($base_uri, $method, $path, $headers, $form_params = null)
    {
        try {
            $client = new Client($base_uri);
            if ($form_params) {
                $response = $client->request($method, $path, $form_params, $headers);
            } else {
                $response = $client->request($method, $path, $headers);
            }
        } catch(\Exception $ex) {
            //　例外処理
        }
        return json_decode($response->getbody()->getcontents());
    }
}
