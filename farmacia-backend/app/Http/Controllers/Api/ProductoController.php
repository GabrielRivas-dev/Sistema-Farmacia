<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        // Filtros
        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->has('laboratorio') && $request->laboratorio) {
            $query->where('laboratorio', 'LIKE', "%{$request->laboratorio}%");
        }

        if ($request->has('disponible') && $request->disponible !== '') {
            $query->where('disponible', $request->boolean('disponible'));
        }

        if ($request->has('promocion') && $request->boolean('promocion')) {
            $query->enPromocion();
        }

        if ($request->has('buscar') && $request->buscar) {
            $query->buscar($request->buscar);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 12);
        $productos = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $productos->items(),
            'pagination' => [
                'current_page' => $productos->currentPage(),
                'per_page' => $productos->perPage(),
                'total' => $productos->total(),
                'last_page' => $productos->lastPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $producto
        ]);
    }

    public function categorias()
    {
        $categorias = Producto::distinct()
            ->whereNotNull('categoria')
            ->pluck('categoria')
            ->map(function ($categoria) {
                return [
                    'id' => $categoria,
                    'nombre' => ucfirst($categoria)
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    public function laboratorios()
    {
        $laboratorios = Producto::distinct()
            ->whereNotNull('laboratorio')
            ->pluck('laboratorio');

        return response()->json([
            'success' => true,
            'data' => $laboratorios
        ]);
    }
}