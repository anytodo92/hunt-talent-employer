<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirstIndustry extends Model
{
    // Relationship
    public function second_industries() {

        return $this->hasMany(SecondIndustry::class, 'first_industry_id', 'id');

    }
}
