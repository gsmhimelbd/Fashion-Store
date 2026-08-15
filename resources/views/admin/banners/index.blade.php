@extends('layouts.admin')

@section('page_title', 'Hero Banners Management')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="bannerManager()">
    
    <!-- Upload / Edit Banner Form -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider" x-text="isEdit ? 'Edit Hero Banner' : 'Upload Hero Banner'"></h3>
            
            <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Banner Image *</label>
                    <input type="file" name="image" :required="!isEdit" accept="image/*" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700">
                    <p class="text-[10px] text-slate-400 mt-1">Recommended: 1600x700px SVG, WebP, JPEG, PNG</p>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Badge / Tag Text</label>
                    <input type="text" name="badge_text" x-model="form.badge_text" placeholder="e.g. ✨ NEW COLLECTION 2026" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Banner Heading Title</label>
                    <input type="text" name="title" x-model="form.title" placeholder="e.g. Elevate Your Everyday Style" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Subtitle / Tagline</label>
                    <input type="text" name="subtitle" x-model="form.subtitle" placeholder="e.g. Curated luxury fashion accessories..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Button CTA Text</label>
                        <input type="text" name="button_text" x-model="form.button_text" placeholder="Shop Now" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Button URL</label>
                        <input type="text" name="button_url" x-model="form.button_url" placeholder="/shop" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none focus:border-primary-500">
                    </div>
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                        <input type="checkbox" name="is_active" x-model="form.is_active" value="1" class="rounded text-primary-600 focus:ring-primary-500">
                        <span>Active on Homepage</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-md transition" x-text="isEdit ? 'Update Banner' : 'Publish Banner'"></button>
                    <button type="button" x-show="isEdit" @click="resetForm()" class="px-4 py-2.5 border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Banners List -->
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Active Hero Sliders ({{ count($banners) }})</h3>
        </div>

        <div class="space-y-4">
            @forelse($banners as $banner)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4 p-4">
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" class="w-32 h-20 object-cover rounded-xl bg-slate-900 border border-slate-200 shrink-0">
                    <div class="space-y-1">
                        @if($banner->badge_text)
                        <span class="text-[10px] font-bold text-primary-600 uppercase">{{ $banner->badge_text }}</span>
                        @endif
                        <h4 class="text-xs font-bold text-slate-900 line-clamp-1">{{ $banner->title ?: 'Untitled Banner' }}</h4>
                        <p class="text-[11px] text-slate-500 line-clamp-1">{{ $banner->subtitle }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto border-t sm:border-0 pt-3 sm:pt-0 border-slate-100">
                    <form method="POST" action="{{ route('admin.banners.toggle', $banner->id) }}">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>

                    <button type="button" @click="editBanner({{ json_encode($banner) }})" class="p-2 text-primary-600 hover:text-primary-800" title="Edit">
                        <i class="fas fa-pen-to-square"></i>
                    </button>

                    <form method="POST" action="{{ route('admin.banners.destroy', $banner->id) }}" onsubmit="return confirm('Delete this banner?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700" title="Delete">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                No hero banners configured yet.
            </div>
            @endforelse
        </div>
    </div>

</div>

<script>
function bannerManager() {
    return {
        isEdit: false,
        formAction: '{{ route('admin.banners.store') }}',
        form: { id: null, title: '', subtitle: '', badge_text: '', button_text: 'Shop Now', button_url: '/shop', is_active: true },

        editBanner(b) {
            this.isEdit = true;
            this.formAction = '/admin/banners/' + b.id;
            this.form = {
                id: b.id,
                title: b.title || '',
                subtitle: b.subtitle || '',
                badge_text: b.badge_text || '',
                button_text: b.button_text || 'Shop Now',
                button_url: b.button_url || '/shop',
                is_active: Boolean(b.is_active)
            };
        },

        resetForm() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.banners.store') }}';
            this.form = { id: null, title: '', subtitle: '', badge_text: '', button_text: 'Shop Now', button_url: '/shop', is_active: true };
        }
    }
}
</script>

@endsection
