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

    // mengecek apakah user sudah pernah beli kelas sebelumnya0
    public function isSubscribedByUser($userId)
    {
        return $this->transactions()
            ->where('user_id', $userId)
            ->where('is_padi',true)
            ->where('ended_at', '>=', now())
            ->exists();
    }
}
