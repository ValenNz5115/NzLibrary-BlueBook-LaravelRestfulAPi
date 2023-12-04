<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function createBook(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'author_id' => 'required|exists:authors,author_id',
            'book_name' => 'required',
            'stock' => 'required|numeric', // Add numeric validation for the stock field
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ], [
            'author_id.required' => 'Class Data is required',
            'author_id.exists' => 'Invalid author_id. Class does not exist.',
            'book_name.required' => 'book_name is required',
            'stock.required' => 'stock is required',
            'stock.numeric' => 'stock must be a numeric value',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpg, png, jpeg, gif, svg.',
            'image.max' => 'The image may not be greater than 2048 kilobytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {
            $imagePath = null;

            if ($req->hasFile('image')) {
                $file = $req->file('image');
                $nama = time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('public/image/books', $nama);
            }

            $book = Book::create([
                'author_id' => $req->input('author_id'),
                'book_name' => $req->input('book_name'),
                'stock' => $req->input('stock'),
                'image' => $imagePath,
            ]);

            // Check if the book was successfully created
            if ($book) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new book',
                    'data' => $book,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new book',
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Class not found',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }


    public function allbook(Request $req)
    {
        try {
            // Query Builder for book
            $query = Book::query();

            // Apply filters
            if ($req->has('book_name')) {
                $query->where('book_name', 'like', '%' . $req->input('book_name') . '%');
            }

            // Apply sorting
            $sortBy = $req->input('sort_by', 'book_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate the results
            $perPage = $req->input('per_page', 10);
            $book = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'book retrieved successfully',
                'data' => $book,
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    public function getbook(Request $req, $book_id)
    {
        try {
            $book = Book::findOrFail($book_id);

            return response()->json([
                'status' => 'success',
                'message' => 'book retrieved successfully',
                'data' => $book,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'book not found',
                'error' => $e->getMessage(),
            ], 404); // 404 Not Found
        }
    }

    public function updatebook(Request $req, $book_id)
    {
        $data = Book::find($book_id);

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Data book Tidak Ditemukan']);
        }

        $validator = Validator::make($req->all(), [
            'author_id' => 'required|exists:authors,author_id',
            'book_name' => 'required',
            'stock' => 'required|numeric', // Add numeric validation for the stock field
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add image validation rule
        ], [
            'author_id.required' => 'Class Data is required',
            'author_id.exists' => 'Invalid author_id. Class does not exist.',
            'book_name.required' => 'book_name is required',
            'stock.required' => 'stock is required',
            'stock.numeric' => 'stock must be a numeric value',
            'image.image' => 'Invalid image format',
            'image.mimes' => 'Supported image formats are jpeg, png, jpg, gif, and svg',
            'image.max' => 'Max image size is 2048 KB',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        $oldImage = $data->image;

        if ($req->hasFile('image')) {
            // Delete the old image
            Storage::delete('public/image/books/' . $oldImage);

            $file = $req->file('image');
            $nama = time() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('public/image/books/', $nama);
            $imageName = basename($imagePath);
        } else {
            $imageName = $oldImage; // Keep the existing image if no new image is uploaded
        }

        $updateData = [
            'author_id' => $req->input('author_id'),
            'book_name' => $req->input('book_name'),
            'stock' => $req->input('stock'),
            'image' => $imageName,
        ];

        $result = $data->update($updateData);

        if ($result) {
            return response()->json(['status' => 'success', 'message' => 'Berhasil Update book']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Gagal Update book']);
        }
    }

    public function deletebook($book_id)
    {
        try {
            // Find the book to be deleted
            $book = Book::findOrFail($book_id);

            // // Check if there are associated records (e.g., books)
            // if ($book->books()->exists()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Cannot delete book with associated books',
            //     ], 422); // 422 Unprocessable Entity
            // }

            Storage::delete('public/image/books/' . $book->image);

            // Perform the delete operation
            $deletebook = $book->delete();

            // Check if the delete operation was successful
            if ($deletebook) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'book deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete book',
                ]);
            }
        } catch (\Exception $e) {
            // Handle unexpected exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }


    public function amountBook()
    {
        try {
            $bookcount = Book::count();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully retrieved Book count',
                'data' => $bookcount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
