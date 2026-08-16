<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contactPhone = Setting::get('contact_phone', '01775153740');
        $contactEmail = Setting::get('contact_email', 'support@fashionstore.com');
        $whatsapp = Setting::get('whatsapp_number', '01775153740');
        $facebook = Setting::get('facebook_page', 'https://facebook.com');
        $instagram = Setting::get('instagram_page', 'https://instagram.com');
        $address = Setting::get('store_address', 'Tangail Sadar, Tangail, Bangladesh');

        return view('contact', compact('contactPhone', 'contactEmail', 'whatsapp', 'facebook', 'instagram', 'address'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:1000',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Thank you, {$validated['name']}! Your message has been received. Our team will contact you shortly.",
            ]);
        }

        return back()->with('success', "Thank you, {$validated['name']}! Your message has been received.");
    }
}
