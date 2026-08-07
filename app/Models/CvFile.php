<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class CvFile extends Model implements HasMedia { use InteractsWithMedia; protected $fillable=['vcard_id','label','version','is_active','download_count']; protected function casts():array{return ['is_active'=>'boolean'];} public function vcard():BelongsTo{return $this->belongsTo(VCard::class);} }
