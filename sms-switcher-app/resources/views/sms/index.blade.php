@extends('layouts.app')

@section('title', 'ارسال پیامک')

@section('content')

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">📤</span>
            <h2 class="text-lg font-semibold text-gray-800">ارسال پیامک</h2>
        </div>

        <form id="singleForm" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="شماره موبایل" name="phone" placeholder="09123456789" required />
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        پروایدر
                    </label>
                    <select name="provider"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- پیش‌فرض --</option>
                        @foreach($activeProviders as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @if(empty($activeProviders))
                        <p class="text-xs text-red-500 mt-1">
                            ⚠️ هیچ پروایدر فعالی نیست.
                            <a href="{{ route('settings') }}" class="underline">تنظیمات</a>
                        </p>
                    @endif
                </div>
                <x-textarea label="متن پیام" name="message" placeholder="متن پیام خود را وارد کنید..." maxlength="500"
                    required />
            </div>

            <x-button type="submit" color="blue" id="singleSubmit">
                📨 ارسال پیام
            </x-button>
        </form>

        <div id="singleResult" class="mt-4 hidden"></div>
    </div>

    @if(count($activeProviders) >= 2)
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">🔄</span>
                <h2 class="text-lg font-semibold text-gray-800">ارسال با پروایدر پشتیبان (Fallback)</h2>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-yellow-800">
                    💡 اگر پروایدر اصلی خطا دهد، به صورت خودکار از پروایدر پشتیبان استفاده می‌شود.
                    <br>
                    <a href="{{ route('settings') }}" class="text-blue-600 hover:underline font-medium">
                        ⚙️ تنظیم پروایدرها
                    </a>
                </p>
            </div>

            <form id="fallbackForm" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="شماره موبایل" name="phone" placeholder="09123456789" required />
                    <x-textarea label="متن پیام" name="message" placeholder="متن پیام خود را وارد کنید..." maxlength="500"
                        required />
                </div>

                <x-button type="submit" color="green" id="fallbackSubmit">
                    🔄 ارسال با پشتیبان
                </x-button>
            </form>

            <div id="fallbackResult" class="mt-4 hidden"></div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="text-center text-gray-500 py-4">
                <span class="text-xl">🔒</span>
                <p class="mt-2">برای استفاده از قابلیت Fallback حداقل به <strong>۲ پروایدر فعال</strong> نیاز دارید.</p>
                <a href="{{ route('settings') }}" class="text-blue-500 hover:underline text-sm mt-1 inline-block">
                    ⚙️ تنظیم پروایدرها
                </a>
            </div>
        </div>
    @endif


    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">ℹ️</span>
            <h2 class="text-lg font-semibold text-gray-800">پروایدرهای پشتیبانی شده</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach($providers as $key => $name)
                <div class="border rounded-lg p-3 text-center">
                    <div class="font-semibold text-gray-800 text-sm">{{ $name }}</div>
                    <div class="text-xs text-gray-400 font-mono mt-1">{{ $key }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('scripts')
    <script>
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
@endsection