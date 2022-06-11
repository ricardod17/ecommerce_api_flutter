<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasScope
{
    public function scopePerPage($query, $perPage)
    {
        if ($perPage == "all") {
            $perPage = $query->count();
        }

        return $perPage;
    }
}