<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreFile extends Model
{
    use HasFactory;

    protected $table = 'store_files' ;

    protected $fillable = [
        'store_id',
        'file_name',
        'file_path',
        'file_type',
    ];

    // Define the relationship with the Store model
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
