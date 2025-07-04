<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'username',
        'cookies',
        'proxy',
        'status',
        'user_id',
        'install_id',
        'openudid',
        'device_id',
        'meta',
        'process_id',
        'live_status',
        'video_url',
        'title',
        'hash_tag_id',
        'message',
        'worker_name',
        'region',
        'stream_type',
    ];

    protected $casts = [
        'status' => 'integer',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
