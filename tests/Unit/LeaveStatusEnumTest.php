<?php

namespace Tests\Unit;

use App\Enums\LeaveStatus;
use PHPUnit\Framework\TestCase;

class LeaveStatusEnumTest extends TestCase
{
    public function test_all_cases_have_labels(): void
    {
        foreach (LeaveStatus::cases() as $case) {
            $this->assertNotEmpty($case->label(), "LeaveStatus::{$case->name} has no label");
        }
    }

    public function test_all_cases_have_badge_class(): void
    {
        foreach (LeaveStatus::cases() as $case) {
            $this->assertNotEmpty($case->badgeClass(), "LeaveStatus::{$case->name} has no badge class");
        }
    }

    public function test_approved_label(): void
    {
        $this->assertEquals('Disetujui', LeaveStatus::Approved->label());
    }

    public function test_rejected_label(): void
    {
        $this->assertEquals('Ditolak', LeaveStatus::Rejected->label());
    }

    public function test_from_label_returns_correct_case(): void
    {
        $case = LeaveStatus::fromLabel('Disetujui');
        $this->assertSame(LeaveStatus::Approved, $case);
    }

    public function test_from_label_returns_null_for_unknown(): void
    {
        $this->assertNull(LeaveStatus::fromLabel('Tidak Ada'));
    }
}
