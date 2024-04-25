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
        Schema::create('concert_artists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concerts_id');
            $table->unsignedBigInteger('artists_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('concerts_id')->references('id')->on('concerts')->onDelete('cascade');
            $table->foreign('artists_id')->references('id')->on('artists')->onDelete('cascade');

        });

        Schema::create('artist_genres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artists_id');
            $table->unsignedBigInteger('genres_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('artists_id')->references('id')->on('artists')->onDelete('cascade');
            $table->foreign('genres_id')->references('id')->on('genres')->onDelete('cascade');

        });

        Schema::create('concert_genres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concerts_id');
            $table->unsignedBigInteger('genres_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('concerts_id')->references('id')->on('concerts')->onDelete('cascade');
            $table->foreign('genres_id')->references('id')->on('genres')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concert_artists');
        Schema::dropIfExists('artist_genres');
        Schema::dropIfExists('concert_genres');
    }
};
