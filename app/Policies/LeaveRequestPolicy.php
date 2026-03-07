<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->isAdmin()
            || $user->id === $leaveRequest->user_id
            || $user->id === $leaveRequest->atasan_reviewer_id
            || $user->canApproveAsPejabat();
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->user_id
            && $leaveRequest->status === 'diajukan';
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return ($user->id === $leaveRequest->user_id || $user->isAdmin())
            && in_array($leaveRequest->status, ['diajukan', 'pertimbangan_atasan']);
    }

    public function reviewAtasan(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->atasan_reviewer_id
            || $user->isAdmin();
    }

    public function decidePejabat(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->canApproveAsPejabat() || $user->isAdmin();
    }
}
