<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\JsonFilter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $allFiles = [
            'data/users/data1.json',
            'data/users/data2.json'
        ];

        $requestedFiles = $request->input('files', []);
        $filePaths = empty($requestedFiles)
            ? $allFiles
            : collect(explode(',', $requestedFiles))->map(fn($file) => "data/users/{$file}")->toArray();

        $filters = $request->only(['amount', 'min_amount', 'max_amount', 'currency', 'email', 'status', 'created_at']);
        $filter = new JsonFilter($filePaths);

        return response()->json($filter->applyFilters($filters)->get());
    }

}
