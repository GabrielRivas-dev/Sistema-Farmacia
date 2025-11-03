<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
   public function store(Request $request)
{
    $request->validate([
        'banco' => 'required|string',
        'referencia_pago' => 'nullable|string',
        'total' => 'required|numeric',
        'items' => 'required',
        'imagen_pago' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
    ]);

    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    $path = null;
    if ($request->hasFile('imagen_pago')) {
        $path = $request->file('imagen_pago')->store('pagos', 'public');
    }

    // 🧩 Decodificar los items (porque vienen como JSON string)
    $items = json_decode($request->items, true);

    if (!is_array($items)) {
        return response()->json(['error' => 'Formato inválido en los items'], 400);
    }

    // Crear orden principal
    $order = Order::create([
        'user_id' => $user->id,
        'total' => $request->total,
        'banco' => $request->banco,
        'referencia_pago' => $request->referencia_pago,
        'imagen_pago' => $path,
    ]);

    // Crear cada item del pedido
    foreach ($items as $item) {
        $order->items()->create([
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'], // 👈 ahora sí llega correctamente
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Pedido creado correctamente',
        'data' => $order->load('items')
    ]);
}

}
