<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Subscription extends Model { protected $fillable=['user_id','plan_id','provider','provider_id','status','billing_cycle','trial_ends_at','starts_at','ends_at','renews_at','cancelled_at','metadata']; protected function casts():array{return ['trial_ends_at'=>'datetime','starts_at'=>'datetime','ends_at'=>'datetime','renews_at'=>'datetime','cancelled_at'=>'datetime','metadata'=>'array'];} public function user():BelongsTo{return $this->belongsTo(User::class);} public function plan():BelongsTo{return $this->belongsTo(Plan::class);} }
