<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();
        
        $query = Client::query()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->withCount('bookings')
            ->with(['bookings' => function ($query) {
                $query->latest()
                    ->limit(5)
                    ->with('service');
            }]);

        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'avatar' => $client->avatar_url,
                'status' => $client->status,
                'notes' => $client->notes,
                'total_bookings' => $client->bookings_count,
                'last_visit' => $client->bookings->first()?->start_time,
                'created_at' => $client->created_at,
                'recent_bookings' => $client->bookings->map(fn ($booking) => [
                    'id' => $booking->id,
                    'start_time' => $booking->start_time,
                    'status' => $booking->status,
                    'service' => $booking->service ? [
                        'id' => $booking->service->id,
                        'name' => $booking->service->name,
                    ] : null,
                ]),
            ];
        });

        return Inertia::render('Business/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $business = $request->user()->businesses()->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('clients')->where(function ($query) use ($business) {
                    return $query->where('business_id', $business->id);
                }),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $client = $business->clients()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'notes' => $validated['notes'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Client added successfully.');
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, Client $client)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure client belongs to business
        if ($client->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string', 
                'email', 
                'max:255',
                Rule::unique('clients')->where(function ($query) use ($business) {
                    return $query->where('business_id', $business->id);
                })->ignore($client->id),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        return back()->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Request $request, Client $client)
    {
        $business = $request->user()->businesses()->firstOrFail();

        // Ensure client belongs to business
        if ($client->business_id !== $business->id) {
            abort(404);
        }

        // Actually delete the client
        $client->delete();

        return back()->with('success', 'Client removed successfully.');
    }
} 