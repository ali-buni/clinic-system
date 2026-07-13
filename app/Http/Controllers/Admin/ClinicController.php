<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClinicRequest;
use App\Http\Requests\Admin\UpdateClinicRequest;
use App\Models\Clinic;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClinicController extends Controller
{
    public function index(Request $request)
    {
        $query = Clinic::with('owner', 'location');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clinics = $query->latest()->paginate(15)->withQueryString();
        $location = $query->get()->pluck('location')->first();
        return view('admin.clinics.index', compact('clinics', 'location'));
    }

    public function create()
    {
        $owners = User::role('owner')->get();

        return view('admin.clinics.create', compact('owners'));
    }

    public function store(StoreClinicRequest $request)
    {
        Cache::forget('locations:all');
        $location = Location::create([
            'name' => $request->location_name,
            'country' => $request->location_country,
            'governorate' => $request->location_governorate,
            'city' => $request->location_city,
        ]);

        $clinic = Clinic::create([
            'title' => $request->title,
            'phone' => $request->phone,
            'user_id' => $request->user_id,
            'location_id' => $location->id,
            'location' => $location->makeLocation(),
        ]);

        return redirect()->route('admin.clinics.show', $clinic)->with('success', 'Clinic created successfully.');
    }

    public function show(Clinic $clinic)
    {
        $clinic->load(['owner', 'location', 'doctors.user', 'secretaries.user', 'appointments', 'rooms']);

        return view('admin.clinics.show', compact('clinic'));
    }

    public function edit(Clinic $clinic)
    {
        $owners = User::role('owner')->get();
        $clinic->load('location');
        $location = $clinic->location()->first();

        return view('admin.clinics.edit', compact('clinic', 'owners', 'location'));
    }

    public function update(UpdateClinicRequest $request, Clinic $clinic)
    {
        $location = $clinic->location()->first();

        if (
            $location &&
            ($location->name !== $request->location_name ||
                $location->country !== $request->location_country ||
                $location->governorate !== $request->location_governorate ||
                $location->city !== $request->location_city
            )
        ) {
            Cache::forget('locations:all');
            $location->update([
                'name' => $request->location_name,
                'country' => $request->location_country,
                'governorate' => $request->location_governorate,
                'city' => $request->location_city,
            ]);
        } else if (
            ! empty($request->location_name) ||
            ! empty($request->location_country) ||
            ! empty($request->location_governorate) ||
            ! empty($request->location_city)
        ) {
            $location = Location::create([
                'name' => $request->location_name ?: null,
                'country' => $request->location_country ?: null,
                'governorate' => $request->location_governorate ?: null,
                'city' => $request->location_city ?: null,
            ]);
        }

        $clinic->update([
            'title' => $request->title,
            'phone' => $request->phone,
            'user_id' => $request->user_id,
            'location_id' => $location->id,
            'location' => $location->makeLocation(),
        ]);

        return redirect()->route('admin.clinics.show', $clinic)->with('success', 'Clinic updated successfully.');
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return redirect()->route('admin.clinics.index')->with('success', 'Clinic deleted successfully.');
    }

    public function restore(Clinic $clinic)
    {
        $clinic->restore();

        return redirect()->route('admin.clinics.show', $clinic)->with('success', 'Clinic restored successfully.');
    }
}
