<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function createStudent(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'class_id' => 'required|exists:classrooms,class_id', // Validate existence of class_id in the classes table
            'student_name' => 'required',
            'birth_day' => 'required',
            'gender' => [
                'required',
                Rule::in(['male', 'female', 'other']),
            ],
            'address' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Make image field optional
        ], [
            'class_id.required' => 'Class Data is required',
            'class_id.exists' => 'Invalid class_id. Class does not exist.',
            'student_name.required' => 'Student_name is required',
            'birth_day.required' => 'Birth_day is required',
            'gender.in' => 'Invalid gender value',
            'address.required' => 'Address is required',
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
                $imagePath = $file->storeAs('public/image/students', $nama);
            }

            $student = Student::create([
                'class_id' => $req->input('class_id'),
                'student_name' => $req->input('student_name'),
                'birth_day' => $req->input('birth_day'),
                'gender' => $req->input('gender'),
                'address' => $req->input('address'),
                'image' => $imagePath,
            ]);

            // Check if the student was successfully created
            if ($student) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new student',
                    'data' => $student,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new student',
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

    public function allStudent(Request $req)
    {
        try {
            // Query Builder for student
            $query = Student::query();

            // Apply filters
            if ($req->has('student_name')) {
                $query->where('student_name', 'like', '%' . $req->input('student_name') . '%');
            }

            // Apply sorting
            $sortBy = $req->input('sort_by', 'student_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate the results
            $perPage = $req->input('per_page', 10);
            $student = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'student retrieved successfully',
                'data' => $student,
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

    public function getstudent(Request $req, $student_id)
    {
        try {
            $student = Student::findOrFail($student_id);

            return response()->json([
                'status' => 'success',
                'message' => 'student retrieved successfully',
                'data' => $student,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'student not found',
                'error' => $e->getMessage(),
            ], 404); // 404 Not Found
        }
    }

    public function updateStudent(Request $req, $student_id)
    {
        $data = Student::find($student_id);

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Data student Tidak Ditemukan']);
        }

        $validator = Validator::make($req->all(), [
            'class_id' => 'required|exists:classrooms,class_id',
            'student_name' => 'required',
            'birth_day' => 'required',
            'gender' => [
                'required',
                Rule::in(['male', 'female', 'other']),
            ],
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add image validation rule
        ], [
            'class_id.required' => 'Class Data is required',
            'class_id.exists' => 'Invalid class_id. Class does not exist.',
            'student_name.required' => 'Student_name is required',
            'birth_day.required' => 'Birth_day is required',
            'gender.in' => 'Invalid gender value',
            'address.required' => 'Address is required',
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
            Storage::delete('public/image/students/' . $oldImage);

            $file = $req->file('image');
            $nama = time() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('public/image/students/', $nama);
            $imageName = basename($imagePath);
        } else {
            $imageName = $oldImage; // Keep the existing image if no new image is uploaded
        }

        $updateData = [
            'class_id' => $req->input('class_id'),
            'student_name' => $req->input('student_name'),
            'birth_day' => $req->input('birth_day'),
            'gender' => $req->input('gender'),
            'address' => $req->input('address'),
            'image' => $imageName,
        ];

        $result = $data->update($updateData);

        if ($result) {
            return response()->json(['status' => 'success', 'message' => 'Berhasil Update student']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Gagal Update student']);
        }
    }

    public function deletestudent($student_id)
    {
        try {
            // Find the student to be deleted
            $student = Student::findOrFail($student_id);

            // // Check if there are associated records (e.g., students)
            // if ($student->students()->exists()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Cannot delete student with associated students',
            //     ], 422); // 422 Unprocessable Entity
            // }

            Storage::delete('public/image/students/' . $student->image);

            // Perform the delete operation
            $deletestudent = $student->delete();

            // Check if the delete operation was successful
            if ($deletestudent) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'student deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete student',
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


    public function amountStudent()
    {
        try {
            $studentCount = Student::count();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully retrieved student count',
                'data' => $studentCount,
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
