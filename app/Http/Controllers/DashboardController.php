<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Menambahkan middleware auth untuk memastikan hanya user yang sudah login yang bisa mengakses dashboard
        $this->middleware('auth');
    }

    public function index()
    {
        return view('index');  // Ganti dengan nama tampilan dashboard Anda
    }
}
