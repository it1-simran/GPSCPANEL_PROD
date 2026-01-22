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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

     public function add(Request $req){

        return $req->input();
        $resto=new User;
        $resto->name=$req->input('name');
        $resto->email=$req->input('email');
        $resto->phone=$req->input('phone');
        $resto->save();
        $req->session()->flash('status','Data Submitted Successfully');
        return redirect('list');


    }
}
