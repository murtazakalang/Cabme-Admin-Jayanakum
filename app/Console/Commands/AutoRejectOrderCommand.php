<?php

namespace App\Console\Commands;

use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use Illuminate\Console\Command;
use Carbon\Carbon;


class AutoRejectOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-reject-order-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $parcelOrders = ParcelOrder::where('status', 'new')->get();
        $today = strtotime(date('d-M-Y')); 
        foreach ($parcelOrders as $order) {
            $parcelDate = strtotime($order->parcel_date); 
            if ($parcelDate < $today) {
                $order->update([
                    'status' => 'canceled',
                    'reason' => 'out of date order',
                ]);
            }
        }

        $rentalOrders = RentalOrder::where('status', 'new')->get();
        $today = Carbon::today()->toDateString();
        foreach ($rentalOrders as $order) {
            if ($order->start_date < $today) {
                $order->update([
                    'status' => 'canceled',
                    'reason' => 'out of date order',
                ]);
            }
        }
        
        $this->info('Successfully update status.');
    }
}
