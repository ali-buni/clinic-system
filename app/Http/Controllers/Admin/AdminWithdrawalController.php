<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveWithdrawalRequest;
use App\Http\Requests\Admin\RejectWithdrawalRequest;
use App\Models\DoctorWithdrawal;
use App\Services\DoctorWithdrawalService;
use Illuminate\Http\Request;

class AdminWithdrawalController extends Controller
{
    public function __construct(
        private readonly DoctorWithdrawalService $withdrawalService
    ) {}

    public function index(Request $request)
    {
        $query = DoctorWithdrawal::with(['doctor.user', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate(15)->withQueryString();

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function show(DoctorWithdrawal $withdrawal)
    {
        $withdrawal->load(['doctor.user', 'approvedBy']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(ApproveWithdrawalRequest $request, DoctorWithdrawal $withdrawal)
    {
        try {
            $this->withdrawalService->approveWithdrawal($withdrawal, auth()->id());

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal approved and transfer initiated successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(RejectWithdrawalRequest $request, DoctorWithdrawal $withdrawal)
    {
        try {
            $this->withdrawalService->rejectWithdrawal(
                $withdrawal,
                auth()->id(),
                $request->validated('rejection_reason')
            );

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal rejected successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
