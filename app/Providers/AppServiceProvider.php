<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Fix for Vite directive to handle URLs properly
        Blade::directive('viteFixed', function ($expression) {
            return "<?php echo Vite::useBuildDirectory('build')->withEntryPoints($expression); ?>";
        });
    }
}
