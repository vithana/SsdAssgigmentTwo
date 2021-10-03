<?php

namespace App\Http\Controllers;

use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DriveController extends Controller
{
    private $drive;
    public function __construct(Google_Client $client)
    {
        $this->middleware(function ($request, $next) use ($client) {
            $accessToken = [
                'access_token' => auth()->user()->token,
                'created' => auth()->user()->created_at->timestamp,
                'expires_in' => auth()->user()->expires_in,
                'refresh_token' => auth()->user()->refresh_token
            ];

            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                }
                auth()->user()->update([
                    'token' => $client->getAccessToken()['access_token'],
                    'expires_in' => $client->getAccessToken()['expires_in'],
                    'created_at' => $client->getAccessToken()['created'],
                ]);
            }

            $client->refreshToken(auth()->user()->refresh_token);
            $this->drive = new Google_Service_Drive($client);
            return $next($request);
        });
    }

    public function getDrive(){
         $files =$this->ListFolders();
        return view('list', ['files' => $files]);

    }

    public function ListFolders(){

        $files = [];
        $results = $this->drive->files->listFiles();
        if (count($results->getFiles()) == 0) {
            print "No files found.\n";
        } else {
            foreach ($results->getFiles() as $file) {
                $files = Arr::prepend($files,$file->getName() );
            }
        }
        return $files;

    }

    
}
