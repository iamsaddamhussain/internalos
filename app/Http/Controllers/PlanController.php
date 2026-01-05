<?php

namespace App\Http\Controllers;

use App\Models\UpgradeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index()
    {
        $workspace = app('workspace');
        
        $limits = $workspace->getPlanLimits();
        $currentCounts = [
            'collections' => $workspace->collections()->count(),
            'records' => \App\Models\Record::whereIn('collection_id', $workspace->collections->pluck('id'))->count(),
            'users' => $workspace->users()->count(),
        ];

        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'price' => '$0',
                'period' => 'forever',
                'description' => 'Perfect for trying out Internal OS',
                'features' => [
                    '3 Collections',
                    '500 Records',
                    '3 Team Members',
                    '1 Workspace',
                    'Basic Field Types',
                    'Community Support'
                ],
                'limits' => [
                    'max_collections' => 3,
                    'max_records' => 500,
                    'max_users' => 3,
                ]
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'price' => '$15-25',
                'period' => 'per user/month',
                'description' => 'For growing teams that need more',
                'features' => [
                    'Unlimited Collections',
                    '50,000 Records',
                    '20 Team Members',
                    '5 Workspaces',
                    'Advanced Field Types',
                    'Export to CSV',
                    'Priority Support'
                ],
                'limits' => [
                    'max_collections' => -1,
                    'max_records' => 50000,
                    'max_users' => 20,
                ],
                'stripe_enabled' => true,
            ],
            [
                'slug' => 'ultra_premium',
                'name' => 'Ultra Premium',
                'price' => 'Custom',
                'period' => 'pricing',
                'description' => 'Enterprise-grade for large organizations',
                'features' => [
                    'Unlimited Everything',
                    'API Access',
                    'Webhooks',
                    'Audit Logs',
                    'SSO Integration',
                    'Custom Fields',
                    'Dedicated Support'
                ],
                'limits' => [
                    'max_collections' => -1,
                    'max_records' => -1,
                    'max_users' => -1,
                ],
                'contact_sales' => true,
            ]
        ];

        return Inertia::render('Settings/Plan', [
            'currentPlan' => [
                'name' => $workspace->getPlanName(),
                'slug' => $workspace->plan,
                'limits' => $limits,
                'current' => $currentCounts,
            ],
            'plans' => $plans,
            'workspace' => $workspace,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function requestUpgrade(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:premium,ultra_premium',
            'message' => 'nullable|string|max:1000',
        ]);

        $workspace = app('workspace');
        $user = auth()->user();

        // Store the upgrade request in database
        $upgradeRequest = UpgradeRequest::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'requested_plan' => $request->plan,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // TODO: Send email notification to sales team
        
        return back()->with('success', 'Upgrade request received! Our team will contact you shortly at ' . $user->email);
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:premium',
        ]);

        $workspace = app('workspace');
        $user = auth()->user();

        // For now, create upgrade request
        // In production with Stripe installed, this would create a Stripe checkout session
        UpgradeRequest::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'requested_plan' => $request->plan,
            'status' => 'pending',
        ]);

        // TODO: When Stripe is configured:
        // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // $session = \Stripe\Checkout\Session::create([
        //     'payment_method_types' => ['card'],
        //     'line_items' => [[
        //         'price' => config('services.stripe.premium_price_id'),
        //         'quantity' => $workspace->users()->count(),
        //     ]],
        //     'mode' => 'subscription',
        //     'success_url' => route('plans.success') . '?session_id={CHECKOUT_SESSION_ID}',
        //     'cancel_url' => route('plans.index'),
        //     'client_reference_id' => $workspace->id,
        // ]);
        // return response()->json(['sessionId' => $session->id]);

        return back()->with('success', 'Upgrade request created. Stripe integration will be available soon!');
    }
}
