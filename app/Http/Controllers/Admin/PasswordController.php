<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PasswordRequest;
use App\Models\AuditLog;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('admin.password');
    }

    public function update(PasswordRequest $request)
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        $user->save();
        AuditLog::record('admin.password_change', 'user', $user->id);

        return back()->with('status', __('Password changed.'));
    }
}
