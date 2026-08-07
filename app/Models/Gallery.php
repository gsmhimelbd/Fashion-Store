<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Gallery extends Model implements HasMedia { use InteractsWithMedia; protected $fillable=['vcard_id','title','caption','is_visible','sort_order']; protected function casts():array{return ['is_visible'=>'boolean'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
