<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZhuyinController extends Controller
{
    public function getZhuyin(Request $request)
    {
        $word = $request->input('word');
        
        try {
            $response = Http::get("https://www.moedict.tw/uni/{$word}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['heteronyms'])) {
                    $zhuyinList = [];
                    
                    foreach ($data['heteronyms'] as $heteronym) {
                        if (isset($heteronym['bopomofo'])) {
                            $zhuyinList[] = $heteronym['bopomofo'];
                        }
                    }
                    
                    return response()->json(['zhuyin' => $zhuyinList]);
                }
            }
            
            return response()->json(['zhuyin' => []], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}