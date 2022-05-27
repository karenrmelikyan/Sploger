<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreRequest;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function abort;
use function back;
use function redirect;
use function view;

final class UserController extends Controller
{

    public function __construct(private UserRepositoryInterface $repository, private Hasher $hasher)
    {
        //
    }

    public function index(Request $request): Renderable
    {
        $maxPerPage = 100;
        $perPage = $request->input('per-page', 25);
        $perPage = $perPage > $maxPerPage ? $maxPerPage : $perPage;

        $users = $this->repository->findAllPaginated((int) $perPage);

        $filters = [];

        return view('user.list', [
            'items' => $users,
            'filters' => $filters,
        ]);
    }

    public function create(): Renderable
    {
        return view('user.create');
    }

    public function edit(string $id): Renderable
    {
        $user = $this->repository->findById((int) $id);
        if ($user === null) {
            abort(404, 'Model not found.');
        }

        return view('user.edit', ['user' => $user]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (isset($validated['id'])) {
            $user = $this->repository->findById((int) $validated['id']);
            if ($user === null) {
                abort(404, 'Model not found.');
            }
        } else {
            $user = new User();
        }

        $validated['password'] = $this->hasher->make($validated['password']);
        $user->fill($validated)->save();

        return redirect('users');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete((int) $id);
        return back();
    }
}
