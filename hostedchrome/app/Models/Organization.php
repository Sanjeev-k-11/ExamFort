<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'code',
        'type',
        'logo_url',
        'email',
        'phone',
        'website',
        'address',
        'max_teachers_allowed',
        'max_students_allowed',
        'max_exams_allowed',
        'status',
    ];

    protected $casts = [
        'max_teachers_allowed' => 'integer',
        'max_students_allowed' => 'integer',
        'max_exams_allowed' => 'integer',
    ];

    public function principals()
    {
        return $this->hasMany(User::class, 'org_id', 'id')->where('role', 'PRINCIPAL');
    }

    public function teachers()
    {
        return $this->hasMany(User::class, 'org_id', 'id')->whereIn('role', ['PROCTOR', 'TEACHER']);
    }

    public function students()
    {
        return $this->hasMany(User::class, 'org_id', 'id')->where('role', 'CANDIDATE');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'org_id', 'id');
    }
}
