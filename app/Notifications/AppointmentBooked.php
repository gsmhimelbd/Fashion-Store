<?php
namespace App\Notifications;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
class AppointmentBooked extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public Appointment $appointment, public string $recipient) {}
    public function via(object $notifiable): array { return $notifiable instanceof User ? ['mail','database'] : ['mail']; }
    public function toMail(object $notifiable): MailMessage { $owner=$this->recipient==='owner'; return (new MailMessage)->subject($owner?'New appointment booked':'Your appointment is confirmed')->greeting($owner?'You have a new appointment':"Hi {$this->appointment->name}")->line($this->appointment->starts_at->timezone($this->appointment->timezone)->format('l, F j \\a\\t g:i A T'))->line($this->appointment->message ?: 'No additional message was included.')->action('View appointment',url('/dashboard#appointments')); }
    public function toArray(object $notifiable): array { return ['appointment_id'=>$this->appointment->id,'vcard_id'=>$this->appointment->vcard_id,'starts_at'=>$this->appointment->starts_at]; }
}
