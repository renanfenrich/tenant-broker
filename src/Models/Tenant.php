<?php

namespace RenanFenrich\TenantBroker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    public function database()
    {
        return $this->hasOne(\App\Models\Database::class);
    }
}
