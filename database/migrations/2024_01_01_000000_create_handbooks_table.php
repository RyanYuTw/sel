<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handbooks', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->comment('年度');
            $table->integer('grade')->comment('年級 1-6');
            $table->enum('semester', ['上', '下'])->comment('學期');
            $table->string('lesson', 32)->comment('課別');
            $table->longText('content')->nullable()->comment('內文');
            $table->tinyInteger('status')->default(0)->comment('狀態 0-未發布 1-已發布');
            $table->unsignedBigInteger('published_at')->default(0)->comment('發布時間');
            $table->unsignedBigInteger('created_at')->default(0)->comment('建立時間');
            $table->unsignedBigInteger('updated_at')->default(0)->comment('更新時間');

            $table->index(['year', 'grade', 'semester', 'lesson']);
            $table->index('status');
            $table->index('published_at');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handbooks');
    }
};
