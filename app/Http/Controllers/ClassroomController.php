<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ClassroomController extends Controller
{
    public function createClass(Request $req)
    {
        // Validation
        $validator = Validator::make($req->all(), [
            'class_name' => 'required',
        ], [
            'class_name.required' => 'Class name is required',
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
            // Attempt to create a new Classroom
            $classroom = Classroom::create([
                'class_name' => $req->input('class_name'),
            ]);

            // Check if the Classroom was successfully created
            if ($classroom) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new class',
                    'data' => $classroom,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new class',
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

    public function allClass(Request $req)
    {
        try {
            // Query Builder for Classroom
            $query = Classroom::query();

            // Apply filters
            if ($req->has('class_name')) {
                $query->where('class_name', 'like', '%' . $req->input('class_name') . '%');
            }

            // Apply sorting
            $sortBy = $req->input('sort_by', 'class_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate the results
            $perPage = $req->input('per_page', 10);
            $classrooms = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Classrooms retrieved successfully',
                'data' => $classrooms,
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

    public function getClass(Request $req, $class_id)
    {
        try {
            $classroom = Classroom::findOrFail($class_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Classroom retrieved successfully',
                'data' => $classroom,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Classroom not found',
                'error' => $e->getMessage(),
            ], 404); // 404 Not Found
        }
    }

    public function updateClass(Request $req, $class_id)
    {
        try {
            // Validate the req data
            $validator = Validator::make($req->all(),[
                'class_name' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            // Update the class
            $updatedClass = Classroom::where('class_id', $class_id)
                ->update([
                    'class_name' => $req->input('class_name'),
                ]);

            // Check if the update was successful
            if ($updatedClass) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Class updated successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update class',
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

    public function deleteClass($class_id)
    {
        try {
            // Find the class to be deleted
            $classroom = Classroom::findOrFail($class_id);

            // // Check if there are associated records (e.g., students)
            // if ($classroom->students()->exists()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Cannot delete class with associated students',
            //     ], 422); // 422 Unprocessable Entity
            // }

            // Perform the delete operation
            $deleteClass = $classroom->delete();

            // Check if the delete operation was successful
            if ($deleteClass) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Class deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete class',
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
