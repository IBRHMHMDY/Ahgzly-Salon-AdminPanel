<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use \App\Models\Traits\BelongsToSalon;

    protected $guarded = [];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }
}
