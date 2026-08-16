<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $query = Order::with('items');

        if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $query->where('status', $statusFilter);
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts', 'statusFilter'));
    }

    public function show(int $id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return response()->json($order);
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->id} status updated to " . ucfirst($order->status),
                'status' => $order->status,
            ]);
        }

        return back()->with('success', "Order #{$order->id} status updated to " . ucfirst($order->status));
    }

    public function exportCsv(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $query = Order::with('items');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $orders = $query->latest()->get();

        $response = new StreamedResponse(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order ID',
                'Customer Name',
                'Phone',
                'WhatsApp',
                'District',
                'Upazila',
                'Address',
                'Payment Method',
                'Payment Number',
                'Transaction ID',
                'Subtotal (BDT)',
                'Delivery (BDT)',
                'Discount (BDT)',
                'Grand Total (BDT)',
                'Status',
                'Order Date',
            ]);

            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->id,
                    $o->customer_name,
                    $o->phone,
                    $o->whatsapp,
                    $o->district,
                    $o->upazila,
                    $o->address,
                    strtoupper($o->payment_method),
                    $o->payment_number ?? 'N/A',
                    $o->transaction_id ?? 'N/A',
                    $o->subtotal,
                    $o->delivery_charge,
                    $o->discount_amount,
                    $o->grand_total,
                    ucfirst($o->status),
                    $o->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        });

        $filename = 'orders-export-' . date('Y-m-d') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
