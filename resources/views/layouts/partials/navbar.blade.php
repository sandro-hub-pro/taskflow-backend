<!-- Navbar -->
<header class="sticky top-0 z-30 h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
    <div class="h-full px-4 lg:px-6 flex items-center justify-between gap-4">
        <!-- Left Section -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Button -->
            <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="menu" class="w-5 h-5 text-slate-600 dark:text-slate-400"></i>
            </button>
            
            <!-- Breadcrumb -->
            <nav class="hidden sm:flex items-center gap-2 text-sm">
                @yield('breadcrumb')
            </nav>
        </div>
        
        <!-- Right Section -->
        <div class="flex items-center gap-2 lg:gap-4">
            <!-- Search (Desktop) -->
            <div class="hidden md:block relative">
                <input type="text" 
                       placeholder="Search..." 
                       class="w-64 pl-10 pr-4 py-2 text-sm bg-slate-100 dark:bg-slate-800 border-0 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 placeholder-slate-400 dark:placeholder-slate-500">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            </div>
            
            <!-- Notifications -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" 
                        class="relative p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="bell" class="w-5 h-5 text-slate-600 dark:text-slate-400"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                
                <!-- Dropdown -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="open = false"
                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                     x-cloak>
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Notifications</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <!-- Empty State -->
                        <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                            <i data-lucide="bell-off" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p class="text-sm">No notifications yet</p>
                        </div>
                    </div>
                    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- Theme Switcher -->
            @include('components.theme-switcher')
            
            <!-- Quick Add -->
            @if(auth()->user()->isAdmin() || auth()->user()->isIncharge())
            <div x-data="{ open: false }" class="relative hidden sm:block">
                <button @click="open = !open" 
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Create</span>
                </button>
                
                <!-- Dropdown -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                     x-cloak>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('projects.create') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        <span>New Project</span>
                    </a>
                    @endif
                    <a href="{{ route('tasks.create') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i data-lucide="plus-square" class="w-4 h-4"></i>
                        <span>New Task</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</header>

