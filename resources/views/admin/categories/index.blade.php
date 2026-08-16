@extends('layouts.admin')

@section('page_title', 'Categories Management')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="categoryManager()">
    
    <!-- Add / Edit Category Form -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider" x-text="isEdit ? 'Edit Category' : 'Create New Category'"></h3>
            
            <form :action="formAction" method="POST" class="space-y-4 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Leather Goods" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Slug (auto-generated if blank)</label>
                    <input type="text" name="slug" x-model="form.slug" placeholder="e.g. leather-goods" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Font Awesome Icon Class</label>
                    <input type="text" name="icon" x-model="form.icon" placeholder="e.g. fa-wallet" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Description</label>
                    <textarea name="description" x-model="form.description" rows="3" placeholder="Category brief overview..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-md transition" x-text="isEdit ? 'Update Category' : 'Save Category'"></button>
                    <button type="button" x-show="isEdit" @click="resetForm()" class="px-4 py-2.5 border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category List Table -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">All Categories ({{ count($categories) }})</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">Icon</th>
                            <th class="py-3.5 px-4">Category Name</th>
                            <th class="py-3.5 px-4">Slug</th>
                            <th class="py-3.5 px-4 text-center">Products</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($categories as $cat)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center text-sm">
                                    <i class="fas {{ $cat->icon ?: 'fa-tag' }}"></i>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $cat->name }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">{{ $cat->slug }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">{{ $cat->products_count }} items</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="editCategory({{ json_encode($cat) }})" class="p-1.5 text-primary-600 hover:text-primary-800" title="Edit">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" onsubmit="return confirm('Delete this category?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function categoryManager() {
    return {
        isEdit: false,
        formAction: '{{ route('admin.categories.store') }}',
        form: { id: null, name: '', slug: '', icon: '', description: '' },

        editCategory(cat) {
            this.isEdit = true;
            this.formAction = '/admin/categories/' + cat.id;
            this.form = {
                id: cat.id,
                name: cat.name || '',
                slug: cat.slug || '',
                icon: cat.icon || '',
                description: cat.description || ''
            };
        },

        resetForm() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.categories.store') }}';
            this.form = { id: null, name: '', slug: '', icon: '', description: '' };
        }
    }
}
</script>

@endsection
