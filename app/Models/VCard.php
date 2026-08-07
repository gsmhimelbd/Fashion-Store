<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class VCard extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = ['user_id','theme_id','name','slug','job_title','company','about','phone','whatsapp','email','website','location','status','is_published','available_for_work','seo','settings','published_at'];
    protected function casts(): array { return ['is_published'=>'boolean','available_for_work'=>'boolean','seo'=>'array','settings'=>'array','published_at'=>'datetime']; }
    public function getRouteKeyName(): string { return 'slug'; }
    public function scopePublished(Builder $query): Builder { return $query->where('is_published', true)->whereNotNull('published_at'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function theme(): BelongsTo { return $this->belongsTo(Theme::class); }
    public function socialLinks(): HasMany { return $this->hasMany(SocialLink::class)->orderBy('sort_order'); }
    public function paymentMethods(): HasMany { return $this->hasMany(PaymentMethod::class)->orderBy('sort_order'); }
    public function services(): HasMany { return $this->hasMany(Service::class)->orderBy('sort_order'); }
    public function portfolios(): HasMany { return $this->hasMany(Portfolio::class)->orderBy('sort_order'); }
    public function galleries(): HasMany { return $this->hasMany(Gallery::class)->orderBy('sort_order'); }
    public function cvFiles(): HasMany { return $this->hasMany(CvFile::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function analytics(): HasMany { return $this->hasMany(Analytics::class); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
}
