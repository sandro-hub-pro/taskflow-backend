<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" 
            class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            :title="theme === 'system' ? 'System' : (theme === 'dark' ? 'Dark' : 'Light')">
        <template x-if="theme === 'light'">
            <i data-lucide="sun" class="w-5 h-5 text-amber-500"></i>
        </template>
        <template x-if="theme === 'dark'">
            <i data-lucide="moon" class="w-5 h-5 text-indigo-400"></i>
        </template>
        <template x-if="theme === 'system'">
            <i data-lucide="monitor" class="w-5 h-5 text-slate-600 dark:text-slate-400"></i>
        </template>
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
         class="absolute right-0 mt-2 w-40 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
         x-cloak>
        <button @click="setTheme('light'); open = false" 
                class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                :class="{ 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20': theme === 'light' }">
            <i data-lucide="sun" class="w-4 h-4"></i>
            <span>Light</span>
        </button>
        <button @click="setTheme('dark'); open = false" 
                class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                :class="{ 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20': theme === 'dark' }">
            <i data-lucide="moon" class="w-4 h-4"></i>
            <span>Dark</span>
        </button>
        <button @click="setTheme('system'); open = false" 
                class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                :class="{ 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20': theme === 'system' }">
            <i data-lucide="monitor" class="w-4 h-4"></i>
            <span>System</span>
        </button>
    </div>
</div>

