<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'name',
        'details',
        'price',
        'offer_type',
        'state',
        'category',
        'type',
        'subcounty_id',
        'administrator_id'
    ];


    //belongs to subcounty
    public function subcounty()
    {
        return $this->belongsTo(Subcounty::class, 'subcounty_id');
    }

    //belongs to administrator
    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
