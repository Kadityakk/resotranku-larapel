<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('admin.employees.index', [
            'employees' => User::with('role')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.employees.create', [
            'roles' => Role::orderBy('role_name')->get(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(User $employee): View
    {
        return view('admin.employees.show', [
            'employee' => $employee->load('role'),
        ]);
    }

    public function edit(User $employee): View
    {
        return view('admin.employees.edit', [
            'employee' => $employee,
            'roles' => Role::orderBy('role_name')->get(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $attributes = $request->validated();

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        $employee->update($attributes);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        if ($employee->is(request()->user())) {
            return redirect()
                ->route('admin.employees.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $employee->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}
