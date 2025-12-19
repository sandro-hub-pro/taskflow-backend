<!-- Mobile Sidebar Overlay -->
<div x-show="mobileSidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
     @click="mobileSidebarOpen = false"
     x-cloak>
</div>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300"
       :class="[
           sidebarOpen ? 'w-72' : 'w-20',
           mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
       ]">
    
    <!-- Logo Section -->
    <div class="h-16 flex items-center px-4 border-b border-slate-200 dark:border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
            <div class="flex-shrink-0 p-2 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl text-white">
                <i data-lucide="layout-grid" class="w-6 h-6"></i>
            </div>
            <span x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                  class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent whitespace-nowrap">
                TaskFlow
            </span>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 dark:text-indigo-400 font-medium' : '' }}">
                    <i data-lucide="home" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
                </a>
            </li>
            
            <!-- My Tasks -->
            <li>
                <a href="{{ route('tasks.my') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all duration-200 {{ request()->routeIs('tasks.my') ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 dark:text-indigo-400 font-medium' : '' }}">
                    <i data-lucide="check-square" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">My Tasks</span>
                    @if(isset($pendingTasksCount) && $pendingTasksCount > 0)
                        <span x-show="sidebarOpen" class="ml-auto px-2 py-0.5 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full">{{ $pendingTasksCount }}</span>
                    @endif
                </a>
            </li>
            
            <!-- Projects -->
            <li>
                <a href="{{ route('projects.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all duration-200 {{ request()->routeIs('projects.*') ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 dark:text-indigo-400 font-medium' : '' }}">
                    <i data-lucide="folder" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Projects</span>
                </a>
            </li>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isIncharge())
            <!-- Divider -->
            <li class="pt-4 pb-2" x-show="sidebarOpen">
                <span class="px-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Management</span>
            </li>
            <li x-show="!sidebarOpen" class="py-2">
                <hr class="border-slate-200 dark:border-slate-700">
            </li>
            @endif
            
            @if(auth()->user()->isAdmin())
            <!-- Users (Admin Only) -->
            <li>
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 dark:text-indigo-400 font-medium' : '' }}">
                    <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Users</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>
    
    <!-- User Section -->
    <div class="p-3 border-t border-slate-200 dark:border-slate-800">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
                    class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <!-- Avatar -->
                <div class="flex-shrink-0 relative">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ Storage::url(auth()->user()->profile_picture) }}" 
                             alt="{{ auth()->user()->full_name }}" 
                             class="w-10 h-10 rounded-xl object-cover">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                </div>
                
                <div x-show="sidebarOpen" class="flex-1 text-left overflow-hidden">
                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
                
                <i x-show="sidebarOpen" data-lucide="chevron-up" class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            
            <!-- Dropdown -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 @click.outside="open = false"
                 class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                 x-cloak>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Profile</span>
                </a>
                <a href="{{ route('profile.settings') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Settings</span>
                </a>
                <hr class="border-slate-200 dark:border-slate-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Collapse Button (Desktop) -->
    <button @click="sidebarOpen = !sidebarOpen" 
            class="hidden lg:flex absolute -right-3 top-20 w-6 h-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full items-center justify-center shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
        <i data-lucide="chevron-left" class="w-4 h-4 text-slate-600 dark:text-slate-400 transition-transform" :class="{ 'rotate-180': !sidebarOpen }"></i>
    </button>
</aside>

