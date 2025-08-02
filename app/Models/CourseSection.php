<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','course_id','position'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function sectionContents()
    {
        return $this->hasMany(SectionContent::class);
    }
}
