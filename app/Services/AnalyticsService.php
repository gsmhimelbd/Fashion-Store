<?php
namespace App\Services;
use App\Models\Analytics;
use App\Models\VCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class AnalyticsService
{
    public function record(VCard $vcard, string $event, Request $request, ?string $target=null): void
    {
        Analytics::create(['vcard_id'=>$vcard->id,'event'=>$event,'target'=>$target,'session_id'=>$request->session()->get('analytics_id',fn()=>tap((string)Str::uuid(),fn($id)=>$request->session()->put('analytics_id',$id))),'ip_hash'=>hash_hmac('sha256',(string)$request->ip(),config('app.key')),'referrer'=>$request->headers->get('referer'),'device'=>$this->device($request->userAgent()),'occurred_at'=>now()]);
    }
    private function device(?string $agent): string { return preg_match('/Mobile|Android|iPhone/i',(string)$agent) ? 'mobile' : 'desktop'; }
}
