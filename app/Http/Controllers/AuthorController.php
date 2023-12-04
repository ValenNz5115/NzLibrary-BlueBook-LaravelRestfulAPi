<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    public function createAuthor(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'author_name' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Make image field optional
        ], [
            'author_name.required' => 'author_name is required',
            'description.required' => 'description is required',
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
                $name = time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('public/image/authors/', $name);
            }

            $author = Author::create([
                'author_name' => $req->input('author_name'),
                'image' => $imagePath,
            ]);

            // Check if the author was successfully created
            if ($author) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new author',
                    'data' => $author,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new author',
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

    public function allAuthor(Request $req)
    {
        try {
            // Query Builder for author
            $query = Author::query();

            // Apply filters
            if ($req->has('author_name')) {
                $query->where('author_name', 'like', '%' . $req->input('author_name') . '%');
            }

            // Apply sorting
            $sortBy = $req->input('sort_by', 'author_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate the results
            $perPage = $req->input('per_page', 10);
            $author = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'author retrieved successfully',
                'data' => $author,
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

    public function getAuthor(Request $req, $author_id)
    {
        try {
            $author = Author::findOrFail($author_id);

            return response()->json([
                'status' => 'success',
                'message' => 'author retrieved successfully',
                'data' => $author,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'author not found',
                'error' => $e->getMessage(),
            ], 404); // 404 Not Found
        }
    }

    public function updateauthor(Request $req, $author_id)
    {
        $data = Author::find($author_id);

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Data author Tidak Ditemukan']);
        }

        $validator = Validator::make($req->all(), [
            'author_name' => 'required',
            'description' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add image validation rule
        ], [
            'author_name.required' => 'author_name is required',
            'description.required' => 'description is required',
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
            Storage::delete('public/image/authors/' . $oldImage);

            $file = $req->file('image');
            $name = time() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('public/image/authors/', $name);
            $imageName = basename($imagePath);
        } else {
            $imageName = $oldImage; // Keep the existing image if no new image is uploaded
        }

        $updateData = [
            'author_name' => $req->input('author_name'),
            'description' => $req->input('description'),
            'image' => $imageName,
        ];

        $result = $data->update($updateData);

        if ($result) {
            return response()->json(['status' => 'success', 'message' => 'Berhasil Update author']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Gagal Update author']);
        }
    }

    public function deleteauthor($author_id)
    {
        try {
            // Find the author to be deleted
            $author = Author::findOrFail($author_id);

            // // Check if there are associated records (e.g., authors)
            // if ($author->authors()->exists()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Cannot delete author with associated authors',
            //     ], 422); // 422 Unprocessable Entity
            // }

            Storage::delete('public/image/authors/' . $author->image);

            // Perform the delete operation
            $deleteauthor = $author->delete();

            // Check if the delete operation was successful
            if ($deleteauthor) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'author deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete author',
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
}
