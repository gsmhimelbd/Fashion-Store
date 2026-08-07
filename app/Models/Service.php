<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Service extends Model { protected $fillable=['vcard_id','title','description','price','currency','cta_url','is_visible','sort_order']; protected function casts():array{return ['price'=>'decimal:2','is_visible'=>'boolean'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
