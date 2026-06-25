<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportHistory extends Model
{
    protected $fillable = [
        'file_name',
        'format',
        'total_rows'
    ];
}