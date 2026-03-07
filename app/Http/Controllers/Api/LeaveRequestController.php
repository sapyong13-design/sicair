<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $leaves = LeaveRequest::where('user_id', $request->user()->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($leaves);
    }

    public function show(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        return response()->json($leaveRequest->load('user'));
    }

    public function balance(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'leave_balance' => $user->leave_balance,
            'year'          => now()->year,
            'cuti_record'   => $user->cutiRecords()->where('tahun', now()->year)->first(),
        ]);
    }
}
