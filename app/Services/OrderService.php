<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        if (Order::where('external_order_id', $data['order_id'])->exists()) {
            return;
        }

        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        if (!$merchant) {
            throw new \Exception('Merchant not found');
        }

        $affiliate = Affiliate::where('discount_code', $data['discount_code'])->first();

        if (!$affiliate) {
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate);
        }

        $order = Order::firstOrNew(['external_order_id' => $data['order_id']]);
        $order->subtotal = $data['subtotal_price'];
        $order->affiliate_id = $affiliate->id;
        $order->merchant_id = $merchant->id;
        $order->commission_owed = $data['subtotal_price'] * $affiliate->commission_rate;
        $order->save();
    }
}
