<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\DTOs\Admin\CompetitionDTO;
use App\Application\UseCases\Admin\Competition\GetCompetitionFormDataUseCase;
use App\Application\UseCases\Admin\Competition\ListCompetitionsUseCase;
use App\Application\UseCases\Admin\Competition\StoreCompetitionUseCase;
use App\Application\UseCases\Admin\Competition\UpdateCompetitionUseCase;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    private const ALLOWED_TYPES = 'gc,major,monument,classic,championship';

    private const IMAGE_DIR_COVERS = 'competitions/covers';

    private const IMAGE_DIR_LOGOS = 'competitions/logos';

    public function __construct(
        private readonly ListCompetitionsUseCase $listCompetitionsUseCase,
        private readonly GetCompetitionFormDataUseCase $getCompetitionFormDataUseCase,
        private readonly StoreCompetitionUseCase $storeCompetitionUseCase,
        private readonly UpdateCompetitionUseCase $updateCompetitionUseCase,
    ) {}

    public function index(): Response
    {
        $data = $this->listCompetitionsUseCase->execute();

        return Inertia::render('Admin/Competitions/Index', [
            'competitions' => $this->resolveImages($data['competitions']),
            'countries' => $data['countries'],
        ]);
    }

    public function create(): Response
    {
        $data = $this->getCompetitionFormDataUseCase->execute();

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => $this->resolveCompetitionImages($data['competition']),
            'countries' => $data['countries'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.self::ALLOWED_TYPES,
            'country_id' => 'required|string|size:2|exists:countries,id',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'logo_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

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
        $data = $this->getCompetitionFormDataUseCase->execute($id);

        return Inertia::render('Admin/Competitions/Form', [
            'competition' => $this->resolveCompetitionImages($data['competition']),
            'countries' => $data['countries'],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.self::ALLOWED_TYPES,
            'country_id' => 'required|string|size:2|exists:countries,id',
            'active' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'logo_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'remove_cover_image' => 'boolean',
            'remove_logo_image' => 'boolean',
        ]);

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

    private function resolveS3Url(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            // fall through
        }

        try {
            return $disk->url($path);
        } catch (\Exception) {
            // fall through
        }

        $endpoint = rtrim(config('filesystems.disks.s3.endpoint', ''), '/');
        $bucket = config('filesystems.disks.s3.bucket', '');

        if ($endpoint && $bucket) {
            return "{$endpoint}/{$bucket}/".ltrim($path, '/');
        }

        return null;
    }

    private function resolveCompetitionImages(?array $competition): ?array
    {
        if ($competition === null) {
            return null;
        }

        $competition['cover_image_url'] = $this->resolveS3Url($competition['cover_image'] ?? null);
        $competition['logo_image_url'] = $this->resolveS3Url($competition['logo_image'] ?? null);

        return $competition;
    }

    private function resolveImages($competitions)
    {
        return $competitions->map(function (CompetitionDTO $c) {
            return new CompetitionDTO(
                id: $c->id,
                name: $c->name,
                type: $c->type,
                countryId: $c->countryId,
                active: $c->active,
                editionsCount: $c->editionsCount,
                coverImage: $c->coverImage,
                logoImage: $c->logoImage,
                coverImageUrl: $this->resolveS3Url($c->coverImage),
                logoImageUrl: $this->resolveS3Url($c->logoImage),
            );
        });
    }
}
