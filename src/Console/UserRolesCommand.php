<?php

declare(strict_types=1);

namespace Yard\UserRoles\Console;

use Illuminate\Console\Command;
use Yard\UserRoles\Facades\UserRoles;

class UserRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user-roles';

    /**
     * The console command description.
     */
    protected $description = 'Create, Update and Delete user roles';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        UserRoles::setRoles();
        $this->info('Created user roles');
    }
}
