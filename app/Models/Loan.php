<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'loans';
    protected $primaryKey = 'loan_id';
    protected $foreignKey=['class_id','book_id'];
    protected $fillable = ['student_id','book_id','loan_date','return_date','status','status_payment','penalty'];
}
