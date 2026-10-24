<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
        ]);

        $orders = Order::query()
            ->with(['user', 'course', 'enrollment'])
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('gateway_reference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $paidTotal = Order::query()->where('status', OrderStatus::Paid)->sum('amount');

        return view('admin.orders.index', compact('orders', 'filters', 'paidTotal'));
    }

    public function show(Order $order): View
    {
        $order->load(['user.student', 'course', 'enrollment']);

        return view('admin.orders.show', compact('order'));
    }
}
