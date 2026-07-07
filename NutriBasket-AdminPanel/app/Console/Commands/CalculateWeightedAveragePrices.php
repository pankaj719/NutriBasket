<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class CalculateWeightedAveragePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:calculate-weighted-averages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate weighted average prices for all items based on purchase history';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weighted average price calculation...');

        $items = Item::all();
        $totalItems = $items->count();
        $processedItems = 0;
        $updatedItems = 0;

        $progressBar = $this->output->createProgressBar($totalItems);
        $progressBar->start();

        foreach ($items as $item) {
            $oldWeightedAverage = $item->weighted_average_price;
            $newWeightedAverage = $item->calculateWeightedAveragePrice();
            
            $processedItems++;
            
            if ($oldWeightedAverage != $newWeightedAverage) {
                $updatedItems++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("Weighted average calculation completed!");
        $this->info("Total items processed: {$processedItems}");
        $this->info("Items updated: {$updatedItems}");

        return Command::SUCCESS;
    }
} 