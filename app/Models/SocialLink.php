<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SocialLink extends Model { protected $fillable=['vcard_id','platform','url','username','is_visible','sort_order']; protected function casts():array{return ['is_visible'=>'boolean'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
