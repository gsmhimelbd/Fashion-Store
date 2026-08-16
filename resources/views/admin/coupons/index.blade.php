@extends('layouts.admin')

@section('page_title', 'Discount Coupons')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="couponManager()">
    
    <!-- Add / Edit Coupon Form -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider" x-text="isEdit ? 'Edit Coupon' : 'Create New Coupon'"></h3>
            
            <form :action="formAction" method="POST" class="space-y-4 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Coupon Promo Code *</label>
                    <input type="text" name="code" x-model="form.code" required placeholder="e.g. FASHION10" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 uppercase font-mono outline-none focus:border-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Discount Type *</label>
                        <select name="type" x-model="form.type" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none focus:border-primary-500 bg-white">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (৳)</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Discount Value *</label>
                        <input type="number" step="0.01" name="value" x-model="form.value" required placeholder="10" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none focus:border-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Min Spend (৳)</label>
                        <input type="number" step="0.01" name="min_spend" x-model="form.min_spend" placeholder="1000" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none focus:border-primary-500">
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Max Discount (৳)</label>
                        <input type="number" step="0.01" name="max_discount" x-model="form.max_discount" placeholder="500" class="w-full border border-slate-300 rounded-xl px-3 py-2 outline-none focus:border-primary-500">
                    </div>
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                        <input type="checkbox" name="is_active" x-model="form.is_active" value="1" class="rounded text-primary-600 focus:ring-primary-500">
                        <span>Active for Customer Use</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-md transition" x-text="isEdit ? 'Update Coupon' : 'Save Coupon'"></button>
                    <button type="button" x-show="isEdit" @click="resetForm()" class="px-4 py-2.5 border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Coupons Table -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Coupons List ({{ count($coupons) }})</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">Code</th>
                            <th class="py-3.5 px-4">Discount</th>
                            <th class="py-3.5 px-4">Min Spend</th>
                            <th class="py-3.5 px-4 text-center">Usage</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-primary-600 text-sm">{{ $coupon->code }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $coupon->type === 'percentage' ? $coupon->value . '%' : '৳' . number_format($coupon->value, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $coupon->min_spend ? '৳' . number_format($coupon->min_spend, 2) : 'No Min' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                {{ $coupon->times_used }} times
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $coupon->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="editCoupon({{ json_encode($coupon) }})" class="p-1.5 text-primary-600 hover:text-primary-800" title="Edit">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon->id) }}" onsubmit="return confirm('Delete coupon?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No discount coupons created yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function couponManager() {
    return {
        isEdit: false,
        formAction: '{{ route('admin.coupons.store') }}',
        form: { id: null, code: '', type: 'percentage', value: 10, min_spend: '', max_discount: '', is_active: true },

        editCoupon(c) {
            this.isEdit = true;
            this.formAction = '/admin/coupons/' + c.id;
            this.form = {
                id: c.id,
                code: c.code || '',
                type: c.type || 'percentage',
                value: c.value || '',
                min_spend: c.min_spend || '',
                max_discount: c.max_discount || '',
                is_active: Boolean(c.is_active)
            };
        },

        resetForm() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.coupons.store') }}';
            this.form = { id: null, code: '', type: 'percentage', value: 10, min_spend: '', max_discount: '', is_active: true };
        }
    }
}
</script>

@endsection
