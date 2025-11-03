<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'precio_original',
        'descuento',
        'imagen',
        'laboratorio',
        'stock',
        'disponible',
        'en_promocion',
        'requiere_receta',
        'es_favorito',
        'categoria',
        'principio_activo',
        'presentacion',
        'concentracion',
        'indicaciones',
        'contraindicaciones',
        'efectos_secundarios',
        'codigo_barras',
        'fecha_vencimiento',
        'stock_minimo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_original' => 'decimal:2',
        'stock' => 'integer',
        'disponible' => 'boolean',
        'en_promocion' => 'boolean',
        'requiere_receta' => 'boolean',
        'es_favorito' => 'boolean',
        'descuento' => 'integer',
        'stock_minimo' => 'integer',
        'fecha_vencimiento' => 'datetime'  // Cambiado de 'date' a 'datetime'
    ];

    /**
     * Scope para productos disponibles
     */
    public function scopeDisponible($query)
    {
        return $query->where('disponible', true)
                    ->where('stock', '>', 0);
    }

    /**
     * Scope para productos en promoción
     */
    public function scopeEnPromocion($query)
    {
        return $query->where('en_promocion', true)
                    ->where('disponible', true);
    }

    /**
     * Scope para búsqueda por nombre, descripción o laboratorio
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('laboratorio', 'LIKE', "%{$termino}%")
                    ->orWhere('principio_activo', 'LIKE', "%{$termino}%");
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para productos que requieren receta
     */
    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    /**
     * Scope para productos sin receta
     */
    public function scopeSinReceta($query)
    {
        return $query->where('requiere_receta', false);
    }

    /**
     * Verificar si el stock es bajo
     */
    public function getStockBajoAttribute()
    {
        return $this->stock <= $this->stock_minimo;
    }

    /**
     * Calcular precio con descuento
     */
    public function getPrecioConDescuentoAttribute()
    {
        if ($this->descuento > 0) {
            return $this->precio - ($this->precio * $this->descuento / 100);
        }
        return $this->precio;
    }

    /**
     * Verificar si el producto está vencido
     */
    public function getEstaVencidoAttribute()
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }
        
        // Convertir a Carbon si no lo es ya
        $fechaVencimiento = $this->fecha_vencimiento instanceof Carbon 
            ? $this->fecha_vencimiento 
            : Carbon::parse($this->fecha_vencimiento);
            
        return $fechaVencimiento->isPast();
    }

    /**
     * Días hasta el vencimiento
     */
    public function getDiasHastaVencimientoAttribute()
    {
        if (!$this->fecha_vencimiento) {
            return null;
        }
        
        // Convertir a Carbon si no lo es ya
        $fechaVencimiento = $this->fecha_vencimiento instanceof Carbon 
            ? $this->fecha_vencimiento 
            : Carbon::parse($this->fecha_vencimiento);
            
        return now()->diffInDays($fechaVencimiento, false);
    }

    /**
     * Accesor para fecha_vencimiento formateada
     */
    public function getFechaVencimientoFormateadaAttribute()
    {
        if (!$this->fecha_vencimiento) {
            return 'No especificada';
        }
        
        return $this->fecha_vencimiento->format('d/m/Y');
    }

    /**
     * Accesor para el estado de vencimiento
     */
    public function getEstadoVencimientoAttribute()
    {
        if (!$this->fecha_vencimiento) {
            return 'sin_fecha';
        }
        
        $dias = $this->dias_hasta_vencimiento;
        
        if ($dias < 0) {
            return 'vencido';
        } elseif ($dias <= 30) {
            return 'por_vencer';
        } else {
            return 'vigente';
        }
    }
}