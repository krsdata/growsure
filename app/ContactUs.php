<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
     /**
     * The metrics table.
     * 
     * @var string
     */
    protected $table = 'contact_us';
    protected $guarded = ['created_at' , 'updated_at' , 'id' ];
    protected $fillable = ['name','email','subject','message'];
}
