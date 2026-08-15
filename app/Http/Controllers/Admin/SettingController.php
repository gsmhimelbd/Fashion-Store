<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('setting_value', 'setting_key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $fields = [
            'store_name',
            'store_tagline',
            'contact_phone',
            'contact_email',
            'whatsapp_number',
            'delivery_charge_tangail',
            'delivery_charge_other',
            'free_delivery_threshold',
            'bkash_number',
            'bkash_type',
            'nagad_number',
            'nagad_type',
            'rocket_number',
            'rocket_type',
            'facebook_page',
            'instagram_page',
            'store_address',
            'currency',
            'announcement_bar',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, trim($request->input($field)));
            }
        }

        if ($request->hasFile('store_logo')) {
            $file = $request->file('store_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            Setting::set('store_logo', 'images/' . $filename);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Site settings updated successfully.');
    }
}
