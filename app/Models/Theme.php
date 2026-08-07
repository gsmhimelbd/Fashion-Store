<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Theme extends Model { protected $fillable=['name','slug','description','preview_image','schema','is_premium','is_active','sort_order']; protected function casts():array{return ['schema'=>'array','is_premium'=>'boolean','is_active'=>'boolean'];} public function vcards():HasMany{return $this->hasMany(VCard::class);} }
