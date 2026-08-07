<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Portfolio extends Model implements HasMedia { use InteractsWithMedia; protected $fillable=['vcard_id','title','category','description','project_url','completed_at','is_visible','sort_order']; protected function casts():array{return ['completed_at'=>'date','is_visible'=>'boolean'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
