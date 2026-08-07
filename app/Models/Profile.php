<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Profile extends Model
{
    protected $fillable = ['user_id','job_title','company','bio','phone','whatsapp','website','address','city','country','timezone','settings'];
    protected function casts(): array { return ['settings' => 'array']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
