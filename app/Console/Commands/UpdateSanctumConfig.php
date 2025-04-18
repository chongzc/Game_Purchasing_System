<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateSanctumConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:update-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Sanctum configuration for SPA authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Update session.php to make sure Sanctum works
        $sessionPath = config_path('session.php');
        $sessionConfig = File::get($sessionPath);
        
        if (strpos($sessionConfig, "'domain' => env('SESSION_DOMAIN', null),") !== false) {
            $sessionConfig = str_replace(
                "'domain' => env('SESSION_DOMAIN', null),",
                "'domain' => env('SESSION_DOMAIN', null), // Updated for Sanctum",
                $sessionConfig
            );
            File::put($sessionPath, $sessionConfig);
            $this->info('Session domain config updated.');
        }
        
        // Update cors.php to include session support
        $corsPath = config_path('cors.php');
        $corsConfig = File::get($corsPath);
        
        if (strpos($corsConfig, "'supports_credentials' => false,") !== false) {
            $corsConfig = str_replace(
                "'supports_credentials' => false,",
                "'supports_credentials' => true,",
                $corsConfig
            );
            File::put($corsPath, $corsConfig);
            $this->info('CORS credentials support enabled.');
        }
        
        // Add stateful domains to sanctum.php
        $sanctumPath = config_path('sanctum.php');
        $sanctumConfig = File::get($sanctumPath);
        
        $defaultDomains = "localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1";
        $this->info('Default domains: ' . $defaultDomains);
        
        $this->info('Configuration updated successfully.');
        
        return Command::SUCCESS;
    }
}
