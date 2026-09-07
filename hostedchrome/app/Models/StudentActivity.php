<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentActivity extends Model
{
    use HasFactory;

    protected $table = 'student_activities';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'activity_title',
        'score_info',
        'time_text',
        'icon',
        'type',
        'created_at',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }
}
