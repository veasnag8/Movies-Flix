<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CategoryAdminController extends Controller
{
    public function __construct(protected GoogleSheetService $sheets)
    {
    }

    public function index()
    {
        return response()->json(['data' => $this->sheets->getCategories()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = $this->sheets->createCategory($validator->validated());

        return response()->json(['data' => $category, 'message' => 'Category created.'], 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:120',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = $this->sheets->updateCategory($id, $validator->validated());

        if (! $category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json(['data' => $category, 'message' => 'Category updated.']);
    }

    public function destroy(string $id)
    {
        if (! $this->sheets->deleteCategory($id)) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json(['message' => 'Category deleted.']);
    }
}
