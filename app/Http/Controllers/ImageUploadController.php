<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|max:2048'
            ]);

            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                return response()->json(['error' => '檔案上傳失敗'], 400);
            }

            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                return response()->json(['error' => '無法讀取檔案'], 500);
            }

            $hash = hash('sha256', $content);
            $image = Image::where('hash', $hash)->first();

            if ($image) {
                $image->updated_at = time();
                $image->save();
            } else {
                $path = $file->store('images', 'public');
                if (!$path) {
                    return response()->json(['error' => '檔案儲存失敗'], 500);
                }

                $imageSize = @getimagesize($file->getRealPath());

                $image = Image::create([
                    'hash' => $hash,
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'width' => $imageSize[0] ?? 0,
                    'height' => $imageSize[1] ?? 0,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
            }

            return response()->json([
                'location' => Storage::url($image->path)
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => '上傳失敗: ' . $e->getMessage()], 500);
        }
    }
}
