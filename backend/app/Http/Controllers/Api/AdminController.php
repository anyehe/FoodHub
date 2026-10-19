<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, Branch, Category, Coupon, InventoryItem, Order, Product, ProductOption, RestaurantTable, User};
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function auditLogs(Request $request)
    {
        return AuditLog::with('user:id,name,email,role')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('route'), fn ($q) => $q->where('route', 'like', '%' . $request->string('route') . '%'))
            ->latest()
            ->paginate(50);
    }

    public function dashboard()
    {
        $today = Order::whereDate('created_at', today());

        return [
            'customers' => User::where('role', 'customer')->count(),
            'riders' => User::where('role', 'rider')->count(),
            'branches' => Branch::count(),
            'products' => Product::count(),
            'orders_today' => (clone $today)->count(),
            'sales_today' => (float) (clone $today)->where('payment_status', 'paid')->sum('grand_total'),
            'pending_orders' => Order::whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'assigned', 'picked_up', 'out_for_delivery'])->count(),
            'low_stock' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')->count(),
        ];
    }

    public function orders(Request $request)
    {
        $query = Order::with(['customer', 'branch', 'items', 'payment', 'assignment.rider', 'statusHistory.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return $query->paginate(30);
    }

    public function order(Order $order)
    {
        return $order->load(['customer', 'branch', 'address', 'items.product', 'payment', 'assignment.rider', 'statusHistory.user']);
    }

    public function transitionOrder(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string|max:500',
        ]);

        return $orders->transition($order, $data['status'], $request->user(), $data['note'] ?? null);
    }

    public function branches()
    {
        return Branch::withCount('orders')->latest()->get();
    }

    public function storeBranch(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:50|unique:branches,code',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_radius_km' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        return Branch::create($data);
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:120',
            'code' => 'sometimes|required|string|max:50|unique:branches,code,' . $branch->id,
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'address' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_radius_km' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($data);
        return $branch;
    }

    public function deleteBranch(Branch $branch)
    {
        abort_if($branch->orders()->exists(), 422, 'Branch has orders and cannot be deleted. Deactivate it instead.');
        $branch->delete();
        return ['message' => 'Branch deleted'];
    }

    public function categories()
    {
        return Category::withCount('products')->orderBy('sort_order')->get();
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        return Category::create($data);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:120',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        }

        $category->update($data);
        return $category;
    }

    public function deleteCategory(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'Category contains products. Deactivate it instead.');
        $category->delete();
        return ['message' => 'Category deleted'];
    }

    public function products(Request $request)
    {
        return Product::with(['category', 'branches', 'options'])->when(
            $request->filled('category_id'),
            fn ($q) => $q->where('category_id', $request->integer('category_id'))
        )->latest()->paginate(30);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'preparation_minutes' => 'nullable|integer|min:1',
            'branch_id' => 'nullable|exists:branches,id',
            'branch_price' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        $branchId = $data['branch_id'] ?? null;
        $branchPrice = $data['branch_price'] ?? null;
        unset($data['branch_id'], $data['branch_price']);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        $product = Product::create($data);

        if ($branchId) {
            $product->branches()->attach($branchId, [
                'price' => $branchPrice ?? $product->base_price,
                'is_available' => true,
            ]);
        }

        return $product->load('branches', 'options', 'category');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'preparation_minutes' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        }

        $product->update($data);
        return $product->load('category', 'branches', 'options');
    }

    public function deleteProduct(Product $product)
    {
        abort_if($product->orderItems()->exists(), 422, 'Product has order history; deactivate it instead.');
        $product->delete();
        return ['message' => 'Product deleted'];
    }

    public function productOptions(Product $product)
    {
        return $product->options()->latest()->get();
    }

    public function storeProductOption(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'price' => 'required|numeric|min:0',
            'is_required' => 'sometimes|boolean',
            'max_choices' => 'sometimes|integer|min:1',
        ]);

        return $product->options()->create($data);
    }

    public function deleteProductOption(ProductOption $option)
    {
        $option->delete();
        return ['message' => 'Product option deleted'];
    }

    public function updateBranchProduct(Request $request, Branch $branch, Product $product)
    {
        $data = $request->validate([
            'price' => 'nullable|numeric|min:0',
            'is_available' => 'required|boolean',
        ]);

        abort_unless($branch->products()->whereKey($product->id)->exists(), 404, 'Product is not assigned to this branch.');
        $branch->products()->updateExistingPivot($product->id, $data);

        return $product->load('branches');
    }

    public function users(Request $request)
    {
        $query = User::with('branch')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        return $query->paginate(30);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'role' => 'sometimes|in:admin,branch_manager,kitchen,waiter,rider,customer',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($data);
        return $user->load('branch');
    }

    public function inventory(Request $request)
    {
        $query = InventoryItem::with('branch')->latest();

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        return $query->paginate(30);
    }

    public function storeInventory(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:150',
            'sku' => 'required|string|max:80',
            'unit' => 'required|string|max:30',
            'quantity' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        return InventoryItem::create($data);
    }

    public function adjustInventory(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'quantity_delta' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
        ]);

        $newQuantity = (float) $item->quantity + (float) $data['quantity_delta'];
        abort_if($newQuantity < 0, 422, 'Inventory cannot become negative.');
        $item->update(['quantity' => $newQuantity]);

        return ['item' => $item->fresh()->load('branch'), 'reason' => $data['reason'] ?? null];
    }

    public function tables(Request $request)
    {
        return RestaurantTable::with('branch')->when(
            $request->filled('branch_id'),
            fn ($q) => $q->where('branch_id', $request->integer('branch_id'))
        )->get();
    }

    public function updateTable(Request $request, RestaurantTable $table)
    {
        $data = $request->validate([
            'status' => 'required|in:available,occupied,reserved,cleaning',
        ]);

        $table->update($data);
        return $table->load('branch');
    }

    public function coupons()
    {
        return Coupon::latest()->get();
    }

    public function storeCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['code'] = strtoupper($data['code']);
        return Coupon::create($data);
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'sometimes|required|in:fixed,percent',
            'value' => 'sometimes|required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $coupon->update($data);
        return $coupon;
    }

    public function deleteCoupon(Coupon $coupon)
    {
        $coupon->delete();
        return ['message' => 'Coupon deleted'];
    }
}
