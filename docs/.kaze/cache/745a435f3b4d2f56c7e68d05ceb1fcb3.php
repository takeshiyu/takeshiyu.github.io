<aside class="w-64 bg-white border-r border-gray-200 fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
       x-cloak
       x-show="true">
    <div class="h-full overflow-y-auto pt-16 md:pt-0">
        <nav class="px-4 py-4">
            
            <?php $__currentLoopData = $navigation ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($item['url']); ?>"
                   class="block py-2 px-4 text-gray-700 hover:bg-gray-100 rounded-md <?php echo e($item['active'] ? 'bg-gray-100' : ''); ?>">
                    <?php echo e($item['title']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>
</aside><?php /**PATH /Users/takeshi/dev/my/takeshiyu.github.io/vendor/takeshiyu/kaze/resources/views/_sidebar.blade.php ENDPATH**/ ?>