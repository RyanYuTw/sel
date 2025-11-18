<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->store('images', 'public');
        
        return response()->json([
            'location' => Storage::url($path)
        ]);
    }
}
