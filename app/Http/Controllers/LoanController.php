<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LoanController extends Controller
{
    public function addLoan(Request $req)
    {
        // Validation
        $validator = Validator::make($req->all(), [
            'student_id' => 'required|exists:students,student_id',
            'book_id' => 'required|exists:books,book_id',
        ], [
            'student_id.required' => 'Student ID is required',
            'student_id.exists' => 'Invalid student ID. Student does not exist.',
            'book_id.required' => 'Book ID is required',
            'book_id.exists' => 'Invalid book ID. Book does not exist.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422); // 422 Unprocessable Entity
        }

        try {
            // Get current date
            $now = date('Y-m-d');

            // Check if the student already has an active loan for the same book
            $existingLoan = Loan::where('student_id', $req->get('student_id'))
                ->where('book_id', $req->get('book_id'))
                ->where('status', 'not_returned')
                ->first();

            if ($existingLoan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Student already has an active loan for this book.',
                ], 422);
            }

            // Create a new loan
            $loan = Loan::create([
                'student_id'   => $req->get('student_id'),
                'book_id'      => $req->get('book_id'),
                'loan_date'    => $now,
                'return_date'  => null,
                'status'       => 'not_returned',
                'status_payment'       => 'not_returned',
                'penalty'      => '0',
            ]);

            // Check if the loan was successfully created
            if ($loan) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new loan',
                    'data' => $loan,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new loan',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function allLoan()
    {
        try {
            // Paginate the results
            $loan = DB::table('loans')
                ->join('students', 'loans.student_id', '=', 'students.student_id')
                ->join('books', 'loans.book_id', '=', 'books.book_id')
                ->join('classrooms', 'students.class_id', '=', 'classrooms.class_id')
                ->orderBy('loans.loan_id')
                ->paginate(6);

            return response()->json([
                'status' => 'success',
                'message' => 'Loan data retrieved successfully',
                'data' => $loan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLoan(){
        $loan = loan::get();
        return response()->json($loan);
    }

    public function returnBook(Request $req, Loan $loan_id)
    {
        try {
            $returnDate = now();
            $loanDate = $loan_id->loan_date;
            $deadline = \Carbon\Carbon::parse($loanDate)->diffInDays($returnDate);
            $dayPenalty = 5000;
            $maxReturnDays = 3;

            $statusPayment = $deadline <= $maxReturnDays ? 'not_fined' : 'penalty';
            $status = 'returned';
            $penalty = $statusPayment === 'penalty' ? ($deadline - $maxReturnDays) * $dayPenalty : 0;

            $loan_id->update([
                'return_date' => $returnDate,
                'status' => $status,
                'status_payment' => $statusPayment,
                'penalty' => $penalty,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Successfully Return book']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while processing your request', 'error' => $e->getMessage()], 500);
        }
    }

    public function loanAmount(){
        $peminjaman = DB::table('loans')->count();
        return response()->json($loans);
    }

    public function amountFines(){
        $loans = DB::table('loans')->sum('denda');
        return response()->json($loans);
    }

    public function amountBookYetReturn(){
        $loans = DB::table('loans')->where('status','=','not_returned')->count();
        return response()->json($loans);
    }
}
