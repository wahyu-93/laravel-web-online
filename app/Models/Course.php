<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','thumbnail','about','category_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){
            $model->slug = Str::slug($model->name);
        });
    }

    public function courseMentors()
    {
        return $this->hasMany(CourseMentor::class, 'course_id');
    }

    public function courseStudents()
    {   
        return $this->hasMany(CourseStudent::class, 'course_id');
    }
    
    public function courseBenefits()
    {   
        return $this->hasMany(CourseBenefit::class, 'course_id');
    }

    public function courseSections()
    {   
        return $this->hasMany(CourseSection::class, 'course_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


}
