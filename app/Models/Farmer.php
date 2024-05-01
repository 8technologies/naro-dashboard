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
            $farmer = self::prepareData($farmer);
            return $farmer;
        });

        //updating
        static::updating(function ($farmer) {
            $farmer = self::prepareData($farmer);
            return $farmer;
        });
    }

    //prepare data
    public static function prepareData($data)
    {
        $gender = strtolower($data->gender);
        if ($gender == 'm' || $gender == 'male') {
            $data->gender = 'Male';
        } else if ($gender == 'f' || $gender == 'female') {
            $data->gender = 'Female';
        }

        $parish = Parish::find($data->parish_id);
        if ($parish != null) {
            $data->district_id = $parish->district_id;
            $data->subcounty_id = $parish->subcounty_id;
        } else {
            //throw new \Exception('Parish not found');
            $sub = Subcounty::find($data->subcounty_id);
            if ($sub != null) {
                $data->district_id = $sub->district_id;
            } else {
                //throw new \Exception('Subcounty not found');
                $district = District::find($data->district_id);
                if ($district == null) {
                    //throw new \Exception('District not found');
                } else {
                    $data->district_id = $district->id;
                }
            }
        }
        return $data;
    }
}
