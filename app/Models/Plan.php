<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Plan extends Model { protected $fillable=['name','slug','description','price_monthly','price_yearly','currency','features','limits','is_active','sort_order']; protected function casts():array{return ['price_monthly'=>'decimal:2','price_yearly'=>'decimal:2','features'=>'array','limits'=>'array','is_active'=>'boolean'];} public function subscriptions():HasMany{return $this->hasMany(Subscription::class);} }
