<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('nationality');
        });

        App\Models\Team::query()
            ->whereNull('color')
            ->get()
            ->each(function (App\Models\Team $team) {
                $team->update([
                    'color' => App\Support\Color::fromString($team->name),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
