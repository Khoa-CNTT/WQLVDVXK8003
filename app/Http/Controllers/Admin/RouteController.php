<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Display a listing of the routes.
     */
    public function index()
    {
        $routes = Route::orderBy('departure_location')->orderBy('arrival_location')->paginate(10);
        return view('admin.routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new route.
     */
    public function create()
    {
        return view('admin.routes.create');
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departure_location' => 'required|string|max:255',
            'arrival_location' => 'required|string|max:255',
            'distance' => 'required|integer|min:1',
            'base_price' => 'required|integer|min:0',
            'estimated_time' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Route::create([
            'departure_location' => $request->departure_location,
            'arrival_location' => $request->arrival_location,
            'distance' => $request->distance,
            'base_price' => $request->base_price,
            'estimated_time' => $request->estimated_time,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.routes.index')->with('success', 'Route created successfully');
    }

    /**
     * Display the specified route.
     */
    public function show($id)
    {
        $route = Route::findOrFail($id);
        return view('admin.routes.show', compact('route'));
    }

    /**
     * Show the form for editing the specified route.
     */
    public function edit($id)
    {
        $route = Route::findOrFail($id);
        return view('admin.routes.edit', compact('route'));
    }

    /**
     * Update the specified route.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'departure_location' => 'required|string|max:255',
            'arrival_location' => 'required|string|max:255',
            'distance' => 'required|integer|min:1',
            'base_price' => 'required|integer|min:0',
            'estimated_time' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $route = Route::findOrFail($id);
        $route->update([
            'departure_location' => $request->departure_location,
            'arrival_location' => $request->arrival_location,
            'distance' => $request->distance,
            'base_price' => $request->base_price,
            'estimated_time' => $request->estimated_time,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.routes.index')->with('success', 'Route updated successfully');
    }

    /**
     * Remove the specified route.
     */
    public function destroy($id)
    {
        $route = Route::findOrFail($id);

        // Check if there are any trips using this route
        if ($route->trips()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete route because it has associated trips');
        }

        $route->delete();
        return redirect()->route('admin.routes.index')->with('success', 'Route deleted successfully');
    }
}
