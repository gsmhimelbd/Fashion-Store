<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\VCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class VCardController extends Controller
{
    public function index(Request $request):JsonResponse { return response()->json($request->user()->vcards()->with('theme')->latest()->paginate()); }
    public function show(Request $request,VCard $vcard):JsonResponse { abort_unless($vcard->user_id===$request->user()->id,403); return response()->json($vcard->load(['theme','socialLinks','services','portfolios.media'])); }
    public function update(Request $request,VCard $vcard):JsonResponse { abort_unless($vcard->user_id===$request->user()->id,403); $data=$request->validate(['name'=>['sometimes','string','max:120'],'job_title'=>['nullable','string','max:150'],'about'=>['nullable','string','max:3000'],'phone'=>['nullable','string','max:40'],'whatsapp'=>['nullable','string','max:40'],'email'=>['nullable','email'],'website'=>['nullable','url'],'available_for_work'=>['boolean'],'settings'=>['array']]); $vcard->update($data); return response()->json(['message'=>'Card updated.','data'=>$vcard->fresh()]); }
}
