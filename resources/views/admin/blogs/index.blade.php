@extends('layouts.admin')

@section('page_title', 'Blog Posts & Cover Photos Manager')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-xs" x-data="blogManager()">
    
    <!-- Blog Post Create / Edit Form -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider" x-text="isEdit ? 'Edit Blog Article' : 'Write New Blog Article'"></h3>
            
            <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <input type="hidden" name="id" x-model="form.id">

                <!-- Title -->
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Article Title *</label>
                    <input type="text" name="title" x-model="form.title" required placeholder="e.g. Top 5 Luxury Watches in 2026" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-indigo-500">
                </div>

                <!-- Category & Author -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Category *</label>
                        <select name="category" x-model="form.category" class="w-full border border-slate-300 rounded-xl px-3 py-2 bg-white outline-none">
                            <option value="Buying Guide">Buying Guide</option>
                            <option value="Tips & Tricks">Tips & Tricks</option>
                            <option value="Comparison">Comparison</option>
                            <option value="B2B & Wholesale">B2B & Wholesale</option>
                            <option value="News & Trends">News & Trends</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Author Name</label>
                        <input type="text" name="author" x-model="form.author" placeholder="OnlineBdMart Editorial" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none">
                    </div>
                </div>

                <!-- Cover Photo File Upload from Local Device -->
                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">Cover Photo (Upload from Local Device) *</label>
                    <div class="border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-50 relative">
                        <input type="file" name="cover_photo" @change="previewCoverPhoto($event)" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-1">
                            <i class="fas fa-cloud-arrow-up text-2xl text-indigo-500"></i>
                            <p class="font-bold text-slate-700">Click to choose image from local device</p>
                            <p class="text-[10px] text-slate-400">JPEG, PNG, WebP, SVG (Recommended: 1200x630)</p>
                        </div>
                    </div>

                    <!-- Live Image Preview -->
                    <template x-if="coverPreview || form.image_path">
                        <div class="relative rounded-2xl overflow-hidden border aspect-video bg-slate-100 mt-2">
                            <img :src="coverPreview || ('/' + form.image_path)" class="w-full h-full object-cover">
                            <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded backdrop-blur-sm">Current Cover</span>
                        </div>
                    </template>

                    <!-- Or select preset local image -->
                    <div>
                        <label class="font-bold text-slate-500 text-[11px] block mb-1">Or Pick From Local Uploads:</label>
                        <select name="existing_image" x-model="form.image_path" class="w-full border rounded-xl px-3 py-1.5 bg-white outline-none text-[11px]">
                            <option value="uploads/hero-banner-1.svg">Cover 1 (Watches & Leather Style)</option>
                            <option value="uploads/hero-banner-2.svg">Cover 2 (Flash Deals Green Style)</option>
                            <option value="uploads/hero-banner-3.svg">Cover 3 (Wholesale Amber Style)</option>
                            <option value="uploads/luxury-watch.svg">Luxury Watch Theme</option>
                            <option value="uploads/leather-wallet.svg">Leather Wallet Theme</option>
                            <option value="uploads/polaroid-sunglasses.svg">Sunglasses Theme</option>
                        </select>
                    </div>
                </div>

                <!-- Summary Excerpt -->
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Summary / Excerpt (Short description)</label>
                    <textarea name="summary" x-model="form.summary" rows="2" placeholder="Brief 2-sentence preview of this article..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2 outline-none"></textarea>
                </div>

                <!-- Full Content -->
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Full Article Content *</label>
                    <textarea name="content" x-model="form.content" required rows="5" placeholder="Write the full guide / article paragraphs here..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2 outline-none"></textarea>
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2 font-bold cursor-pointer">
                        <input type="checkbox" name="is_published" x-model="form.is_published" value="1" class="rounded text-indigo-600">
                        <span>Published (Live on /blog page)</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow transition" x-text="isEdit ? 'Update Article' : 'Publish Article'"></button>
                    <button type="button" x-show="isEdit" @click="resetForm()" class="px-4 py-3 border border-slate-300 rounded-xl font-bold hover:bg-slate-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Blog Articles List -->
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase">Published Articles ({{ count($blogs) }})</h3>
            <a href="{{ route('blog.index') }}" target="_blank" class="text-indigo-600 font-bold hover:underline flex items-center gap-1">
                <i class="fas fa-external-link-alt text-[10px]"></i> View Public Blog
            </a>
        </div>

        <div class="space-y-4">
            @forelse($blogs as $blog)
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <img src="{{ asset($blog->image_path ?: 'uploads/hero-banner-1.svg') }}" class="w-28 h-20 object-cover rounded-2xl border bg-slate-100 shrink-0">
                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $blog->category }}</span>
                        <h4 class="font-extrabold text-slate-900 text-sm line-clamp-1">{{ $blog->title }}</h4>
                        <p class="text-slate-400 text-[11px] line-clamp-1">{{ $blog->summary }}</p>
                        <span class="text-[10px] text-slate-400 block font-mono">Published by {{ $blog->author }} • {{ $blog->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto border-t sm:border-0 pt-3 sm:pt-0 border-slate-100">
                    <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="p-2 text-slate-400 hover:text-slate-700" title="Preview Article">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" @click="editBlog({{ json_encode($blog) }})" class="p-2 text-indigo-600 hover:text-indigo-800" title="Edit Article">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.blogs.destroy', $blog->id) }}" onsubmit="return confirm('Delete this blog post?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700" title="Delete">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-white p-12 rounded-3xl border text-center text-slate-400">
                No blog articles published yet. Use the form on the left to write your first guide!
            </div>
            @endforelse
        </div>

        <div class="pt-4 flex justify-center">
            {{ $blogs->links() }}
        </div>
    </div>

</div>

<script>
function blogManager() {
    return {
        isEdit: false,
        coverPreview: null,
        formAction: '{{ route('admin.blogs.store') }}',
        form: {
            id: null,
            title: '',
            category: 'Buying Guide',
            author: 'OnlineBdMart Editorial',
            summary: '',
            content: '',
            image_path: 'uploads/hero-banner-1.svg',
            is_published: true
        },

        previewCoverPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.coverPreview = URL.createObjectURL(file);
            }
        },

        editBlog(b) {
            this.isEdit = true;
            this.formAction = '/admin-panel/blogs/' + b.id;
            this.coverPreview = null;
            this.form = {
                id: b.id,
                title: b.title || '',
                category: b.category || 'Buying Guide',
                author: b.author || 'OnlineBdMart Editorial',
                summary: b.summary || '',
                content: b.content || '',
                image_path: b.image_path || 'uploads/hero-banner-1.svg',
                is_published: Boolean(b.is_published)
            };
        },

        resetForm() {
            this.isEdit = false;
            this.coverPreview = null;
            this.formAction = '{{ route('admin.blogs.store') }}';
            this.form = {
                id: null,
                title: '',
                category: 'Buying Guide',
                author: 'OnlineBdMart Editorial',
                summary: '',
                content: '',
                image_path: 'uploads/hero-banner-1.svg',
                is_published: true
            };
        }
    }
}
</script>

@endsection
