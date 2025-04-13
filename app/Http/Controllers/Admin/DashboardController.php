<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Ticket;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        // Get counts for dashboard
        $usersCount = User::count();
        $driversCount = Driver::count();
        $vehiclesCount = Vehicle::count();
        $routesCount = Route::count();
        $tripsCount = Trip::count();

        // Get today's tickets
        $todayTickets = Ticket::whereDate('created_at', Carbon::today())->count();

        // Get revenue stats
        $totalRevenue = Ticket::where('status', 'confirmed')
            ->sum('price');

        $monthlyRevenue = Ticket::where('status', 'confirmed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');

        // Get upcoming trips
        $upcomingTrips = Trip::with(['route', 'driver', 'vehicle'])
            ->where('departure_date', '>=', Carbon::today())
            ->where('status', 'scheduled')
            ->orderBy('departure_date')
            ->orderBy('departure_time')
            ->take(5)
            ->get();

        // Get recent tickets
        $recentTickets = Ticket::with(['trip.route', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'usersCount',
            'driversCount',
            'vehiclesCount',
            'routesCount',
            'tripsCount',
            'todayTickets',
            'totalRevenue',
            'monthlyRevenue',
            'upcomingTrips',
            'recentTickets'
        ));
    }

    /**
     * Display sales report.
     */
    public function salesReport(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        // Daily sales for the selected period
        $dailySales = Ticket::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as tickets_count'),
                DB::raw('SUM(price) as total_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Sales by route
        $salesByRoute = Ticket::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('trips', 'tickets.trip_id', '=', 'trips.id')
            ->join('routes', 'trips.route_id', '=', 'routes.id')
            ->select(
                'routes.departure_location',
                'routes.arrival_location',
                DB::raw('COUNT(*) as tickets_count'),
                DB::raw('SUM(tickets.price) as total_revenue')
            )
            ->groupBy('routes.departure_location', 'routes.arrival_location')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Total summary
        $totalTickets = Ticket::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalRevenue = Ticket::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('price');

        return view('admin.reports.sales', compact(
            'dailySales',
            'salesByRoute',
            'totalTickets',
            'totalRevenue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display routes performance report.
     */
    public function routesReport(Request $request)
    {
        $routes = Route::all();
        $selectedRouteId = $request->route_id;

        if ($selectedRouteId) {
            $route = Route::findOrFail($selectedRouteId);

            // Get trips for this route
            $trips = Trip::where('route_id', $selectedRouteId)
                ->orderBy('departure_date', 'desc')
                ->orderBy('departure_time', 'desc')
                ->paginate(10);

            // Calculate performance metrics
            $totalTrips = Trip::where('route_id', $selectedRouteId)->count();

            $totalTickets = Ticket::whereHas('trip', function ($query) use ($selectedRouteId) {
                $query->where('route_id', $selectedRouteId);
            })->count();

            $totalRevenue = Ticket::whereHas('trip', function ($query) use ($selectedRouteId) {
                $query->where('route_id', $selectedRouteId);
            })->where('status', 'confirmed')->sum('price');

            $averageOccupancy = $totalTrips > 0 ? ($totalTickets / $totalTrips) : 0;

            return view('admin.reports.route_detail', compact(
                'route',
                'trips',
                'totalTrips',
                'totalTickets',
                'totalRevenue',
                'averageOccupancy'
            ));
        }

        // Route list with summary
        $routeStats = [];

        foreach ($routes as $route) {
            $totalTrips = Trip::where('route_id', $route->id)->count();

            $totalTickets = Ticket::whereHas('trip', function ($query) use ($route) {
                $query->where('route_id', $route->id);
            })->count();

            $totalRevenue = Ticket::whereHas('trip', function ($query) use ($route) {
                $query->where('route_id', $route->id);
            })->where('status', 'confirmed')->sum('price');

            $routeStats[] = [
                'route' => $route,
                'total_trips' => $totalTrips,
                'total_tickets' => $totalTickets,
                'total_revenue' => $totalRevenue,
                'average_occupancy' => $totalTrips > 0 ? ($totalTickets / $totalTrips) : 0,
            ];
        }

        return view('admin.reports.routes', compact('routeStats'));
    }
}
