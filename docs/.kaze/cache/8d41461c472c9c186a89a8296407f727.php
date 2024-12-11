<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Kaze Documentation'); ?></title>
    <meta name="description" content="<?php echo e($description ?? ''); ?>">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/tokyo-night-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
    <script>hljs.highlightAll();</script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    typography: {
                        DEFAULT: {
                            css: {
                                'pre': {
                                    backgroundColor: 'transparent',
                                    padding: 0,
                                    margin: '1.5rem 0',
                                },
                                'pre code': {
                                    backgroundColor: 'transparent',
                                    padding: '1.5rem',
                                    borderRadius: '0.5rem',
                                    color: 'inherit',
                                },
                                'code::before': { content: '""' },
                                'code::after': { content: '""' },
                            }
                        }
                    }
                }
            }
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php echo $__env->make('_nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="flex min-h-screen pt-16">
    <?php echo $__env->make('_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <main class="flex-1 ml-0 md:ml-64 transition-all duration-200">
        <div class="max-w-4xl mx-auto px-4 py-8">
            
            <div class="mb-8">
                <?php if($title): ?>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e($title); ?></h1>
                <?php endif; ?>

                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <?php if(isset($frontMatter['date'])): ?>
                        <time datetime="<?php echo e($frontMatter['date']); ?>">
                            <?php echo e(\Carbon\Carbon::parse($frontMatter['date'])->format('F j, Y')); ?>

                        </time>
                    <?php endif; ?>

                    <?php if(isset($frontMatter['description'])): ?>
                        <div class="text-gray-500"><?php echo e($frontMatter['description']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <article class="prose prose-slate max-w-none">
                <?php echo $content; ?>

            </article>
        </div>
    </main>
</div>


<div x-show="sidebarOpen"
     x-cloak
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm md:hidden"></div>


<footer class="bg-white/80 border-t border-gray-200/80 py-4 mt-8 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 text-center text-gray-600">
        Powered by <a href="https://github.com/your-repo/kaze" class="text-sky-600 hover:text-sky-700 transition-colors">Kaze</a>
    </div>
</footer>
</body>
</html>
<?php /**PATH /Users/takeshi/dev/my/takeshiyu.github.io/vendor/takeshiyu/kaze/resources/views/layouts/main.blade.php ENDPATH**/ ?>