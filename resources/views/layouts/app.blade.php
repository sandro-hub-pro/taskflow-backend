<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeHandler()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'TaskFlow') - Task Management System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-20'">
            <!-- Navbar -->
            @include('layouts.partials.navbar')
            
            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                         class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 flex items-center justify-between animate-slide-down">
                        <div class="flex items-center gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="hover:bg-emerald-100 dark:hover:bg-emerald-800 p-1 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                         class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 flex items-center justify-between animate-slide-down">
                        <div class="flex items-center gap-3">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="hover:bg-red-100 dark:hover:bg-red-800 p-1 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif
                
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="py-4 px-6 border-t border-slate-200 dark:border-slate-800 text-center text-sm text-slate-500 dark:text-slate-400">
                <p>&copy; {{ date('Y') }} TaskFlow. Crafted with care for seamless task management.</p>
            </footer>
        </div>
    </div>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        function themeHandler() {
            return {
                theme: localStorage.getItem('theme') || 'system',
                isDark: false,
                
                init() {
                    this.applyTheme();
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                        if (this.theme === 'system') {
                            this.applyTheme();
                        }
                    });
                },
                
                setTheme(newTheme) {
                    this.theme = newTheme;
                    localStorage.setItem('theme', newTheme);
                    this.applyTheme();
                },
                
                applyTheme() {
                    if (this.theme === 'system') {
                        this.isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    } else {
                        this.isDark = this.theme === 'dark';
                    }
                }
            }
        }
        
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
        
        // Re-initialize icons after Alpine updates
        document.addEventListener('alpine:initialized', function() {
            setTimeout(() => lucide.createIcons(), 100);
        });
    </script>
    
    @stack('scripts')
</body>
</html>

