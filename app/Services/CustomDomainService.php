<?php
namespace App\Services;
use App\Models\CustomDomain;
use App\Models\User;
use App\Models\VCard;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class CustomDomainService
{
    public function register(User $user,VCard $vcard,string $domain):CustomDomain { $domain=strtolower(trim(preg_replace('#^https?://#','',$domain),'/')); if(!filter_var('https://'.$domain,FILTER_VALIDATE_URL)) throw ValidationException::withMessages(['domain'=>'Enter a valid domain name.']); return CustomDomain::updateOrCreate(['domain'=>$domain],['user_id'=>$user->id,'vcard_id'=>$vcard->id,'status'=>'pending','verification_token'=>Str::random(48),'ssl_status'=>'pending']); }
    public function markVerified(CustomDomain $domain):CustomDomain { $domain->update(['status'=>'verified','verified_at'=>now(),'ssl_status'=>'provisioning']); return $domain->fresh(); }
}
