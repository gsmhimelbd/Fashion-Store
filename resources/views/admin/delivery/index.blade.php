@extends('layouts.admin')

@section('page_title', 'Bangladesh Delivery Zones & Rates')

@section('content')

<div class="space-y-6 text-xs" x-data="deliveryManager()">
    
    <!-- General Delivery Settings -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 max-w-4xl">
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-50 text-indigo-700">🚚 All Bangladesh Delivery Zones</span>
                <h3 class="text-base font-extrabold text-slate-900 font-serif mt-1">Delivery Charge Rates & Courier Setup</h3>
                <p class="text-slate-500 mt-0.5">Customize specific delivery charges and estimated delivery days for all districts in Bangladesh.</p>
            </div>
            <button type="button" @click="openAddZoneModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Add Delivery Zone
            </button>
        </div>

        <form method="POST" action="/admin-panel/settings" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Inside Tangail District (৳) *</label>
                    <input type="number" name="delivery_charge_tangail" value="{{ \App\Models\Setting::get('delivery_charge_tangail', '50') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none font-bold text-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Inside Dhaka Metro Area (৳) *</label>
                    <input type="number" name="delivery_charge_dhaka" value="{{ \App\Models\Setting::get('delivery_charge_dhaka', '80') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none font-bold text-indigo-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">All Other 62 Districts / National (৳) *</label>
                    <input type="number" name="delivery_charge_other" value="{{ \App\Models\Setting::get('delivery_charge_other', '150') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none font-bold text-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Free Delivery Minimum Cart Total (৳)</label>
                    <input type="number" name="free_delivery_threshold" value="{{ \App\Models\Setting::get('free_delivery_threshold', '2000') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Default Courier Partner</label>
                    <select name="default_courier" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                        <option value="steadfast">Steadfast Courier Bangladesh</option>
                        <option value="pathao">Pathao Courier</option>
                        <option value="redx">RedX Logistics</option>
                        <option value="sundarban">Sundarban Courier Service</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save Delivery Configuration</button>
        </form>
    </div>

    <!-- 64 Districts Specific Rates Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4 max-w-4xl">
        <h3 class="font-bold text-sm text-slate-900 uppercase">Division & District Delivery Rate Master</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3">Zone / Division</th>
                        <th class="p-3">Districts Included</th>
                        <th class="p-3">Delivery Rate</th>
                        <th class="p-3">Estimated Time</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-xs">
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-bold text-slate-900">Tangail Home Zone</td>
                        <td class="p-3 text-slate-600">Tangail Sadar, Mirzapur, Kalihati, Ghatail, Madhupur, Sakhipur, Dhanbari, Gopalpur, Delduar, Nagarpur, Bhuapur, Basail</td>
                        <td class="p-3 font-extrabold text-emerald-600">৳50.00</td>
                        <td class="p-3 text-slate-500 font-medium">Within 24 Hours</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-bold text-slate-900">Dhaka Division (Metro)</td>
                        <td class="p-3 text-slate-600">Dhaka City, Dhanmondi, Gulshan, Mirpur, Uttara, Gazipur, Narayanganj, Savar, Keraniganj</td>
                        <td class="p-3 font-extrabold text-indigo-600">৳80.00</td>
                        <td class="p-3 text-slate-500 font-medium">1 - 2 Business Days</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-bold text-slate-900">Chittagong & Sylhet Division</td>
                        <td class="p-3 text-slate-600">Chittagong, Cox's Bazar, Sylhet, Moulvibazar, Habiganj, Sunamganj, Comilla, Feni, Brahmanbaria, Noakhali</td>
                        <td class="p-3 font-extrabold text-indigo-600">৳130.00</td>
                        <td class="p-3 text-slate-500 font-medium">2 - 3 Business Days</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-bold text-slate-900">Rajshahi & Rangpur Division</td>
                        <td class="p-3 text-slate-600">Rajshahi, Bogra, Pabna, Natore, Sirajganj, Rangpur, Dinajpur, Gaibandha, Kurigram, Lalmonirhat, Nilphamari</td>
                        <td class="p-3 font-extrabold text-indigo-600">৳140.00</td>
                        <td class="p-3 text-slate-500 font-medium">2 - 3 Business Days</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-bold text-slate-900">Khulna & Barisal Division</td>
                        <td class="p-3 text-slate-600">Khulna, Jessore, Kushtia, Satkhira, Bagerhat, Barisal, Bhola, Jhalokati, Patuakhali, Pirojpur</td>
                        <td class="p-3 font-extrabold text-indigo-600">৳150.00</td>
                        <td class="p-3 text-slate-500 font-medium">2 - 3 Business Days</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function deliveryManager() {
    return {
        openAddZoneModal() {
            showToast('You can update zone delivery charges directly in the form above.', 'info');
        }
    }
}
</script>

@endsection
