<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    public function index(Request $request)
    {
        $userlocationResponse = $this->api()->get('v1/user-locations');
        $userLocations = $userlocationResponse['data'];

        return view('app.user_locations.index', compact('userLocations'));
    }
}
