<?php

namespace RenanFenrich\TenantBroker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'tenant_id', 'created_at', 'updated_at'];
}
