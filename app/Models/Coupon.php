<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model { protected $fillable=['code','type','value','currency','max_redemptions','redemptions','starts_at','expires_at','applicable_plan_ids','is_active']; protected function casts():array{return ['value'=>'decimal:2','starts_at'=>'datetime','expires_at'=>'datetime','applicable_plan_ids'=>'array','is_active'=>'boolean'];} public function isAvailable():bool{return $this->is_active && (!$this->starts_at || $this->starts_at->isPast()) && (!$this->expires_at || $this->expires_at->isFuture()) && (!$this->max_redemptions || $this->redemptions < $this->max_redemptions);} }
