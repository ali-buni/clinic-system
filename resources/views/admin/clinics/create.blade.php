@extends('admin.layouts.app')
@section('title', 'Create Clinic')
@section('header', 'Create New Clinic')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.clinics.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">No owner</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ old('user_id') == $owner->id ? 'selected' : '' }}>
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
                        <input type="hidden" name="location_country" id="location_country_name" value="{{ old('location_country') }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Governorate *</label>
                        <select id="location_governorate" required disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Governorate</option>
                        </select>
                        <input type="hidden" name="location_governorate" id="location_governorate_name" value="{{ old('location_governorate') }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">City *</label>
                        <select id="location_city" required disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select City</option>
                        </select>
                        <input type="hidden" name="location_city" id="location_city_name" value="{{ old('location_city') }}">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Place Name</label>
                        <input type="text" name="location_name" value="{{ old('location_name') }}" placeholder="e.g. Near Central Hospital, Downtown area..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Create Clinic</button>
            <a href="{{ route('admin.clinics.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Cancel</a>
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

    async function loadCountries() {
        try {
            const res = await fetch(`${apiBase}/countries`);
            const countries = await res.json();
            countries.forEach(c => {
                countrySelect.add(new Option(c.name, c.iso2));
            });
        } catch (err) {
            console.error('Failed to load countries:', err);
        }
    }

    async function loadGovernorates(countryCode) {
        try {
            const res = await fetch(`${apiBase}/countries/${countryCode}/governorates`);
            const governorates = await res.json();
            governorateSelect.disabled = false;
            governorates.forEach(g => {
                governorateSelect.add(new Option(g.name, g.iso2));
            });
        } catch (err) {
            console.error('Failed to load governorates:', err);
        }
    }

    async function loadCities(countryCode, governorateCode) {
        try {
            const res = await fetch(`${apiBase}/countries/${countryCode}/governorates/${governorateCode}/cities`);
            const cities = await res.json();
            citySelect.disabled = false;
            cities.forEach(city => {
                citySelect.add(new Option(city.name, city.name));
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
