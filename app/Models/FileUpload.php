<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileUpload extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'file_uploads';
    protected $fillable = [
        'name',
        'path',
        'type',
        'extension',
        'description',
        'module_id',
        'uploaded_by'
    ];
}
