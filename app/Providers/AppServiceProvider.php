<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('plan.resolver', function () {
            return new class {
                public function getTier(\App\Models\User $user): string
                {
                    $tiers = config('services.stripe.price_tiers');
                    $pro = $tiers['pro'] ?? [];
                    $casual = $tiers['casual'] ?? [];

                    // Try local Cashier record first
                    if ($user->subscribed('default')) {
                        $sub = $user->subscription('default');
                        $priceId = $sub?->stripe_price;
                        if ($priceId) {
                            if (in_array($priceId, $pro, true)) return 'pro';
                            if (in_array($priceId, $casual, true)) return 'casual';
                        }
                    }

                    // Fallback: query Stripe directly for customer's active subscription
                    try {
                        $stripe = new StripeClient(config('services.stripe.secret'));
                        $customerId = $user->createOrGetStripeCustomer();
                        $subs = $stripe->subscriptions->all([
                            'customer' => $customerId,
                            'limit' => 5,
                            'status' => 'all',
                            'expand' => ['data.items.data.price'],
                        ]);
                        foreach ($subs->data as $s) {
                            // consider active/past_due/trialing as eligible tiers
                            if (in_array($s->status, ['active','trialing','past_due'], true)) {
                                $item = $s->items->data[0] ?? null;
                                $priceId = $item?->price?->id;
                                if ($priceId) {
                                    if (in_array($priceId, $pro, true)) return 'pro';
                                    if (in_array($priceId, $casual, true)) return 'casual';
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore and treat as free
                    }

                    return 'free';
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
