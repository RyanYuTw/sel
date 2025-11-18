<?php

namespace Database\Seeders;

use App\Models\Handbook;
use Illuminate\Database\Seeder;

class HandbookSeeder extends Seeder
{
    public function run(): void
    {
        Handbook::create([
            'title' => '數學基礎概念',
            'content' => '<h2>加法與減法</h2><p>學習基本的加法和減法運算...</p>'
        ]);

        Handbook::create([
            'title' => '國語閱讀理解',
            'content' => '<h2>閱讀技巧</h2><p>培養良好的閱讀習慣...</p>'
        ]);
    }
}