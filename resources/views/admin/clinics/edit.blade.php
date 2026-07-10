@extends('admin.layouts.app')
@section('title', 'Edit Clinic')
@section('header', 'Edit: ' . $clinic->title)

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.clinics.update', $clinic) }}">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $clinic->title) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                <input type="text" name="phone" value="{{ old('phone', $clinic->phone) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">No owner</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ old('user_id', $clinic->user_id) == $owner->id ? 'selected' : '' }}>
                            {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="border-t pt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Location *</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Country *</label>
                        <select id="location_country" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Country</option>
                        </select>
                        <input type="hidden" name="location_country" id="location_country_name" value="{{ old('location_country', $loc?->country) }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Governorate *</label>
                        <select id="location_governorate" required disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Governorate</option>
                        </select>
                        <input type="hidden" name="location_governorate" id="location_governorate_name" value="{{ old('location_governorate', $loc?->governorate) }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">City *</label>
                        <select id="location_city" required disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select City</option>
                        </select>
                        <input type="hidden" name="location_city" id="location_city_name" value="{{ old('location_city', $loc?->city) }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Place Name</label>
                        <input type="text" name="location_name" value="{{ old('location_name', $loc?->name) }}" placeholder="e.g. Near Central Hospital, Downtown area..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update Clinic</button>
            <a href="{{ route('admin.clinics.show', $clinic) }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('location_country');
    const governorateSelect = document.getElementById('location_governorate');
    const citySelect = document.getElementById('location_city');
    const countryNameInput = document.getElementById('location_country_name');
    const governorateNameInput = document.getElementById('location_governorate_name');
    const cityNameInput = document.getElementById('location_city_name');

    const apiBase = '{{ url("/api/v1/clinic-system") }}';
    const existingCountry = '{{ $clinic->location?->country ?? "" }}';
    const existingGovernorate = '{{ $clinic->location?->governorate ?? "" }}';
    const existingCity = '{{ $clinic->location?->city ?? "" }}';

    async function loadCountries() {
        try {
            const res = await fetch(`${apiBase}/countries`);
            const countries = await res.json();
            let foundCountry = null;
            countries.forEach(c => {
                const opt = new Option(c.name, c.iso2);
                if (c.name === existingCountry) {
                    opt.selected = true;
                    foundCountry = c;
                }
                countrySelect.add(opt);
            });
            if (foundCountry) {
                await loadGovernorates(foundCountry.iso2);
            }
        } catch (err) {
            console.error('Failed to load countries:', err);
        }
    }

    async function loadGovernorates(countryCode) {
        try {
            const res = await fetch(`${apiBase}/countries/${countryCode}/governorates`);
            const governorates = await res.json();
            governorateSelect.disabled = false;
            governorateSelect.innerHTML = '<option value="">Select Governorate</option>';
            let foundGovernorate = null;
            governorates.forEach(g => {
                const opt = new Option(g.name, g.iso2);
                if (g.name === existingGovernorate) {
                    opt.selected = true;
                    foundGovernorate = g;
                }
                governorateSelect.add(opt);
            });
            if (foundGovernorate) {
                await loadCities(countryCode, foundGovernorate.iso2);
            }
        } catch (err) {
            console.error('Failed to load governorates:', err);
        }
    }

    async function loadCities(countryCode, governorateCode) {
        try {
            const res = await fetch(`${apiBase}/countries/${countryCode}/governorates/${governorateCode}/cities`);
            const cities = await res.json();
            citySelect.disabled = false;
            citySelect.innerHTML = '<option value="">Select City</option>';
            cities.forEach(city => {
                const opt = new Option(city.name, city.name);
                if (city.name === existingCity) opt.selected = true;
                citySelect.add(opt);
            });
        } catch (err) {
            console.error('Failed to load cities:', err);
        }
    }

    loadCountries();

    countrySelect.addEventListener('change', function() {
        countryNameInput.value = this.options[this.selectedIndex].text;
        governorateSelect.innerHTML = '<option value="">Select Governorate</option>';
        governorateNameInput.value = '';
        cityNameInput.value = '';
        citySelect.innerHTML = '<option value="">Select City</option>';
        citySelect.disabled = true;

        if (!this.value) {
            governorateSelect.disabled = true;
            governorateNameInput.value = '';
            return;
        }

        loadGovernorates(this.value);
    });

    governorateSelect.addEventListener('change', function() {
        governorateNameInput.value = this.options[this.selectedIndex].text;
        cityNameInput.value = '';
        citySelect.innerHTML = '<option value="">Select City</option>';

        if (!this.value) {
            citySelect.disabled = true;
            return;
        }

        loadCities(countrySelect.value, this.value);
    });

    citySelect.addEventListener('change', function() {
        cityNameInput.value = this.options[this.selectedIndex].text;
    });
});
</script>
@endsection
