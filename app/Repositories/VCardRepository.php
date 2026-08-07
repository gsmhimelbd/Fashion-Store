<?php
namespace App\Repositories;
use App\Models\VCard;
class VCardRepository implements VCardRepositoryInterface
{
    public function findPublishedBySlug(string $slug): VCard
    {
        return VCard::published()->with(['theme','socialLinks'=>fn($q)=>$q->where('is_visible',true),'paymentMethods'=>fn($q)=>$q->where('is_visible',true),'services'=>fn($q)=>$q->where('is_visible',true),'portfolios.media','galleries.media','cvFiles'=>fn($q)=>$q->where('is_active',true)])->where('slug',$slug)->firstOrFail();
    }
    public function findForUser(int $id, int $userId): VCard { return VCard::whereBelongsTo(\App\Models\User::findOrFail($userId))->findOrFail($id); }
}
