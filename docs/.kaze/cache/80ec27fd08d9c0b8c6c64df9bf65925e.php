<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="/" class="text-xl font-bold text-gray-800">
                    <?php echo e($config['site_name'] ?? 'Kaze'); ?>

                </a>
            </div>
            <div class="flex items-center md:hidden">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav><?php /**PATH /Users/takeshi/dev/my/takeshiyu.github.io/vendor/takeshiyu/kaze/resources/views/_nav.blade.php ENDPATH**/ ?>