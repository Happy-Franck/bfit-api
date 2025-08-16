<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function listPlans()
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        $prices = $stripe->prices->all([
            'active' => true,
            'limit' => 50,
            'expand' => ['data.product']
        ]);

        // Allowed price IDs from config (supports explicit NEXT_PUBLIC_* and a comma-separated STRIPE_ALLOWED_PRICE_IDS)
        $allowedEnvIds = config('services.stripe.allowed_price_ids', []);
        $allowedCsv = config('services.stripe.allowed_price_ids_csv');
        if ($allowedCsv) {
            $allowedCsvIds = array_filter(array_map('trim', explode(',', $allowedCsv)));
            $allowedEnvIds = array_unique(array_merge($allowedEnvIds, $allowedCsvIds));
        }

        $plans = collect($prices->data)
            ->filter(function ($price) use ($allowedEnvIds) {
                $isEligible = $price->type === 'recurring' && $price->active && isset($price->product);
                if (!$isEligible) {
                    return false;
                }
                // If a whitelist is provided, only include those price IDs
                if (!empty($allowedEnvIds)) {
                    return in_array($price->id, $allowedEnvIds, true);
                }
                return true;
            })
            ->map(function ($price) {
                $product = $price->product;
                return [
                    'price_id' => $price->id,
                    'currency' => strtoupper($price->currency),
                    'unit_amount' => $price->unit_amount,
                    'interval' => $price->recurring->interval ?? null,
                    'interval_count' => $price->recurring->interval_count ?? 1,
                    'trial_period_days' => $price->recurring->trial_period_days ?? null,
                    'product' => [
                        'id' => is_object($product) ? $product->id : $product,
                        'name' => is_object($product) && isset($product->name) ? $product->name : null,
                        'description' => is_object($product) && isset($product->description) ? $product->description : null,
                        'images' => is_object($product) && isset($product->images) ? $product->images : [],
                        'metadata' => is_object($product) && isset($product->metadata) ? $product->metadata : (object)[],
                    ],
                ];
            })
            ->values();

        return response()->json([
            'plans' => $plans,
        ], 200);
    }

    public function createCheckoutSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price_id' => 'required|string',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Ensure Stripe customer
        $customerId = $user->createOrGetStripeCustomer();

        $session = $stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[
                'price' => $request->input('price_id'),
                'quantity' => 1,
            ]],
            'success_url' => $request->input('success_url') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $request->input('cancel_url'),
            'allow_promotion_codes' => true,
            'client_reference_id' => (string) $user->id,
            'metadata' => [
                'user_id' => (string) $user->id,
                'email' => (string) $user->email,
            ],
        ]);

        return response()->json([
            'id' => $session->id,
            'url' => $session->url,
        ], 200);
    }

    public function createPortalSession(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $returnUrl = $request->input('return_url');
        if (!$returnUrl || !filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['message' => 'Invalid return_url'], 422);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $customerId = $user->createOrGetStripeCustomer();

        $params = [
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ];
        // Optionally pass a specific portal configuration
        $portalCfg = config('services.stripe.portal_configuration');
        if (!empty($portalCfg)) {
            $params['configuration'] = $portalCfg;
        }

        try {
            $session = $stripe->billingPortal->sessions->create($params);
        } catch (\Exception $e) {
            Log::error('Stripe portal error: '.$e->getMessage());
            return response()->json([
                'message' => 'Stripe portal unavailable. Configure your Portal in Stripe dashboard (test mode).',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'url' => $session->url,
        ], 200);
    }
} 