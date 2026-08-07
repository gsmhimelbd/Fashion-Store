<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
class DashboardController extends Controller
{
    public function __invoke():View { $metrics=['users'=>User::count(),'subscribers'=>Subscription::where('status','active')->count(),'mrr'=>Transaction::where('status','paid')->where('paid_at','>=',now()->startOfMonth())->sum('amount')]; return view('admin.dashboard',compact('metrics')); }
}
