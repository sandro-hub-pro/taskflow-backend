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
    <div class="min-h-screen flex">
        <!-- Left Panel - Decorative -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600">
            <!-- Animated Background -->
            <div class="absolute inset-0 bg-mesh opacity-50"></div>
            <div class="absolute inset-0 pattern-dots opacity-30"></div>
            
            <!-- Floating Elements -->
            <div class="absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-3xl backdrop-blur-xl animate-float"></div>
            <div class="absolute bottom-40 right-20 w-24 h-24 bg-white/10 rounded-2xl backdrop-blur-xl animate-float" style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white/10 rounded-xl backdrop-blur-xl animate-float" style="animation-delay: 2s;"></div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center p-12 text-white">
                <div class="max-w-md text-center">
                    <!-- Logo -->
                    <div class="mb-8 flex justify-center">
                        <div class="p-4 bg-white/10 backdrop-blur-xl rounded-2xl">
                            <i data-lucide="layout-grid" class="w-16 h-16"></i>
                        </div>
                    </div>
                    
                    <h1 class="text-4xl font-bold mb-4">TaskFlow</h1>
                    <p class="text-xl text-white/80 mb-8">Streamline your projects, empower your team, achieve more together.</p>
                    
                    <!-- Features -->
                    <div class="space-y-4 text-left">
                        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold">Team Collaboration</h3>
                                <p class="text-sm text-white/70">Work together seamlessly</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold">Progress Tracking</h3>
                                <p class="text-sm text-white/70">Monitor tasks in real-time</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <i data-lucide="zap" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold">Boost Productivity</h3>
                                <p class="text-sm text-white/70">Get more done, faster</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Panel - Form -->
        <div class="flex-1 flex flex-col">
            <!-- Theme Switcher -->
            <div class="absolute top-4 right-4 z-50">
                @include('components.theme-switcher')
            </div>
            
            <!-- Mobile Logo -->
            <div class="lg:hidden flex justify-center pt-8 pb-4">
                <a href="/" class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl text-white">
                        <i data-lucide="layout-grid" class="w-8 h-8"></i>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">TaskFlow</span>
                </a>
            </div>
            
            <!-- Form Container -->
            <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
                <div class="w-full max-w-md">
                    @yield('content')
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center py-4 text-sm text-slate-500 dark:text-slate-400">
                <p>&copy; {{ date('Y') }} TaskFlow. All rights reserved.</p>
            </div>
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
    </script>
    
    @stack('scripts')
</body>
</html>

