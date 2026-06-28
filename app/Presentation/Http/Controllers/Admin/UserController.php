<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\User\ListUsersUseCase;
use App\Application\UseCases\Admin\User\ToggleAdminUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly ListUsersUseCase $listUsersUseCase,
        private readonly ToggleAdminUseCase $toggleAdminUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->listUsersUseCase->execute();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function toggleAdmin(string $id): RedirectResponse
    {
        $this->toggleAdminUseCase->execute($id);

        return redirect()->route('admin.users.index');
    }
}
