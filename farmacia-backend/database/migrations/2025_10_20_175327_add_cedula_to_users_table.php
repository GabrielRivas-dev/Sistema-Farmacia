<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Si existe la columna dni, la renombramos a cedula
            if (Schema::hasColumn('users', 'dni')) {
                $table->renameColumn('dni', 'cedula');
            } else {
                // Si no existe, creamos cedula
                $table->string('cedula')->nullable()->after('telefono');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cedula')) {
                $table->renameColumn('cedula', 'dni');
            }
        });
    }
};