<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        return view('home.index');
    }

    /**
     * Display a static page.
     */
    public function page($page)
    {
        if (view()->exists($page)) {
            return view($page);
        }

        return abort(404);
    }

    /**
     * Display the about page.
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Display the security policy page.
     */
    public function security()
    {
        return view('security');
    }

    /**
     * Display the utilities page.
     */
    public function utilities()
    {
        return view('utilities');
    }

}
