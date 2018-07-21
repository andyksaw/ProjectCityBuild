<?php

namespace App\Modules\ServerKeys\Models;

use App\Support\Model;

class ServerKeyToken extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'server_key_tokens';

    protected $primaryKey = 'server_key_token_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'server_key_id',
        'token_hash',
        'is_blacklisted',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [
        'created_at',
        'updated_at',
    ];


    public function serverKey()
    {
        return $this->hasOne('App\Modules\ServerKeys\Models\ServerKey', 'server_key_id', 'server_key_id');
    }
}
