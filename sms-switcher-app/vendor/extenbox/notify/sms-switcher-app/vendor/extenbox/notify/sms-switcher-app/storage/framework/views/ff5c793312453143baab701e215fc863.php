<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 سیستم تست SMS - سوییچ بین پنل‌های پیامکی</title>
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
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from { top: -100px; opacity: 0; }
            to { top: 20px; opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Toast Notification -->
    <div id="toast" class="toast hidden px-6 py-3 rounded-lg shadow-lg text-white text-center"></div>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">📱 سیستم تست SMS</h1>
                <p class="text-gray-600">تست پکیج <span class="font-mono bg-gray-200 px-2 py-1 rounded">extenbox/notify</span> با قابلیت سوییچ بین پنل‌های پیامکی</p>
            </div>

            <!-- Status Bar -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-gray-500">پروایدر پیش‌فرض:</span>
                        <span class="badge bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold mr-2" id="defaultProvider">
                            <?php echo e($currentProvider); ?>

                        </span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">وضعیت:</span>
                        <span id="connectionStatus" class="px-3 py-1 rounded-full text-sm font-semibold">
                            ⏳ در انتظار
                        </span>
                    </div>
                </div>
            </div>

            <!-- فرم ارسال ساده -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center mb-4">
                    <span class="text-2xl ml-2">📤</span>
                    <h2 class="text-xl font-semibold">ارسال پیامک (تک پروایدر)</h2>
                </div>
                
                <form id="singleForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- شماره موبایل -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                📱 شماره موبایل:
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="09123456789" 
                                   required
                                   pattern="[0-9]{10,15}"
                                   title="شماره موبایل باید حداقل ۱۰ رقم باشد">
                            <p class="text-xs text-gray-500 mt-1">مثال: 09123456789</p>
                        </div>

                        <!-- انتخاب پروایدر -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                🔄 انتخاب پروایدر:
                            </label>
                            <select name="provider" 
                                    id="provider" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- پیش‌فرض (<?php echo e($currentProvider); ?>) --</option>
                                <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">خالی بگذارید تا از پیش‌فرض استفاده شود</p>
                        </div>
                    </div>

                    <!-- متن پیام -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            ✍️ متن پیام:
                        </label>
                        <textarea name="message" 
                                  id="message" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="متن پیام خود را وارد کنید..."
                                  required
                                  maxlength="500"></textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-gray-500">حداکثر ۵۰۰ کاراکتر</p>
                            <p class="text-xs text-gray-500" id="charCount">0 / 500</p>
                        </div>
                    </div>

                    <!-- دکمه ارسال -->
                    <button type="submit" 
                            id="singleSubmit"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        📨 ارسال پیام
                    </button>
                </form>

                <!-- نتیجه ارسال -->
                <div id="singleResult" class="mt-4 hidden"></div>
            </div>

            <!-- فرم Fallback -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center mb-4">
                    <span class="text-2xl ml-2">🔄</span>
                    <h2 class="text-xl font-semibold">ارسال با پروایدر پشتیبان (Fallback)</h2>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-yellow-800">
                        💡 در این حالت، اگر پروایدر اصلی خطا دهد، به صورت خودکار از پروایدر پشتیبان استفاده می‌شود.
                    </p>
                </div>
                
                <form id="fallbackForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- شماره موبایل -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                📱 شماره موبایل:
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   id="fphone" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="09123456789" 
                                   required>
                        </div>

                        <!-- پروایدر اصلی -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                🥇 پروایدر اصلی:
                            </label>
                            <select name="primary_provider" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e($key == 'ghasedak' ? 'selected' : ''); ?>>
                                        <?php echo e($name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- پروایدر پشتیبان -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                🥈 پروایدر پشتیبان:
                            </label>
                            <select name="fallback_provider" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e($key == 'mediana' ? 'selected' : ''); ?>>
                                        <?php echo e($name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <!-- متن پیام -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            ✍️ متن پیام:
                        </label>
                        <textarea name="message" 
                                  id="fmessage" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="متن پیام خود را وارد کنید..."
                                  required
                                  maxlength="500"></textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-gray-500">حداکثر ۵۰۰ کاراکتر</p>
                            <p class="text-xs text-gray-500" id="fcharCount">0 / 500</p>
                        </div>
                    </div>

                    <!-- دکمه ارسال -->
                    <button type="submit" 
                            id="fallbackSubmit"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        🔄 ارسال با پشتیبان
                    </button>
                </form>

                <!-- نتیجه -->
                <div id="fallbackResult" class="mt-4 hidden"></div>
            </div>

            <!-- اطلاعات پروایدرها -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-4">
                    <span class="text-2xl ml-2">ℹ️</span>
                    <h2 class="text-xl font-semibold">پروایدرهای پشتیبانی شده</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border rounded-lg p-3 text-center hover:shadow-md transition">
                            <div class="text-lg font-bold"><?php echo e($name); ?></div>
                            <div class="text-sm text-gray-500 font-mono"><?php echo e($key); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ==================== Utility Functions ====================
    
    // نمایش Toast
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };
        
        toast.className = `toast px-6 py-3 rounded-lg shadow-lg text-white text-center ${colors[type]}`;
        toast.textContent = message;
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // نمایش نتیجه
    function showResult(elementId, data, success) {
        const div = document.getElementById(elementId);
        const bgClass = success ? 'bg-green-50 border-green-400' : 'bg-red-50 border-red-400';
        const textClass = success ? 'text-green-800' : 'text-red-800';
        const icon = success ? '✅' : '❌';
        
        let html = `<div class="border ${bgClass} ${textClass} px-4 py-3 rounded-lg">`;
        html += `<div class="font-bold text-lg mb-2">${icon} ${data.success ? 'عملیات موفق' : 'خطا در عملیات'}</div>`;
        html += `<p class="mb-1"><strong>پیام:</strong> ${data.message}</p>`;
        
        if (data.provider || data.provider_used) {
            html += `<p class="mb-1"><strong>پروایدر:</strong> <span class="font-mono bg-gray-200 px-2 py-1 rounded">${data.provider || data.provider_used}</span></p>`;
        }
        
        if (data.primary_failed) {
            html += `<p class="mb-1"><strong>پروایدر اصلی ناموفق:</strong> <span class="font-mono bg-red-200 px-2 py-1 rounded">${data.primary_failed}</span></p>`;
        }
        
        if (data.providers_tried) {
            html += `<p class="mb-1"><strong>پروایدرهای تست شده:</strong> ${data.providers_tried.join(' → ')}</p>`;
        }
        
        if (data.response && data.response.status_code) {
            html += `<p class="mb-1"><strong>کد وضعیت:</strong> ${data.response.status_code}</p>`;
        }
        
        if (data.response && data.response.message && data.response.message !== data.message) {
            html += `<p class="mb-1 text-sm opacity-75"><strong>جزئیات:</strong> ${data.response.message}</p>`;
        }
        
        html += `</div>`;
        
        div.innerHTML = html;
        div.classList.remove('hidden');
    }

    // شمارش کاراکتر
    document.getElementById('message').addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('charCount').textContent = `${count} / 500`;
    });

    document.getElementById('fmessage').addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('fcharCount').textContent = `${count} / 500`;
    });

    // ==================== Single SMS Form ====================
    
    document.getElementById('singleForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('singleSubmit');
        const resultDiv = document.getElementById('singleResult');
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loader inline-block ml-2"></span> در حال ارسال...';
        resultDiv.classList.add('hidden');
        
        try {
            const formData = new FormData(this);
            const response = await fetch('/send-sms', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showResult('singleResult', data, true);
                showToast('✅ پیام با موفقیت ارسال شد', 'success');
            } else {
                showResult('singleResult', data, false);
                showToast('❌ خطا در ارسال پیام', 'error');
            }
            
        } catch (error) {
            showResult('singleResult', {
                success: false,
                message: 'خطا در ارتباط با سرور: ' + error.message,
                provider: document.getElementById('provider').value || 'default'
            }, false);
            showToast('❌ خطا در ارتباط با سرور', 'error');
            
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '📨 ارسال پیام';
        }
    });

    // ==================== Fallback SMS Form ====================
    
    document.getElementById('fallbackForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('fallbackSubmit');
        const resultDiv = document.getElementById('fallbackResult');
        const primaryProvider = this.querySelector('[name="primary_provider"]').value;
        const fallbackProvider = this.querySelector('[name="fallback_provider"]').value;
        
        // Validate different providers
        if (primaryProvider === fallbackProvider) {
            showToast('⚠️ پروایدر اصلی و پشتیبان نمی‌توانند یکسان باشند', 'warning');
            return;
        }
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loader inline-block ml-2"></span> در حال ارسال با Fallback...';
        resultDiv.classList.add('hidden');
        
        try {
            const formData = new FormData(this);
            const response = await fetch('/send-sms-fallback', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showResult('fallbackResult', data, true);
                if (data.primary_failed) {
                    showToast('⚠️ پروایدر اصلی خطا داد، از پشتیبان استفاده شد', 'warning');
                } else {
                    showToast('✅ پیام با موفقیت ارسال شد', 'success');
                }
            } else {
                showResult('fallbackResult', data, false);
                showToast('❌ هر دو پروایدر خطا دادند', 'error');
            }
            
        } catch (error) {
            showResult('fallbackResult', {
                success: false,
                message: 'خطا در ارتباط با سرور: ' + error.message,
                providers_tried: [primaryProvider, fallbackProvider]
            }, false);
            showToast('❌ خطا در ارتباط با سرور', 'error');
            
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '🔄 ارسال با پشتیبان';
        }
    });

    // ==================== Check Connection Status ====================
    
    async function checkConnection() {
        const statusEl = document.getElementById('connectionStatus');
        statusEl.innerHTML = '⏳ در حال بررسی...';
        statusEl.className = 'px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-600';
        
        try {
            const response = await fetch('/test-sms');
            const data = await response.json();
            
            if (data.response && data.response.status_code) {
                statusEl.innerHTML = '🟢 آنلاین';
                statusEl.className = 'px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800';
            } else {
                statusEl.innerHTML = '🟡 نیاز به تنظیم';
                statusEl.className = 'px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800';
            }
        } catch (error) {
            statusEl.innerHTML = '🔴 خطا در ارتباط';
            statusEl.className = 'px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800';
        }
    }
    
    // Check on page load
    checkConnection();
    </script>
</body>
</html><?php /**PATH D:\project\Personal\Notify\sms-switcher-app\resources\views/sms-switcher.blade.php ENDPATH**/ ?>