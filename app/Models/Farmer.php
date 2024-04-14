<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    //table
    protected $table = "farmers";

    //boot
    protected static function boot()
    {
        parent::boot();
        //creating
        static::creating(function ($farmer) {
            $parish = Parish::find($farmer->parish_id);
            if ($parish != null) {
                $farmer->district_id = $parish->district_id;
                $farmer->subcounty_id = $parish->subcounty_id;
            } else {
                throw new \Exception('Parish not found');
            }
        });

        //updating
        static::updating(function ($farmer) {
            $parish = Parish::find($farmer->parish_id);
            if ($parish != null) {
                $farmer->district_id = $parish->district_id;
                $farmer->subcounty_id = $parish->subcounty_id;
            } else {
                throw new \Exception('Parish not found');
            }
        });
    }
}
