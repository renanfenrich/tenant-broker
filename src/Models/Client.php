<?php

namespace RenanFenrich\TenantBroker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
