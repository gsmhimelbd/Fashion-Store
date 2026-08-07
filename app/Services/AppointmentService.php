<?php
namespace App\Services;
use App\Jobs\SendAppointmentNotifications;
use App\Models\Appointment;
use App\Models\VCard;
class AppointmentService
{
    public function book(VCard $vcard, array $data): Appointment
    {
        $appointment=$vcard->appointments()->create($data+['status'=>'confirmed']);
        SendAppointmentNotifications::dispatch($appointment);
        return $appointment;
    }
}
