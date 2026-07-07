<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoAcknowledgeDeliveredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-acknowledge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-acknowledge orders that have been delivered for more than 24 hours without quantity change requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        
        // Get all orders that are delivered for more than 24 hours
        $ordersToAcknowledge = Order::where('order_status', 'delivered')
            ->where('delivered', '<=', $twentyFourHoursAgo)
            ->whereNull('acknowledged_at')
            ->get();

        $acknowledgedCount = 0;

        foreach ($ordersToAcknowledge as $order) {
            try {
                $order->order_status = 'acknowledged';
                $order->acknowledged_at = Carbon::now();
                $order->save();
                
                $acknowledgedCount++;
                
                Log::info("Auto-acknowledged order #{$order->id} after 24 hours");
                
            } catch (\Exception $e) {
                Log::error("Failed to auto-acknowledge order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Auto-acknowledged {$acknowledgedCount} orders");
        
        return 0;
    }
}
