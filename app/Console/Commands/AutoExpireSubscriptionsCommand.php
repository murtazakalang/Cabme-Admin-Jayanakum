<?php

namespace App\Console\Commands;

use App\Models\SubscriptionHistory;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoExpireSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-expire-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto expire active subscription plans when expiry_date is passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $expiredPlans = SubscriptionHistory::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::now())
            ->get();

        if ($expiredPlans->isNotEmpty()) {

            foreach ($expiredPlans as $plan) {

                // Mark the subscription as expired
                $plan->update(['status' => 'expired']);

                // Check if driver has another active plan
                $hasOtherActive = SubscriptionHistory::where('user_id', $plan->user_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $plan->id)
                    ->exists();

                // If no other active plans exist, reset driver's subscription info
                if (!$hasOtherActive) {
                    Driver::where('id', $plan->user_id)->update([
                        'subscriptionPlanId' => null,
                        'subscriptionExpiryDate' => null,
                        'subscriptionTotalOrders' => 0,
                        'subscriptionTotalVehicle' => 0,
                        'subscriptionTotalDriver' => 0,
                        'subscription_plan' => null
                    ]);
                }
            }
        } else {
            // Optional: Log info or notify
            Log::info('No expired subscriptions found at this time.');
        }

        $this->info('Successfully run expired subscription plans command.');
    }
}
