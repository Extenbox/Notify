

<?php $__env->startSection('title', 'ارسال پیامک'); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <span class="text-xl">📤</span>
        <h2 class="text-lg font-semibold text-gray-800">ارسال پیامک</h2>
    </div>

    <form id="singleForm" class="space-y-4">
        <?php echo csrf_field(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['label' => 'شماره موبایل','name' => 'phone','placeholder' => '09123456789','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'شماره موبایل','name' => 'phone','placeholder' => '09123456789','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $attributes = $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $component = $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['label' => 'متن پیام','name' => 'message','placeholder' => 'متن پیام خود را وارد کنید...','maxlength' => '500','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'متن پیام','name' => 'message','placeholder' => 'متن پیام خود را وارد کنید...','maxlength' => '500','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
        </div>

        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','color' => 'blue','id' => 'singleSubmit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'blue','id' => 'singleSubmit']); ?>
            📨 ارسال پیام
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
    </form>

    <div id="singleResult" class="mt-4 hidden"></div>
</div>


<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <span class="text-xl">🔄</span>
        <h2 class="text-lg font-semibold text-gray-800">ارسال با پروایدر پشتیبان (Fallback)</h2>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
        <p class="text-sm text-yellow-800">
            💡 اگر پروایدر اصلی خطا دهد، به صورت خودکار از پروایدر پشتیبان استفاده می‌شود.
            <br>
            <a href="<?php echo e(route('settings')); ?>" class="text-blue-600 hover:underline font-medium">
                ⚙️ تنظیم پروایدرها
            </a>
        </p>
    </div>

    <form id="fallbackForm" class="space-y-4">
        <?php echo csrf_field(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['label' => 'شماره موبایل','name' => 'phone','placeholder' => '09123456789','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'شماره موبایل','name' => 'phone','placeholder' => '09123456789','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $attributes = $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $component = $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['label' => 'متن پیام','name' => 'message','placeholder' => 'متن پیام خود را وارد کنید...','maxlength' => '500','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'متن پیام','name' => 'message','placeholder' => 'متن پیام خود را وارد کنید...','maxlength' => '500','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
        </div>

        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','color' => 'green','id' => 'fallbackSubmit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'green','id' => 'fallbackSubmit']); ?>
            🔄 ارسال با پشتیبان
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
    </form>

    <div id="fallbackResult" class="mt-4 hidden"></div>
</div>


<div class="bg-white rounded-lg shadow-sm border p-6">
    <div class="flex items-center gap-2 mb-4">
        <span class="text-xl">ℹ️</span>
        <h2 class="text-lg font-semibold text-gray-800">پروایدرهای پشتیبانی شده</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border rounded-lg p-3 text-center">
                <div class="font-semibold text-gray-800 text-sm"><?php echo e($name); ?></div>
                <div class="text-xs text-gray-400 font-mono mt-1"><?php echo e($key); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// ==================== Single SMS ====================
document.getElementById('singleForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const submitBtn = document.getElementById('singleSubmit');
    const resultDiv = document.getElementById('singleResult');
    const formData = new FormData(e.target);

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loader inline-block ml-2"></span> در حال ارسال...';
    resultDiv.classList.add('hidden');

    try {
        const res = await fetch('/send-sms', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        showResult(resultDiv, data);
    } catch (error) {
        showResult(resultDiv, { success: false, message: 'خطا در ارتباط با سرور' });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '📨 ارسال پیام';
    }
});

// ==================== Fallback SMS ====================
document.getElementById('fallbackForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const submitBtn = document.getElementById('fallbackSubmit');
    const resultDiv = document.getElementById('fallbackResult');
    const formData = new FormData(e.target);

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loader inline-block ml-2"></span> در حال ارسال...';
    resultDiv.classList.add('hidden');

    try {
        const res = await fetch('/send-sms-fallback', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        showResult(resultDiv, data);
    } catch (error) {
        showResult(resultDiv, { success: false, message: 'خطا در ارتباط با سرور' });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '🔄 ارسال با پشتیبان';
    }
});

// ==================== Result Display ====================
function showResult(el, data) {
    const isSuccess = data.success;
    el.className = `mt-4 border rounded-lg p-4 ${isSuccess ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300'}`;
    el.innerHTML = `
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xl">${isSuccess ? '✅' : '❌'}</span>
            <span class="font-semibold ${isSuccess ? 'text-green-800' : 'text-red-800'}">${data.message}</span>
        </div>
        <div class="text-sm text-gray-600 space-y-1">
            ${data.provider ? `<p>پروایدر: <code class="bg-gray-200 px-2 py-0.5 rounded">${data.provider}</code></p>` : ''}
            ${data.response?.status_code ? `<p>کد وضعیت: ${data.response.status_code}</p>` : ''}
        </div>
    `;
}

// ==================== Connection Status ====================
(async () => {
    const el = document.getElementById('connectionStatus');
    try {
        const res = await fetch('/test-sms');
        const data = await res.json();
        el.textContent = data.response?.status_code ? '🟢 آنلاین' : '🟡 نیاز به تنظیم';
        el.className = 'px-3 py-1 rounded-full text-xs font-semibold ' + (data.response?.status_code ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700');
    } catch {
        el.textContent = '🔴 خطا';
        el.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700';
    }
})();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project\Personal\Notify\sms-switcher-app\resources\views/sms/index.blade.php ENDPATH**/ ?>