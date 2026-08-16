<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholesaleInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'contact_person',
        'phone',
        'whatsapp',
        'email',
        'district',
        'products_interested',
        'estimated_monthly_quantity',
        'message',
        'status',
    ];
}
