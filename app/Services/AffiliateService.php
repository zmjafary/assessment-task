<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        if (User::where('email', $email)->exists()) {
            throw new AffiliateCreateException('Email already exists');
        }

        $discountCode = $this->apiService->createDiscountCode($merchant)['code'];

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE
        ]);

        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'discount_code' => $discountCode,
            'commission_rate' => $commissionRate ?? $merchant->default_commission_rate
        ]);

        try{
            Mail::to($email)->send(new AffiliateCreated($affiliate));
        } catch (\Exception $e) {
            // Log error
        }

        return $affiliate;
    }

    public function logCommission(Affiliate $affiliate, float $commission)
    {
        // Log commission
    }
}
