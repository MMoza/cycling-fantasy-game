<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\User;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_admin' => $u->is_admin,
                'leagues_count' => $u->leagues()->count(),
                'created_at' => $u->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function toggleAdmin(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_admin' => ! $user->is_admin]);

        return redirect()->route('admin.users.index');
    }
}
