<?php
namespace App\Jobs;
use App\Models\Appointment;
use App\Notifications\AppointmentBooked;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
class SendAppointmentNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries=3;
    public function __construct(public Appointment $appointment) { $this->afterCommit(); }
    public function handle(): void { $this->appointment->load('vcard.user'); $this->appointment->vcard->user->notify(new AppointmentBooked($this->appointment,'owner')); Notification::route('mail',$this->appointment->email)->notify(new AppointmentBooked($this->appointment,'guest')); }
}
