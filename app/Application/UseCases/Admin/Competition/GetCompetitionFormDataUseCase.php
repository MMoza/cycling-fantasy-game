<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Application\DTOs\View\AdminCompetitionFormPageDTO;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;

class GetCompetitionFormDataUseCase
{
    public function __construct(
        private readonly MediaUrlResolverInterface $mediaUrls,
    ) {}

    public function execute(?string $id = null): AdminCompetitionFormPageDTO
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        $competition = null;

        if ($id) {
            $model = CompetitionModel::findOrFail($id);
            $competition = [
                'id' => $model->id,
                'name' => $model->name,
                'type' => $model->type->value,
                'country_id' => $model->country_id,
                'active' => $model->active,
                'cover_image' => $model->cover_image,
                'logo_image' => $model->logo_image,
                'pcs_slug' => $model->pcs_slug,
                'cover_image_url' => $this->mediaUrls->resolve($model->cover_image),
                'logo_image_url' => $this->mediaUrls->resolve($model->logo_image),
            ];
        }

        return new AdminCompetitionFormPageDTO(
            competition: $competition,
            countries: $countries,
        );
    }
}
