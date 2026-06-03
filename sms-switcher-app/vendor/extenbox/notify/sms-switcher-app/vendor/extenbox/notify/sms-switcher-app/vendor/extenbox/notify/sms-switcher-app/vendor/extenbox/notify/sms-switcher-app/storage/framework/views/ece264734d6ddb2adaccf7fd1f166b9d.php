<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'SMS Switcher'); ?> | Extenbox Notify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">📱 Extenbox Notify</h1>
                    <p class="text-sm text-gray-500">تست پکیج با قابلیت سوییچ پروایدر</p>
                </div>
                <a href="<?php echo e(route('settings')); ?>" 
                   class="inline-flex items-center gap-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                    ⚙️ <span>تنظیمات</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">وضعیت:</span>
                <span id="connectionStatus" class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                    ⏳ بررسی...
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="text-center py-6 text-sm text-gray-400 border-t mt-12">
        Extenbox Notify Package Test — Laravel <?php echo e(app()->version()); ?>

    </footer>

    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html><?php /**PATH D:\project\Personal\Notify\sms-switcher-app\resources\views/layouts/app.blade.php ENDPATH**/ ?>