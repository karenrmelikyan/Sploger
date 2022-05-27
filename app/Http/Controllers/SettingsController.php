<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Settings\StoreRequest;
use App\Repositories\SettingsRepositoryInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;

use function abort;
use function array_filter;
use function preg_split;
use function redirect;
use function view;

class SettingsController extends Controller
{
    public function __construct(private SettingsRepositoryInterface $repository)
    {
        //
    }

    public function index(): Renderable
    {
        return view('settings.list', [
            'settings' => $this->repository->findAll(),
        ]);
    }

    public function edit(string $id): Renderable
    {
        $setting = $this->repository->findById((int) $id);
        if ($setting === null) {
            abort(404, 'Model not found.');
        }

        return view('settings.edit', [
            'setting' => $setting,
            'value' => $setting->value !== null ? implode("\n", json_decode($setting->value)) : '',
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $setting = $this->repository->findById((int) $validated['id']);
        if ($setting === null) {
            abort(404, 'Model not found.');
        }
        if ($validated['value'] !== null) {
            $validated['value'] = json_encode(array_filter(preg_split('/(\r\n|\r|\n)/', $validated['value'])));
        }
        $setting->fill($validated)->save();

        return redirect('settings');
    }
}
