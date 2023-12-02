<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\BaseController;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function memo(){
        return view('dashboard.memo');
    }
}
