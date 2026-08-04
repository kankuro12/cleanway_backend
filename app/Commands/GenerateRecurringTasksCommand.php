<?php

namespace App\Commands;

use App\Domain\Tasks\GenerateRecurringTasks;
use App\Models\TaskRecurrence;
use Illuminate\Console\Command;

class GenerateRecurringTasksCommand extends Command
{
    protected $signature = 'tasks:generate-recurring {--days=30 : Look-ahead window in days}';

    protected $description = 'Generate task instances from active recurrence templates';

    public function handle(GenerateRecurringTasks $generator): int
    {
        $total = 0;

        foreach (TaskRecurrence::where('active', true)->cursor() as $recurrence) {
            $total += $generator->generate($recurrence, (int) $this->option('days'));
        }

        $this->info("Generated {$total} task instances.");

        return self::SUCCESS;
    }
}
