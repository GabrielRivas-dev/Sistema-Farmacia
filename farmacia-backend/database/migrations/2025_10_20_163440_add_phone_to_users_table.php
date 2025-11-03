<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono')->nullable()->after('email');
            $table->string('dni')->nullable()->after('telefono');
            $table->date('fecha_nacimiento')->nullable()->after('dni');
            $table->string('direccion')->nullable()->after('fecha_nacimiento');
            $table->boolean('activo')->default(true)->after('remember_token');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'dni', 'fecha_nacimiento', 'direccion', 'activo']);
            $table->dropSoftDeletes();
        });
    }
};