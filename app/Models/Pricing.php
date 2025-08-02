<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pricing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','duration','price'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
