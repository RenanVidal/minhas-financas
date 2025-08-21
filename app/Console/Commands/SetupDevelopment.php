<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SetupDevelopment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dev:setup 
                            {--fresh : Drop all tables and recreate}
                            {--seed=standard : Seeding type (standard|comprehensive|dev)}';

    /**
     * The console command description.
     */
    protected $description = 'Setup complete development environment with database and demo data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up development environment...');
        $this->newLine();

        // Check if .env exists
        if (!File::exists(base_path('.env'))) {
            $this->error('❌ .env file not found. Please copy .env.example to .env first.');
            return 1;
        }

        // Generate app key if needed
        if (empty(config('app.key'))) {
            $this->info('🔑 Generating application key...');
            Artisan::call('key:generate');
        }

        // Setup database
        $this->setupDatabase();

        // Seed data
        $this->seedData();

        // Setup storage links
        $this->info('🔗 Creating storage links...');
        Artisan::call('storage:link');

        // Clear caches
        $this->info('🧹 Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $this->newLine();
        $this->info('✅ Development environment setup completed!');
        $this->newLine();

        $this->displaySummary();

        return 0;
    }

    private function setupDatabase(): void
    {
        if ($this->option('fresh')) {
            $this->info('🗄️  Dropping and recreating database...');
            Artisan::call('migrate:fresh');
        } else {
            $this->info('🗄️  Running migrations...');
            Artisan::call('migrate');
        }
    }

    private function seedData(): void
    {
        $seedType = $this->option('seed');
        
        $this->info("🌱 Seeding database with {$seedType} data...");

        $options = [];
        
        switch ($seedType) {
            case 'comprehensive':
                $options['--comprehensive'] = true;
                break;
            case 'dev':
                $options['--dev'] = true;
                break;
            case 'standard':
            default:
                // No additional options for standard seeding
                break;
        }

        Artisan::call('demo:seed', $options);
    }

    private function displaySummary(): void
    {
        $this->info('📋 Setup Summary:');
        $this->line('  • Database: Migrated and seeded');
        $this->line('  • Storage: Linked');
        $this->line('  • Caches: Cleared');
        $this->line('  • Environment: Ready for development');
        
        $this->newLine();
        $this->info('🌐 You can now start the development server:');
        $this->line('  php artisan serve');
        
        $this->newLine();
        $this->info('📚 Available commands:');
        $this->line('  • php artisan demo:seed --comprehensive    - Add more demo users');
        $this->line('  • php artisan migrate:fresh --seed         - Reset everything');
        $this->line('  • php artisan dev:setup --fresh --seed=dev - Full reset with dev data');
    }
}