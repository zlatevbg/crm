<?php

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $api = new Api;

        return view('layouts.main', compact('api'));
    }
}
