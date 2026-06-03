<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'name', 'placeholder' => '', 'required' => false, 'pattern' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['label', 'name', 'placeholder' => '', 'required' => false, 'pattern' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div>
    <label for="<?php echo e($name); ?>" class="block text-sm font-medium text-gray-700 mb-1">
        <?php echo e($label); ?>

    </label>
    <input
        type="text"
        name="<?php echo e($name); ?>"
        id="<?php echo e($name); ?>"
        placeholder="<?php echo e($placeholder); ?>"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        <?php echo e($required ? 'required' : ''); ?>

        <?php echo e($pattern ? "pattern={$pattern}" : ''); ?>

        <?php echo e($attributes); ?>

    />
</div><?php /**PATH D:\project\Personal\Notify\sms-switcher-app\resources\views/components/input.blade.php ENDPATH**/ ?>