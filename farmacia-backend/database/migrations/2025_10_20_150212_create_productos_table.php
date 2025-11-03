<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 8, 2);
            $table->decimal('precio_original', 8, 2)->nullable();
            $table->integer('descuento')->nullable()->default(0);
            $table->string('imagen')->nullable();
            $table->string('laboratorio');
            $table->integer('stock')->default(0);
            $table->boolean('disponible')->default(true);
            $table->boolean('en_promocion')->default(false);
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('es_favorito')->default(false);
            $table->string('categoria');
            $table->string('principio_activo')->nullable();
            $table->string('presentacion')->nullable();
            $table->string('concentracion')->nullable();
            $table->text('indicaciones')->nullable();
            $table->text('contraindicaciones')->nullable();
            $table->text('efectos_secundarios')->nullable();
            $table->string('codigo_barras')->unique()->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('stock_minimo')->default(10);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para mejor performance
            $table->index('categoria');
            $table->index('laboratorio');
            $table->index('disponible');
            $table->index('en_promocion');
            $table->index(['disponible', 'en_promocion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};