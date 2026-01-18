<?php

namespace App\Console\Commands;

use App\Services\AutomationScheduler;
use Illuminate\Console\Command;

class RunAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automations:run {--type=all : Type of automations to run (all, date, events)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run automation rules and execute matching actions';

    /**
     * Execute the console command.
     */
    public function handle(AutomationScheduler $scheduler)
    {
        $type = $this->option('type');

        $this->info('Starting automation runner...');

        try {
            if ($type === 'all' || $type === 'date') {
                $this->info('Processing date-based automations...');
                $results = $scheduler->runDateBasedAutomations();
                
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Automations', $results['total']],
                        ['Successfully Executed', $results['executed']],
                        ['Skipped', $results['skipped']],
                        ['Failed', $results['failed']],
                    ]
                );
            }

            $this->info('✓ Automation run completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Automation run failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
