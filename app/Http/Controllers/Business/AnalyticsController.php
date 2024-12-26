<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->user()->business;
        
        // Return empty data if business is not found
        if (!$business) {
            return Inertia::render('Business/Analytics', [
                'stats' => [
                    'total_revenue' => 0,
                    'revenue_trend' => 0,
                    'total_bookings' => 0,
                    'bookings_trend' => 0,
                    'new_customers' => 0,
                    'customers_trend' => 0,
                    'average_rating' => 0,
                    'rating_trend' => 0,
                    'revenue_chart' => [
                        'labels' => [],
                        'data' => [],
                    ],
                    'bookings_chart' => [
                        'labels' => [],
                        'data' => [],
                    ],
                    'popular_services' => [],
                    'staff_performance' => [],
                ],
            ]);
        }

        $range = $request->input('range', 30);
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($range);
        $previousStartDate = Carbon::now()->subDays($range * 2);

        // Get current period bookings
        $currentBookings = $business->bookings()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Get previous period bookings for comparison
        $previousBookings = $business->bookings()
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->get();

        // Calculate total revenue and trends
        $currentRevenue = $currentBookings->sum('total_price');
        $previousRevenue = $previousBookings->sum('total_price');
        $revenueTrend = $previousRevenue > 0 
            ? round(($currentRevenue - $previousRevenue) / $previousRevenue * 100, 1)
            : 100;

        // Calculate bookings trend
        $bookingsTrend = $previousBookings->count() > 0
            ? round(($currentBookings->count() - $previousBookings->count()) / $previousBookings->count() * 100, 1)
            : 100;

        // Get new customers
        $newCustomers = Client::where('business_id', $business->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $previousNewCustomers = Client::where('business_id', $business->id)
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->count();
        $customersTrend = $previousNewCustomers > 0
            ? round(($newCustomers - $previousNewCustomers) / $previousNewCustomers * 100, 1)
            : 100;

        // Calculate average rating
        $currentRatings = $currentBookings->whereNotNull('rating')->avg('rating') ?? 0;
        $previousRatings = $previousBookings->whereNotNull('rating')->avg('rating') ?? 0;
        $ratingTrend = $previousRatings > 0
            ? round(($currentRatings - $previousRatings) / $previousRatings * 100, 1)
            : 0;

        // Prepare chart data
        $revenueChartData = $this->prepareRevenueChartData($business, $startDate, $endDate);
        $bookingsChartData = $this->prepareBookingsChartData($business, $startDate, $endDate);

        // Get popular services
        $popularServices = Service::where('business_id', $business->id)
            ->withCount(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_price')
            ->withAvg(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'rating')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'bookings_count' => $service->bookings_count,
                    'revenue' => $service->bookings_sum_total_price ?? 0,
                    'rating' => round($service->bookings_avg_rating ?? 0, 1),
                ];
            });

        // Get staff performance
        $staffPerformance = Staff::where('business_id', $business->id)
            ->withCount(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_price')
            ->withAvg(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'rating')
            ->orderByDesc('bookings_count')
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'avatar' => $staff->avatar,
                    'bookings_count' => $staff->bookings_count,
                    'revenue' => $staff->bookings_sum_total_price ?? 0,
                    'rating' => round($staff->bookings_avg_rating ?? 0, 1),
                ];
            });

        return Inertia::render('Business/Analytics', [
            'stats' => [
                'total_revenue' => $currentRevenue,
                'revenue_trend' => $revenueTrend,
                'total_bookings' => $currentBookings->count(),
                'bookings_trend' => $bookingsTrend,
                'new_customers' => $newCustomers,
                'customers_trend' => $customersTrend,
                'average_rating' => $currentRatings,
                'rating_trend' => $ratingTrend,
                'revenue_chart' => $revenueChartData,
                'bookings_chart' => $bookingsChartData,
                'popular_services' => $popularServices,
                'staff_performance' => $staffPerformance,
            ],
        ]);
    }

    private function prepareRevenueChartData($business, $startDate, $endDate)
    {
        $days = $endDate->diffInDays($startDate);
        $interval = $days <= 30 ? '1 day' : ($days <= 90 ? '1 week' : '1 month');
        
        $bookings = $business->bookings()
            ->selectRaw('DATE_TRUNC(?, created_at) as date, SUM(total_price) as revenue', [$interval])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $labels[] = $currentDate->format('M j');
            $booking = $bookings->firstWhere('date', $currentDate->format('Y-m-d'));
            $data[] = $booking ? $booking->revenue : 0;

            if ($interval === '1 day') {
                $currentDate->addDay();
            } elseif ($interval === '1 week') {
                $currentDate->addWeek();
            } else {
                $currentDate->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function prepareBookingsChartData($business, $startDate, $endDate)
    {
        $days = $endDate->diffInDays($startDate);
        $interval = $days <= 30 ? '1 day' : ($days <= 90 ? '1 week' : '1 month');
        
        $bookings = $business->bookings()
            ->selectRaw('DATE_TRUNC(?, created_at) as date, COUNT(*) as count', [$interval])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $labels[] = $currentDate->format('M j');
            $booking = $bookings->firstWhere('date', $currentDate->format('Y-m-d'));
            $data[] = $booking ? $booking->count : 0;

            if ($interval === '1 day') {
                $currentDate->addDay();
            } elseif ($interval === '1 week') {
                $currentDate->addWeek();
            } else {
                $currentDate->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
} 