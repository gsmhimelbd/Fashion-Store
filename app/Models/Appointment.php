<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Appointment extends Model { protected $fillable=['vcard_id','name','email','phone','starts_at','ends_at','timezone','message','status','meeting_url','metadata']; protected function casts():array{return ['starts_at'=>'datetime','ends_at'=>'datetime','metadata'=>'array'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
