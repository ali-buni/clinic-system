<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $roleCounts = Role::pluck('id', 'name');

        $data = [
            'totalClinics' => Clinic::count(),
            'activeClinics' => Clinic::whereNull('deleted_at')->count(),
            'totalUsers' => User::count(),
            'usersByRole' => (object) [
                'owners' => $roleCounts['owner'] ? User::role('owner')->count() : 0,
                'doctors' => $roleCounts['doctor'] ? User::role('doctor')->count() : 0,
                'secretaries' => $roleCounts['secretary'] ? User::role('secretary')->count() : 0,
                'patients' => $roleCounts['patient'] ? User::role('patient')->count() : 0,
            ],
            'totalAppointments' => Appointment::count(),
            'totalRevenue' => Invoice::all()->sum('total_cost'),
            'pendingPayments' => Payment::whereNull('paid_at')->count(),
            'totalPayments' => Payment::count(),
        ];

        return view('admin.dashboard', $data);
    }

    public function loginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::byEmail($request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
        }

        if (! $user->hasRole('admin')) {
            return back()->withErrors(['email' => 'Unauthorized. Admin access only.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
