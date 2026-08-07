<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name'=>['required','string','max:120'],'email'=>['nullable','required_without:phone','email','max:190'],'phone'=>['nullable','required_without:email','string','max:40'],'company'=>['nullable','string','max:150'],'message'=>['nullable','string','max:2000'],'consent'=>['accepted']]; }
}
