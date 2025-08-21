<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                        <!-- Logo -->
                        <div class="text-center mb-4">
                            <a href="/" class="text-decoration-none">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                                <h3 class="mt-3 text-primary fw-bold">{{ config('app.name', 'FinanceApp') }}</h3>
                                <p class="text-muted small">Sistema de Gerenciamento Financeiro</p>
                            </a>
                        </div>

                        <!-- Auth Card -->
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                {{ $slot }}
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
