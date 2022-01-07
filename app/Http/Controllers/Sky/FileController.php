<?php


namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class FileController extends Controller
{
    public function __invoke(Request $request, $slug = null)
    {
        $api = new Api('files');

        $guzzle = new \GuzzleHttp\Client();
        $url = 'https://login.microsoftonline.com/312d0f20-964c-45bb-85e4-a22e1eef3fb1/oauth2/token?api-version=1.0';
        $token = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => 'a276353b-a5a1-4204-b0ef-ac643535bd29',
                'client_secret' => 'QVNVW.NsI@ibT.WdIgWS4tKkN/BqO805',
                'resource' => 'https://graph.microsoft.com/',
                'grant_type' => 'client_credentials',
            ],
        ])->getBody()->getContents());
        $accessToken = $token->access_token;

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $user = $graph->createRequest("GET", "/me")->setReturnType(Model\User::class)->execute();

        return view('files.index', compact('api', 'user'));
    }
}
