<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\VCard;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request,VCard $vcard,AppointmentService $service):JsonResponse { abort_unless($vcard->is_published,404); $appointment=$service->book($vcard,$request->validated()); return response()->json(['message'=>'Appointment confirmed.','data'=>$appointment],201); }
}
