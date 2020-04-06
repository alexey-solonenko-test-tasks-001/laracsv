<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $m = [];
        /* Fix the date */
        $m['now'] = date('Y-m-d',strtotime('now'));

        return view('home',[
            'm' => $m,
        ]);
    }
}
