<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    
     /**
     * The database table used by the model.
     *
     * @var string
     */
    
    protected $table = 'courses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     /**
     * The primary key used by the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
 
    protected $guarded = ['created_at' , 'updated_at' , 'id' ];

   
}
