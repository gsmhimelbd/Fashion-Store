<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Lead extends Model { protected $fillable=['vcard_id','name','email','phone','company','message','source','status','consented_at','metadata']; protected function casts():array{return ['consented_at'=>'datetime','metadata'=>'array'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
