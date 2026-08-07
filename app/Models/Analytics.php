<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Analytics extends Model { protected $table='analytics'; protected $fillable=['vcard_id','event','target','session_id','ip_hash','referrer','country','city','device','browser','occurred_at','metadata']; protected function casts():array{return ['occurred_at'=>'datetime','metadata'=>'array'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
