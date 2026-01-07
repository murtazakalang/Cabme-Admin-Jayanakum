<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class ImportRoutePermissions extends Command
{
    protected $signature = 'permissions:import-routes {--skip= : Comma separated route name prefixes to skip (eg: auth,password)}';
    protected $description = 'Import named routes into Spatie permissions table';

    public function handle()
    {
        $allowedActionMap = [
            'index' => 'list', 
            'list' =>'list',
            'create'=>'create', 
            'edit'=>'edit', 
            'destroy'=>'delete', 
            'delete'=>'delete',
            'show'=>'view'
        ];

        $ignoreRoutes = [
            'complaints.show',
        ];

        foreach (collect(Route::getRoutes())->map->getName()->filter()->unique() as $name) {
            if (!$name || !Str::contains($name, '.')) continue;
            if (in_array($name, $ignoreRoutes)) continue;

            $parts = explode('.', $name);
            $action = end($parts);
            if (!isset($allowedActionMap[$action])) continue; 

            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->info("Imported permissions into the permissions table.");
    }
}