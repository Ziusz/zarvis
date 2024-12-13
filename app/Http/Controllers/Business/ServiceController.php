<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $business = $request->user()->businesses()->firstOrFail();

        $service = $business->services()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'duration' => $request->duration,
            'price' => $request->price,
            'capacity' => $request->capacity,
            'status' => $request->status,
        ]);

        // Reload business with services
        $business->load('services');

        return back()->with([
            'success' => 'Service created successfully.',
            'business' => $business
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $service->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'duration' => $request->duration,
            'price' => $request->price,
            'capacity' => $request->capacity,
            'status' => $request->status,
        ]);

        // Reload business with services
        $business = $service->business->load('services');

        return back()->with([
            'success' => 'Service updated successfully.',
            'business' => $business
        ]);
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        $business = $service->business;
        $service->delete();

        // Reload business with services
        $business->load('services');

        return back()->with([
            'success' => 'Service deleted successfully.',
            'business' => $business
        ]);
    }
} 