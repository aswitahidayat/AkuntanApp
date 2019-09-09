<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zip extends Model
{
    protected $table = 'kka_dab.mst_zipcode';
    protected $primaryKey = 'zip_id'; // or null

    public $incrementing = true;
    public $timestamps = false;
}
