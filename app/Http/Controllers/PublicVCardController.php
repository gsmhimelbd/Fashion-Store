<?php
namespace App\Http\Controllers;
use App\Repositories\VCardRepositoryInterface;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
class PublicVCardController extends Controller
{
    public function __construct(private VCardRepositoryInterface $cards, private AnalyticsService $analytics) {}
    public function __invoke(Request $request, string $slug): View
    {
        $vcard=$this->cards->findPublishedBySlug($slug);
        $this->analytics->record($vcard,'profile_view',$request);
        return view('vcards.show',compact('vcard'));
    }
}
