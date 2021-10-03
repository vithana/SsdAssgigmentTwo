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

    public function getDrive()
    {
        $files = $this->ListFolders();
        return view('list', ['files' => $files]);
    }

    public function ListFolders()
    {

        $files = [];
        $results = $this->drive->files->listFiles();
        if (count($results->getFiles()) == 0) {
            print "No files found.\n";
        } else {
            foreach ($results->getFiles() as $file) {
                $files = Arr::prepend($files, $file->getName());
            }
        }
        return $files;
    }

    function uploadFile(Request $request)
    {
        if ($request->isMethod('GET')) {
            $files = $this->ListFolders();
            return view('list', ['files' => $files]);
        } else {
            $this->createFile($request->file('file'));
            $files = $this->ListFolders();
            return view('list', ['files' => $files]);
        }
    }

    function createStorageFile($storage_path)
    {
        $this->createFile($storage_path);
    }

    function createFile($file, $parent_id = null)
    {
        $name = gettype($file) === 'object' ? $file->getClientOriginalName() : $file;
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'parent' => $parent_id ? $parent_id : 'root'
        ]);

        $content = gettype($file) === 'object' ?  File::get($file) : Storage::get($file);
        $mimeType = gettype($file) === 'object' ? File::mimeType($file) : Storage::mimeType($file);

        $file = $this->drive->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);
    }

    function deleteFileOrFolder($id)
    {
        try {
            $this->drive->files->delete($id);
        } catch (Exception $e) {
            return false;
        }
    }

    function createFolder($folder_name)
    {
        $folder_meta = new Google_Service_Drive_DriveFile(array(
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder'
        ));
        $folder = $this->drive->files->create($folder_meta, array(
            'fields' => 'id'
        ));
        return $folder->id;
    }
}
