<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassroomController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[UserController::class,'register']);
Route::post('/login', [UserController::class,'login']);
Route::post('/me', [UserController::class,'getAuthenticatedUser']);


Route::group(['middleware' => ['jwt.verify']], function ()
{

    Route::post('class/addclass',[ClassroomController::class, 'createClass']);
    Route::get('class',[ClassroomController::class, 'allClass']);
    Route::get('class/detail/{class_id}', [ClassroomController::class, 'getClass']);
    Route::put('class/updateclass/{class_id}', [ClassroomController::class, 'updateClass']);
    Route::delete('class/deleteclass/{class_id}', [ClassroomController::class, 'deleteClass']);

    Route::post('student/addstudent',[StudentController::class, 'createStudent']);
    Route::get('student',[StudentController::class, 'allStudent']);
    Route::get('student/detail/{student_id}', [StudentController::class, 'getStudent']);
    Route::post('student/updatestudent/{student_id}', [StudentController::class, 'updateStudent']);
    Route::delete('student/deletestudent/{student_id}', [studentController::class, 'deleteStudent']);
    Route::get('student/amountstudent/', [studentController::class, 'amountstudent']);

    Route::post('author/addauthor',[AuthorController::class, 'createAuthor']);
    Route::get('author',[AuthorController::class, 'allAuthor']);
    Route::get('author/detail/{author_id}', [AuthorController::class, 'getAuthor']);
    Route::post('author/updateauthor/{author_id}', [AuthorController::class, 'updateAuthor']);
    Route::delete('author/deleteauthor/{author_id}', [AuthorController::class, 'deleteAuthor']);

    Route::post('book/addbook',[BookController::class, 'createBook']);
    Route::get('book',[BookController::class, 'allBook']);
    Route::get('book/detail/{book_id}', [BookController::class, 'getBook']);
    Route::post('book/updatebook/{book_id}', [BookController::class, 'updateBook']);
    Route::delete('book/deletebook/{book_id}', [BookController::class, 'deleteBook']);
    Route::get('book/amountbook/', [BookController::class, 'amountBook']);


    //api peminjaman
    Route::post('/loan/addloan', [LoanController::class, 'addLoan']);
    Route::get('/loan', [LoanController::class,'allLoan']);
    Route::get('/loan/detail', [LoanController::class,'getLoan']);
    Route::post('/loan/returnbook/{loan_id}', [LoanController::class,'returnBook']);

    Route::get('/loan/loanamount', [LoanController::class,'amountBookYetReturn']);
    Route::get('/loan/amountfines', [LoanController::class,'amountFines']);
    Route::get('/loan/amountbookyetreturn', [LoanController::class,'amountBookYetReturn']);
});
