<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\Competition\DeleteCompetitionUseCase;
use App\Application\UseCases\Admin\Competition\GetCompetitionFormDataUseCase;
use App\Application\UseCases\Admin\Competition\ListCompetitionsUseCase;
use App\Application\UseCases\Admin\Competition\StoreCompetitionUseCase;
use App\Application\UseCases\Admin\Competition\UpdateCompetitionUseCase;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Admin\StoreCompetitionRequest;
use App\Presentation\Http\Requests\Admin\UpdateCompetitionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    private const IMAGE_DIR_COVERS = 'competitions/covers';

    private const IMAGE_DIR_LOGOS = 'competitions/logos';

    public function __construct(
        private readonly ListCompetitionsUseCase $listCompetitionsUseCase,
        private readonly GetCompetitionFormDataUseCase $getCompetitionFormDataUseCase,
        private readonly StoreCompetitionUseCase $storeCompetitionUseCase,
        private readonly UpdateCompetitionUseCase $updateCompetitionUseCase,
        private readonly DeleteCompetitionUseCase $deleteCompetitionUseCase,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Competitions/Index', $this->listCompetitionsUseCase->execute()->toArray());
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Competitions/Form', $this->getCompetitionFormDataUseCase->execute()->toArray());
    }

    public function store(StoreCompetitionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $data = $validated;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store(self::IMAGE_DIR_COVERS, 's3');
        }

        if ($request->hasFile('logo_image')) {
            $data['logo_image'] = $request->file('logo_image')->store(self::IMAGE_DIR_LOGOS, 's3');
        }

        $this->storeCompetitionUseCase->execute($data);

        return redirect()->route('admin.competitions.index');
    }

    public function edit(string $id): Response
    {
        return Inertia::render('Admin/Competitions/Form', $this->getCompetitionFormDataUseCase->execute($id)->toArray());
    }

    public function update(UpdateCompetitionRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $data = $validated;

        $existingModel = CompetitionModel::findOrFail($id);

        if ($request->hasFile('cover_image')) {
            if ($existingModel->cover_image) {
                Storage::disk('s3')->delete($existingModel->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store(self::IMAGE_DIR_COVERS, 's3');
        } elseif (! empty($validated['remove_cover_image'])) {
            if ($existingModel->cover_image) {
                Storage::disk('s3')->delete($existingModel->cover_image);
            }
            $data['cover_image'] = null;
        } else {
            unset($data['cover_image']);
        }

        if ($request->hasFile('logo_image')) {
            if ($existingModel->logo_image) {
                Storage::disk('s3')->delete($existingModel->logo_image);
            }
            $data['logo_image'] = $request->file('logo_image')->store(self::IMAGE_DIR_LOGOS, 's3');
        } elseif (! empty($validated['remove_logo_image'])) {
            if ($existingModel->logo_image) {
                Storage::disk('s3')->delete($existingModel->logo_image);
            }
            $data['logo_image'] = null;
        } else {
            unset($data['logo_image']);
        }

        $this->updateCompetitionUseCase->execute($id, $data);

        return redirect()->route('admin.competitions.index');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->deleteCompetitionUseCase->execute($id);

        return redirect()->route('admin.competitions.index');
    }
}
