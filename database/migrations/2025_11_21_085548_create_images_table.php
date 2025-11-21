<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique()->comment('圖片內容 SHA256 hash');
            $table->text('path')->comment('圖片儲存路徑');
            $table->string('filename')->comment('原始檔名');
            $table->string('mime_type', 50)->comment('MIME 類型');
            $table->unsignedInteger('size')->default(0)->comment('檔案大小(bytes)');
            $table->unsignedInteger('width')->default(0)->comment('圖片寬度');
            $table->unsignedInteger('height')->default(0)->comment('圖片高度');
            $table->unsignedBigInteger('created_at')->default(0)->comment('建立時間');
            $table->unsignedBigInteger('updated_at')->default(0)->comment('更新時間');

            $table->index('hash');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
