<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','course_section_id','content'];

    public function courseSection()
    {   
        return $this->belongsTo(CourseSection::class,'course_section_id');
    }
}
