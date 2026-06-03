@extends('layouts.app')

@section('title', 'تنظیمات پروایدرها')

@section('content')
<div class="space-y-6">
    
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">⚙️ تنظیمات پروایدرهای پیامکی</h2>
        <a href="{{ route('home') }}" class="text-blue-500 hover:underline text-sm">← بازگشت</a>
    </div>

    <div class="grid grid-cols-1 gap-4" id="providersContainer">
        @foreach($providers as $driver => $label)
            @php $stored = $storedSettings[$driver] ?? null; @endphp
            <div class="bg-white rounded-lg shadow-sm border p-5" data-driver="{{ $driver }}">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-semibold">{{ $label }}</span>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $driver }}</code>
                        @if($stored && $stored['is_default'])
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded">پیش‌فرض</span>
                        @endif
                        @if($stored && $stored['is_fallback'])
                            <span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded">پشتیبان</span>
                        @endif
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               class="sr-only peer active-toggle" 
                               data-driver="{{ $driver }}"
                               {{ $stored && $stored['is_active'] ? 'checked' : '' }}>
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>

                <form class="provider-form space-y-3" data-driver="{{ $driver }}">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach(($providerFields[$driver] ?? []) as $key => $field)
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    {{ $field['label'] }}
                                    @if($field['required'])
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <input 
                                    type="{{ $field['type'] }}"
                                    name="{{ $key }}"
                                    value="{{ $stored['config'][$key] ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    {{ $field['required'] ? 'required' : '' }}
                                />
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4 pt-2 border-t">
                        <label class="inline-flex items-center gap-1 text-sm">
                            <input type="radio" name="role" value="default" class="role-radio"
                                   {{ $stored && $stored['is_default'] ? 'checked' : '' }}>
                            <span>پیش‌فرض</span>
                        </label>
                        <label class="inline-flex items-center gap-1 text-sm">
                            <input type="radio" name="role" value="fallback" class="role-radio"
                                   {{ $stored && $stored['is_fallback'] ? 'checked' : '' }}>
                            <span>پشتیبان</span>
                        </label>
                        <label class="inline-flex items-center gap-1 text-sm">
                            <input type="radio" name="role" value="none" class="role-radio"
                                   {{ (!$stored || (!$stored['is_default'] && !$stored['is_fallback'])) ? 'checked' : '' }}>
                            <span>معمولی</span>
                        </label>

                        <button type="submit" class="mr-auto bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-lg transition">
                            💾 ذخیره
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>

</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.provider-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const driver = form.dataset.driver;
        const formData = new FormData(form);
        const activeToggle = document.querySelector(`.active-toggle[data-driver="${driver}"]`);
        
        const config = {};
        formData.forEach((value, key) => {
            if (key !== '_token' && key !== 'role') {
                config[key] = value;
            }
        });
        
        const role = formData.get('role');
        
        const data = {
            driver: driver,
            config: config,
            is_active: activeToggle.checked,
            is_default: role === 'default',
            is_fallback: role === 'fallback',
        };
        
        try {
            const res = await fetch('/save-setting', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });
            
            const result = await res.json();
            
            if (result.success) {
                alert('✅ ذخیره شد!');
                location.reload();
            } else {
                alert('❌ ' + result.message);
            }
            
        } catch (error) {
            alert('❌ خطا در ارتباط با سرور');
        }
    });
});
</script>
@endsection