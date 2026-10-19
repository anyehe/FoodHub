<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Branch, Coupon, Product};
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function quote(Request $r, PricingService $pricing)
    {
        $d = $r->validate([
            'branch_id' => 'required|exists:branches,id',
            'fulfillment_type' => 'required|in:delivery,pickup,dine_in',
            'coupon' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'items.*.options' => 'nullable|array',
        ]);

        $branch = Branch::where('is_active', true)->findOrFail($d['branch_id']);
        $coupon = null;

        if (!empty($d['coupon'])) {
            $coupon = Coupon::where('code', strtoupper(trim($d['coupon'])))->first();
            if (!$coupon || !$coupon->usable()) {
                throw ValidationException::withMessages([
                    'coupon' => 'Invalid or expired coupon.',
                ]);
            }
        }

        $rows = [];

        foreach ($d['items'] as $line) {
            $product = Product::with('options')
                ->where('is_active', true)
                ->findOrFail($line['product_id']);

            $branchProduct = $branch->products()
                ->where('products.id', $product->id)
                ->first();

            if (!$branchProduct || !$branchProduct->pivot->is_available) {
                throw ValidationException::withMessages([
                    'items' => "{$product->name} is unavailable at this branch.",
                ]);
            }

            $optionTotal = 0;
            foreach ($line['options'] ?? [] as $optionId) {
                $option = $product->options->firstWhere('id', (int) $optionId);
                if (!$option) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid product option selected.',
                    ]);
                }
                $optionTotal += (float) $option->price;
            }

            $rows[] = [
                'unit_price' => (float) ($branchProduct->pivot->price ?? $product->base_price) + $optionTotal,
                'quantity' => (int) $line['quantity'],
            ];
        }

        return $pricing->calculate(
            $rows,
            $coupon,
            $d['fulfillment_type']
        );
    }
}
