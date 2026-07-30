<x-site-layout title="สร้างองค์กร">
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('org.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> องค์กรของฉัน</a>
        <h1 class="text-2xl font-bold mt-2">สร้างองค์กร</h1>

        <form method="POST" action="{{ route('org.store') }}" class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-6 space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium mb-1">ชื่อองค์กร <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="เช่น บริษัท ตัวอย่าง จำกัด"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="seats" class="block text-sm font-medium mb-1">จำนวนที่นั่ง (สมาชิกสูงสุด) <span class="text-red-500">*</span></label>
                <input type="number" name="seats" id="seats" value="{{ old('seats', 10) }}" min="1" required
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('seats')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">สร้างองค์กร</button>
        </form>
    </div>
</x-site-layout>
