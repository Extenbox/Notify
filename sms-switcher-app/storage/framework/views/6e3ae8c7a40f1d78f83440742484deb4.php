<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['color' => 'blue', 'type' => 'submit', 'id' => null]));

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

foreach (array_filter((['color' => 'blue', 'type' => 'submit', 'id' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $colors = [
        'blue'  => 'bg-blue-500 hover:bg-blue-600',
        'green' => 'bg-green-500 hover:bg-green-600',
    ];
?>

<button
    type="<?php echo e($type); ?>"
    id="<?php echo e($id); ?>"
    class="w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed <?php echo e($colors[$color] ?? $colors['blue']); ?>"
    <?php echo e($attributes); ?>

>
    <?php echo e($slot); ?>

</button><?php /**PATH D:\project\Personal\Notify\sms-switcher-app\resources\views/components/button.blade.php ENDPATH**/ ?>