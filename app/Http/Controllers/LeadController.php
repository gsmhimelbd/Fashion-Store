<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreLeadRequest;
use App\Models\VCard;
use Illuminate\Http\JsonResponse;
class LeadController extends Controller
{
    public function store(StoreLeadRequest $request,VCard $vcard):JsonResponse { abort_unless($vcard->is_published,404); $data=$request->safe()->except('consent')+['source'=>$request->header('X-Lead-Source','profile'),'consented_at'=>now()]; $lead=$vcard->leads()->create($data); return response()->json(['message'=>'Details shared successfully.','data'=>$lead],201); }
}
