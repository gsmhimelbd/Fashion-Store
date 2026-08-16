@extends('layouts.admin')

@section('page_title', 'Products Management')

@section('content')

<div x-data="productManager()">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl outline-none focus:border-primary-500">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Search
            </button>
        </form>

        <button type="button" @click="openCreateModal()" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-600/20 transition flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Item Preview</th>
                        <th class="py-3.5 px-4">Product Name</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4 text-center">Stock</th>
                        <th class="py-3.5 px-4 text-center">Featured</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4">
                            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-xl bg-slate-100 border border-slate-200">
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-900 line-clamp-1">{{ $product->name }}</p>
                            @if($product->sku)
                            <span class="text-[10px] font-mono text-slate-400 font-bold">SKU: {{ $product->sku }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-600">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            ৳{{ number_format($product->effective_price, 2) }}
                            @if($product->sale_price && $product->sale_price < $product->price)
                            <span class="text-[10px] text-slate-400 line-through block">৳{{ number_format($product->price, 2) }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold">
                            <span class="px-2.5 py-1 rounded-full text-[10px] {{ $product->stock > 5 ? 'bg-emerald-50 text-emerald-700' : ($product->stock > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ $product->stock }} in stock
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($product->is_featured)
                            <span class="text-emerald-500 font-bold"><i class="fas fa-check-circle text-base"></i></span>
                            @else
                            <span class="text-slate-300"><i class="fas fa-circle-dot text-base"></i></span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700" title="View Public Page">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" @click="openEditModal({{ json_encode($product) }})" class="p-1.5 text-primary-600 hover:text-primary-800" title="Edit Product">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete Product">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 flex justify-center">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Add / Edit Product Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-base font-extrabold text-slate-900 font-serif" x-text="isEdit ? 'Edit Product' : 'Add New Product'"></h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Product Title *</label>
                            <input type="text" name="name" x-model="form.name" required placeholder="e.g. Luxury Chronograph Watch" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Category *</label>
                            <select name="category_id" x-model="form.category_id" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">SKU Code</label>
                            <input type="text" name="sku" x-model="form.sku" placeholder="e.g. WAT-001" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Regular Price (৳) *</label>
                            <input type="number" step="0.01" name="price" x-model="form.price" required placeholder="3850.00" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Sale / Discount Price (৳)</label>
                            <input type="number" step="0.01" name="sale_price" x-model="form.sale_price" placeholder="3250.00" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Available Stock Count *</label>
                            <input type="number" name="stock" x-model="form.stock" required placeholder="25" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div class="flex items-center gap-6 pt-5">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                                <input type="checkbox" name="is_featured" x-model="form.is_featured" value="1" class="rounded text-primary-600 focus:ring-primary-500">
                                <span>Featured Item</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                                <input type="checkbox" name="is_active" x-model="form.is_active" value="1" class="rounded text-primary-600 focus:ring-primary-500">
                                <span>Active for Sale</span>
                            </label>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Short Summary (1-2 sentences)</label>
                            <input type="text" name="short_description" x-model="form.short_description" placeholder="Brief highlight of materials and specs..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Full Detailed Description</label>
                            <textarea name="description" x-model="form.description" rows="3" placeholder="Dimensions, water resistance, movement, warranty info..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Upload Product Images</label>
                            <input type="file" name="images[]" multiple accept="image/*" class="w-full border border-slate-300 rounded-xl px-3.5 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            <p class="text-[10px] text-slate-400 mt-1">Upload JPEG, PNG, WebP or SVG format.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="px-5 py-2.5 border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-md transition">
                            Save Product
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
function productManager() {
    return {
        modalOpen: false,
        isEdit: false,
        formAction: '{{ route('admin.products.store') }}',
        form: {
            id: null,
            name: '',
            category_id: '',
            sku: '',
            price: '',
            sale_price: '',
            stock: 10,
            is_featured: false,
            is_active: true,
            short_description: '',
            description: ''
        },

        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.products.store') }}';
            this.form = {
                id: null,
                name: '',
                category_id: '',
                sku: '',
                price: '',
                sale_price: '',
                stock: 10,
                is_featured: false,
                is_active: true,
                short_description: '',
                description: ''
            };
            this.modalOpen = true;
        },

        openEditModal(product) {
            this.isEdit = true;
            this.formAction = '/admin/products/' + product.id;
            this.form = {
                id: product.id,
                name: product.name || '',
                category_id: product.category_id || '',
                sku: product.sku || '',
                price: product.price || '',
                sale_price: product.sale_price || '',
                stock: product.stock || 0,
                is_featured: Boolean(product.is_featured),
                is_active: Boolean(product.is_active),
                short_description: product.short_description || '',
                description: product.description || ''
            };
            this.modalOpen = true;
        }
    }
}
</script>

@endsection
