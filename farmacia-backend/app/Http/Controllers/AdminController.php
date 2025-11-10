<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Producto;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard statistics
    public function dashboardStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_products' => Producto::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'recent_orders' => Order::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'monthly_revenue' => Order::where('status', 'completed')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('total')
        ];

        return response()->json($stats);
    }

    // User management
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($users);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:admin,user',
            'is_active' => 'sometimes|boolean'
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $user
        ]);
    }

    // Product management
    public function getProducts(Request $request)
    {
        $query = Producto::query();

        if ($request->has('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($products);
    }

    public function createProduct(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'laboratorio' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'imagen' => 'sometimes|string'
        ]);

        $product = Producto::create($validated);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'product' => $product
        ]);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'precio' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'laboratorio' => 'sometimes|string|max:255',
            'categoria' => 'sometimes|string|max:255',
            'imagen' => 'sometimes|string'
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'product' => $product
        ]);
    }

    public function deleteProduct($id)
    {
        $product = Producto::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente'
        ]);
    }

    // Order management
    public function getOrders(Request $request)
    {
        $query = Order::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($orders);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Estado del pedido actualizado',
            'order' => $order
        ]);
    }
}