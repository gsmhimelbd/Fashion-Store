<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CustomDomain extends Model { protected $fillable=['user_id','vcard_id','domain','status','verification_token','verified_at','ssl_status','ssl_expires_at','is_primary']; protected function casts():array{return ['verified_at'=>'datetime','ssl_expires_at'=>'datetime','is_primary'=>'boolean'];} public function user():BelongsTo{return $this->belongsTo(User::class);} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
