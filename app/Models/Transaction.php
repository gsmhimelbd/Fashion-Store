<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Transaction extends Model { protected $fillable=['user_id','subscription_id','reference','provider','provider_reference','type','amount','currency','status','paid_at','verified_at','verified_by','metadata']; protected function casts():array{return ['amount'=>'decimal:2','paid_at'=>'datetime','verified_at'=>'datetime','metadata'=>'array'];} public function user():BelongsTo{return $this->belongsTo(User::class);} public function subscription():BelongsTo{return $this->belongsTo(Subscription::class);} }
