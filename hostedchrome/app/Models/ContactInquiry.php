<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    use HasFactory;

    protected $table = 'contact_inquiries';
    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'organization_name',
        'subject',
        'message',
        'status',
    ];
}
