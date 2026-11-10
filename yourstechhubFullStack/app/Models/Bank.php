<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'account_number', 'account_name', 'balance', 'currency', 'is_active'])]
class Bank extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function cashEntries(): HasMany
    {
        return $this->hasMany(CashEntry::class);
    }
}
