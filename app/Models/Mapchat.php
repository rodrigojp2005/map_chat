<?php

namespace App\Models;

// Alias model to allow gradual rename from Gincana to Mapchat without DB changes yet.
class Mapchat extends Gincana
{
    // Keep using the existing table/columns for now
    protected $table = 'gincanas';
}
