<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table="students";
    protected $primaryKey="student_id";
    protected $foreignKey="class_id";
    protected $fillable=['class_id','student_name','birth_day','gender','address','image'];


}
