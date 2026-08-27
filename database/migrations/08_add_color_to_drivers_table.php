<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('nationality');
        });

        App\Models\Driver::query()
            ->whereNull('color')
            ->get()
            ->each(function (App\Models\Driver $driver) {
                $driver->update([
                    'color' => App\Support\Color::fromString($driver->name.' '.$driver->surname),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
