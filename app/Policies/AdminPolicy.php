<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Complaint;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Contracts\Auth\Authenticatable;

class ComplaintsPolicy
{public function viewAny(Admin $admin): bool
    {
        return true;
    }

    // يمكنه رؤية الشكوى
    public function view(Admin $admin, Complaint $complaint): bool
    {
        return true;
    }

    // يمكنه إنشاء شكاوى (غالبًا لا)
    public function create(Admin $admin): bool
    {
        return false; // أو true حسب الحاجة
    }

    // يمكنه التعديل على الشكوى
    public function update(Admin $admin, Complaint $complaint): bool
    {
        return true;
    }

    // يمكنه حذف الشكوى
    public function delete(Admin $admin, Complaint $complaint): bool
    {
        return true;
    }

    // لا يمكنه استعادة
    public function restore(Admin $admin, Complaint $complaint): bool
    {
        return false;
    }

    // لا يمكنه الحذف النهائي
    public function forceDelete(Admin $admin, Complaint $complaint): bool
    {
        return false;
    }}
