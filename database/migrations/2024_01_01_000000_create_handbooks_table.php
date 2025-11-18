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
            $table->longText('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handbooks');
    }
};
