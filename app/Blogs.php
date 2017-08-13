<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blogs extends Model
{
     /**
     * The metrics table.
     * 
     * @var string
     */
    protected $table = 'blogs';
    protected $guarded = ['created_at' , 'updated_at' , 'id' ];
    
    public function courceDetail()
    {
        return $this->belongsTo('App\Course', 'blog_course_id','id');
    } 

}
