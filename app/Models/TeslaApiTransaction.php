<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeslaApiTransaction extends Model
{
    protected $fillable = [
        'user_id', 'method', 'path', 'status', 'request_body', 'response_body'
    ];
}