<?php
namespace App\Http\Controllers;
use App\Models\Analytics;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function __invoke(Request $request):View { $cards=$request->user()->vcards()->withCount(['appointments','leads'])->get(); $card=$cards->first(); $stats=$card?Analytics::where('vcard_id',$card->id)->where('occurred_at','>=',now()->subDays(7))->selectRaw("event, count(*) as aggregate")->groupBy('event')->pluck('aggregate','event'):collect(); return view('dashboard.index',compact('cards','card','stats')); }
}
