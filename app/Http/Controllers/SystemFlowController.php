<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemFlowController extends Controller
{
    public function index()
    {
        return view('system-flow');
    }
}
