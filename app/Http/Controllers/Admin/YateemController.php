<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YateemsHelp;
use Illuminate\Http\Request;

class YateemController extends Controller
{
    public function index()
    {
        $yateemsHelps = YateemsHelp::all();
        return view('admin.yateems', compact('yateemsHelps'));
    }

    public function create()
    {
        return view('admin.yateems-create');
    }
}
