<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberCategory;
use Illuminate\Http\Request;

class MemberCategoryController extends Controller
{
    public function show()
    {
        $$memberCategories = MemberCategory::where('status', 'active')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();
        return response($memberCategories);
    }
}
