<?php

namespace App\Console\Commands;

use Database\Seeders\DemoUserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\GoalSeeder;
use Database\Seeders\DevelopmentSeeder;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\ComprehensiveSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:seed 
                            {--dev : Include development test users}
                            {--comprehensive : Create comprehensive demo data with multiple user scenarios}
                            {--production : Create production-ready default data only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with demo financial data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌱 Seeding demo data...');

        // Confirm in production
        if (app()->environment('production')) {
            if (!$this->confirm('You are in production! Are you sure you want to seed demo data?')) {
                $this->info('Cancelled.');
                return;
            }
        }

        DB::transaction(function () {
            if ($this->option('production')) {
                // Production seeding - only essential data
                $this->info('🏭 Creating production data...');
                $this->call('db:seed', ['--class' => ProductionSeeder::class]);
                return;
            }

            if ($this->option('comprehensive')) {
                // Comprehensive seeding with multiple scenarios
                $this->info('🎭 Creating comprehensive demo data...');
                $this->call('db:seed', ['--class' => ComprehensiveSeeder::class]);
                return;
            }

            // Standard demo seeding
            $this->info('👤 Creating demo users...');
            $this->call('db:seed', ['--class' => DemoUserSeeder::class]);

            $this->info('🏷️  Creating categories...');
            $this->call('db:seed', ['--class' => CategorySeeder::class]);

            $this->info('💰 Creating transactions...');
            $this->call('db:seed', ['--class' => TransactionSeeder::class]);

            $this->info('🎯 Creating goals...');
            $this->call('db:seed', ['--class' => GoalSeeder::class]);

            // Seed development data if requested
            if ($this->option('dev')) {
                $this->info('🔧 Creating development test data...');
                $this->call('db:seed', ['--class' => DevelopmentSeeder::class]);
            }
        });

        $this->newLine();
        $this->info('✅ Demo data seeded successfully!');
        $this->newLine();
        
        if ($this->option('production')) {
            $this->table(
                ['User', 'Email', 'Password'],
                [
                    ['Administrador do Sistema', 'admin@sistema.com', 'admin@2024!'],
                ]
            );
            $this->warn('⚠️  Remember to change the admin password in production!');
        } elseif ($this->option('comprehensive')) {
            $this->table(
                ['User Profile', 'Email', 'Password', 'Scenario'],
                [
                    ['Ana Oliveira', 'ana@demo.com', 'demo123', 'High Earner'],
                    ['Carlos Mendes', 'carlos@demo.com', 'demo123', 'Budget Conscious'],
                    ['Beatriz Lima', 'beatriz@demo.com', 'demo123', 'Student'],
                    ['Roberto Silva', 'roberto@demo.com', 'demo123', 'Freelancer'],
                    ['Fernanda Costa', 'fernanda@demo.com', 'demo123', 'Family Budget'],
                ]
            );
        } else {
            $this->table(
                ['User', 'Email', 'Password'],
                [
                    ['Usuário Demo', 'demo@financeiro.com', 'demo123'],
                    ['Administrador', 'admin@financeiro.com', 'admin123'],
                    ['Test User', 'test@example.com', 'password'],
                ]
            );

            if ($this->option('dev')) {
                $this->info('Development users:');
                $this->table(
                    ['User', 'Email', 'Password'],
                    [
                        ['João Silva', 'joao@teste.com', '123456'],
                        ['Maria Santos', 'maria@teste.com', '123456'],
                        ['Pedro Costa', 'pedro@teste.com', '123456'],
                    ]
                );
            }
        }

        $this->newLine();
        $this->info('🚀 You can now login and explore the financial management system!');
        
        // Show usage examples
        $this->newLine();
        $this->info('💡 Seeding options:');
        $this->line('  • php artisan demo:seed                    - Standard demo data');
        $this->line('  • php artisan demo:seed --dev              - Include development users');
        $this->line('  • php artisan demo:seed --comprehensive    - Multiple user scenarios');
        $this->line('  • php artisan demo:seed --production       - Production-ready data only');
    }
}
