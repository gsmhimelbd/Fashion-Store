<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name'=>['required','string','max:120'],'email'=>['required','email:rfc,dns','max:190'],'phone'=>['nullable','string','max:40'],'starts_at'=>['required','date','after:now'],'timezone'=>['required','timezone'],'message'=>['nullable','string','max:2000']]; }
}
