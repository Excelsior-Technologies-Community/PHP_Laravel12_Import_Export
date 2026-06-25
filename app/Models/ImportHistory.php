<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $fillable = [
        'file_name',
        'total_rows',
        'success_rows',
        'failed_rows'
    ];
}