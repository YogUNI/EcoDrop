<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pickup;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pickups = Pickup::with('user', 'handledBy')->latest()->get();
        return view('admin.dashboard', compact('pickups'));
    }
}