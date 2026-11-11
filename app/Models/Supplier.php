<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'nif', 'contact_name', 'email', 'phone', 'website',
        'address', 'city', 'country', 'notes', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(SupplierInquiry::class);
    }
}
