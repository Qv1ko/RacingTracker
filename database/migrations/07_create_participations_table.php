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
        Schema::create('participations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('race_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->integer('position')->nullable();
            $table->string('status', 10);
            $table->double('driverPoints');
            $table->double('uncertainty');
            $table->double('teamPoints')->nullable();

            $table->unique(['driver_id', 'team_id', 'race_id']);
            $table->index('driver_id');
            $table->index('team_id');
            $table->index('race_id');
            $table->index('position');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
