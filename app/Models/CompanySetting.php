<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    public const CREATED_AT = null;

    protected $table = 'company_settings';

    protected $fillable = [
        'company_name', 'nif', 'address', 'city', 'province',
        'phone', 'email', 'logo_path', 'currency', 'delivery_fee',
    ];

    protected function casts(): array
    {
        return ['delivery_fee' => 'decimal:2'];
    }
}
