<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function uploadToSpace(){
    $localFilePath = public_path('test.txt');
    $fileContents = file_get_contents($localFilePath);

    Storage::disk('do_spaces')->put('test.txt', $fileContents);

    return response()->json([
        'message' => 'File uploaded successfully',
    ]);
}
}
