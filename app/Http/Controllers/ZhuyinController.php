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
                    
                    if (!empty($zhuyinList)) {
                        return response()->json(['zhuyin' => $zhuyinList]);
                    }
                    
                    // 如果沒有注音，檢查是否為異體字
                    foreach ($data['heteronyms'] as $heteronym) {
                        if (isset($heteronym['definitions'])) {
                            foreach ($heteronym['definitions'] as $definition) {
                                if (isset($definition['def']) && preg_match('/\x{300c}(.)\x{300d}\x{7684}\x{7570}\x{9ad4}\x{5b57}/u', $definition['def'], $matches)) {
                                    // 查詢正體字
                                    $correctWord = $matches[1];
                                    $correctResponse = Http::get("https://www.moedict.tw/uni/{$correctWord}");
                                    if ($correctResponse->successful()) {
                                        $correctData = $correctResponse->json();
                                        if (isset($correctData['heteronyms'])) {
                                            foreach ($correctData['heteronyms'] as $correctHeteronym) {
                                                if (isset($correctHeteronym['bopomofo'])) {
                                                    $zhuyinList[] = $correctHeteronym['bopomofo'];
                                                }
                                            }
                                        }
                                    }
                                    break 2;
                                }
                            }
                        }
                    }
                    
                    if (!empty($zhuyinList)) {
                        return response()->json(['zhuyin' => $zhuyinList]);
                    }
                }
            }
            
            return response()->json(['zhuyin' => []]);
        } catch (\Exception $e) {
            \Log::error('Zhuyin API error: ' . $e->getMessage());
            return response()->json(['zhuyin' => []]);
        }
    }
}