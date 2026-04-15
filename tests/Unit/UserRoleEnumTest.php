<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleEnumTest extends TestCase
{
    public function test_all_cases_have_labels(): void
    {
        foreach (UserRole::cases() as $case) {
            $this->assertNotEmpty($case->label(), "UserRole::{$case->name} has no label");
        }
    }

    public function test_admin_is_admin(): void
    {
        $this->assertTrue(UserRole::Admin->isAdmin());
    }

    public function test_pegawai_is_not_admin(): void
    {
        $this->assertFalse(UserRole::Pegawai->isAdmin());
    }

    public function test_atasan_can_approve_as_atasan(): void
    {
        $this->assertTrue(UserRole::Atasan->canApproveAsAtasan());
    }

    public function test_ketua_can_approve_as_atasan(): void
    {
        $this->assertTrue(UserRole::Ketua->canApproveAsAtasan());
    }

    public function test_pegawai_cannot_approve_as_atasan(): void
    {
        $this->assertFalse(UserRole::Pegawai->canApproveAsAtasan());
    }

    public function test_ketua_can_approve_as_pejabat(): void
    {
        $this->assertTrue(UserRole::Ketua->canApproveAsPejabat());
    }

    public function test_panitera_can_approve_as_pejabat(): void
    {
        $this->assertTrue(UserRole::Panitera->canApproveAsPejabat());
    }

    public function test_hakim_cannot_approve_as_pejabat(): void
    {
        $this->assertFalse(UserRole::Hakim->canApproveAsPejabat());
    }
}
