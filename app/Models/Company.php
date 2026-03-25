<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'razon_social',
        'ruc',
        'direccion',
        'telefono',
        'email',
        'logo_path',
        'production',
        'sol_user',
        'sol_pass',
        'cert_path',
        'client_id',
        'client_secret',
        'user_id',
        'api_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
