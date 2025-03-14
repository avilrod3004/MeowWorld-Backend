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
        Schema::table('cat_posts', function (Blueprint $table) {
            $table->dropForeign(['cat_id']);
            $table->dropForeign(['post_id']);

            $table->foreign('cat_id')->references('id')->on('cats')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cat_posts', function (Blueprint $table) {
            $table->dropForeign(['cat_id']);
            $table->dropForeign(['post_id']);

            $table->foreign('cat_id')->references('id')->on('cats');
            $table->foreign('post_id')->references('id')->on('posts');
        });
    }

};
