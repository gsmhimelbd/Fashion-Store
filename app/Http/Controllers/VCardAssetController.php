<?php
namespace App\Http\Controllers;
use App\Models\VCard;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class VCardAssetController extends Controller
{
    public function qr(VCard $vcard):Response { abort_unless($vcard->is_published,404); $svg=QrCode::format('svg')->size(900)->margin(2)->errorCorrection('H')->generate(route('vcards.show',$vcard->slug)); return response($svg)->header('Content-Type','image/svg+xml')->header('Content-Disposition',"attachment; filename=\"{$vcard->slug}-qr.svg\""); }
}
