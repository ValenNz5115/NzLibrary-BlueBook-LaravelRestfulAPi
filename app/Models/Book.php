<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table="books";
    protected $primaryKey="book_id";
    protected $foreignKey="author_id";
    protected $fillable=['book_id','author_id','book_name','stock','image'];
}
