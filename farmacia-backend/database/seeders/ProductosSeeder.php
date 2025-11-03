<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Analgésicos
            [
                'nombre' => 'Paracetamol 500mg',
                'descripcion' => 'Analgésico y antipirético para el alivio del dolor y la fiebre',
                'precio' => 8.50,
                'precio_original' => 10.00,
                'descuento' => 15,
                'imagen' => 'https://picsum.photos/300/200?random=1',
                'laboratorio' => 'Bayer',
                'stock' => 45,
                'disponible' => true,
                'en_promocion' => true,
                'requiere_receta' => false,
                'categoria' => 'analgesicos',
                'principio_activo' => 'Paracetamol',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'indicaciones' => 'Dolor leve a moderado, fiebre',
                'codigo_barras' => '1234567890123'
            ],
            [
                'nombre' => 'Ibuprofeno 400mg',
                'descripcion' => 'Antiinflamatorio no esteroideo para dolor e inflamación',
                'precio' => 12.80,
                'imagen' => 'https://picsum.photos/300/200?random=2',
                'laboratorio' => 'Pfizer',
                'stock' => 32,
                'disponible' => true,
                'en_promocion' => false,
                'requiere_receta' => false,
                'categoria' => 'analgesicos',
                'principio_activo' => 'Ibuprofeno',
                'presentacion' => 'Cápsulas',
                'concentracion' => '400mg',
                'indicaciones' => 'Dolor muscular, artritis, dolor menstrual',
                'codigo_barras' => '1234567890124'
            ],

            // Antibióticos
            [
                'nombre' => 'Amoxicilina 500mg',
                'descripcion' => 'Antibiótico de amplio espectro para infecciones bacterianas',
                'precio' => 25.90,
                'imagen' => 'https://picsum.photos/300/200?random=3',
                'laboratorio' => 'Roche',
                'stock' => 18,
                'disponible' => true,
                'en_promocion' => false,
                'requiere_receta' => true,
                'categoria' => 'antibioticos',
                'principio_activo' => 'Amoxicilina',
                'presentacion' => 'Cápsulas',
                'concentracion' => '500mg',
                'indicaciones' => 'Infecciones respiratorias, urinarias, de piel',
                'codigo_barras' => '1234567890125'
            ],

            // Vitaminas
            [
                'nombre' => 'Vitamina C 1000mg',
                'descripcion' => 'Suplemento de vitamina C para el sistema inmunológico',
                'precio' => 18.50,
                'precio_original' => 22.00,
                'descuento' => 16,
                'imagen' => 'https://picsum.photos/300/200?random=4',
                'laboratorio' => 'Nature\'s Bounty',
                'stock' => 67,
                'disponible' => true,
                'en_promocion' => true,
                'requiere_receta' => false,
                'categoria' => 'vitaminas',
                'principio_activo' => 'Ácido Ascórbico',
                'presentacion' => 'Tabletas masticables',
                'concentracion' => '1000mg',
                'indicaciones' => 'Defensa inmunológica, antioxidante',
                'codigo_barras' => '1234567890126'
            ],

            // Dermatológicos
            [
                'nombre' => 'Crema Hidratante con Urea',
                'descripcion' => 'Crema para piel seca y áspera, tratamiento de callosidades',
                'precio' => 32.40,
                'imagen' => 'https://picsum.photos/300/200?random=5',
                'laboratorio' => 'Eucerin',
                'stock' => 23,
                'disponible' => true,
                'en_promocion' => false,
                'requiere_receta' => false,
                'categoria' => 'dermatologicos',
                'principio_activo' => 'Urea',
                'presentacion' => 'Crema',
                'concentracion' => '10%',
                'indicaciones' => 'Piel seca, callosidades, hiperqueratosis',
                'codigo_barras' => '1234567890127'
            ],

            // Respiratorios
            [
                'nombre' => 'Salbutamol Inhalador',
                'descripcion' => 'Broncodilatador para el alivio del asma y broncoespasmo',
                'precio' => 45.60,
                'imagen' => 'https://picsum.photos/300/200?random=6',
                'laboratorio' => 'GSK',
                'stock' => 0,
                'disponible' => false,
                'en_promocion' => false,
                'requiere_receta' => true,
                'categoria' => 'respiratorios',
                'principio_activo' => 'Salbutamol',
                'presentacion' => 'Inhalador',
                'concentracion' => '100mcg/dosis',
                'indicaciones' => 'Asma bronquial, broncoespasmo',
                'codigo_barras' => '1234567890128'
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        // Generar más productos de ejemplo
        $laboratorios = ['Bayer', 'Pfizer', 'Roche', 'Novartis', 'GSK', 'Merck', 'Sanofi', 'AstraZeneca'];
        $categorias = ['analgesicos', 'antibioticos', 'vitaminas', 'dermatologicos', 'respiratorios', 'gastrointestinales', 'cardiacos', 'diabeticos'];
        
        for ($i = 7; $i <= 30; $i++) {
            $categoria = $categorias[array_rand($categorias)];
            $enPromocion = rand(0, 1) === 1;
            $descuento = $enPromocion ? rand(10, 30) : 0;
            $precio = rand(5, 100) + (rand(0, 99) / 100);
            $precioOriginal = $enPromocion ? $precio * (1 + $descuento/100) : null;

            Producto::create([
                'nombre' => "Medicamento {$i} " . $this->getNombrePorCategoria($categoria),
                'descripcion' => "Descripción del medicamento {$i} para tratamiento de diversas condiciones",
                'precio' => round($precio, 2),
                'precio_original' => $precioOriginal ? round($precioOriginal, 2) : null,
                'descuento' => $descuento,
                'imagen' => "https://picsum.photos/300/200?random={$i}",
                'laboratorio' => $laboratorios[array_rand($laboratorios)],
                'stock' => rand(0, 100),
                'disponible' => rand(0, 10) > 1, // 90% de disponibilidad
                'en_promocion' => $enPromocion,
                'requiere_receta' => rand(0, 1) === 1,
                'categoria' => $categoria,
                'principio_activo' => "Principio Activo {$i}",
                'presentacion' => ['Tabletas', 'Cápsulas', 'Jarabe', 'Crema', 'Inhalador'][array_rand([0,1,2,3,4])],
                'concentracion' => rand(100, 1000) . 'mg',
                'codigo_barras' => '1234567890' . str_pad($i, 3, '0', STR_PAD_LEFT)
            ]);
        }
    }

    private function getNombrePorCategoria($categoria)
    {
        $nombres = [
            'analgesicos' => ['Plus', 'Forte', 'Rapid', 'Extra'],
            'antibioticos' => ['Max', 'Duo', 'Complex', 'Broad'],
            'vitaminas' => ['Complete', 'Energy', 'Immune', 'Vital'],
            'dermatologicos' => ['Soft', 'Care', 'Protect', 'Heal'],
            'respiratorios' => ['Air', 'Breathe', 'Clear', 'Relief'],
            'gastrointestinales' => ['Digest', 'Comfort', 'Balance', 'Soothe'],
            'cardiacos' => ['Cardio', 'Heart', 'Flow', 'Pressure'],
            'diabeticos' => ['Gluco', 'Sugar', 'Balance', 'Control']
        ];

        return $nombres[$categoria][array_rand($nombres[$categoria])];
    }
}