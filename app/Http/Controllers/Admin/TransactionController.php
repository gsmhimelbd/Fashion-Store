<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class TransactionController extends Controller
{
    public function verify(Request $request,Transaction $transaction):JsonResponse { abort_unless(in_array($transaction->status,['pending','review'],true),422,'Only pending payments may be verified.'); $transaction->update(['status'=>'paid','paid_at'=>$transaction->paid_at??now(),'verified_at'=>now(),'verified_by'=>$request->user()->id]); return response()->json(['message'=>'Payment verified.','data'=>$transaction->fresh()]); }
    public function reject(Request $request,Transaction $transaction):JsonResponse { $data=$request->validate(['reason'=>['required','string','max:500']]); $transaction->update(['status'=>'failed','verified_at'=>now(),'verified_by'=>$request->user()->id,'metadata'=>array_merge($transaction->metadata??[],['rejection_reason'=>$data['reason']])]); return response()->json(['message'=>'Payment rejected.']); }
}
