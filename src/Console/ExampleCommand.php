<?php

declare(strict_types=1);

namespace Yard\SkeletonPackage\Console;

use Illuminate\Console\Command;
use Yard\SkeletonPackage\Facades\Example;

class ExampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'example';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'My custom Acorn command.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info(
            Example::getQuote()
        );
    }
}
