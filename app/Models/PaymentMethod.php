<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentMethod extends Model { protected $fillable=['vcard_id','provider','label','account_name','account_number','instructions','metadata','is_visible','sort_order']; protected function casts():array{return ['metadata'=>'encrypted:array','is_visible'=>'boolean'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
