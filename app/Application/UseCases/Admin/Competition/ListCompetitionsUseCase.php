<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Application\DTOs\Admin\CompetitionDTO;
use App\Application\DTOs\View\AdminCompetitionsListPageDTO;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;

class ListCompetitionsUseCase
{
    public function __construct(
        private readonly MediaUrlResolverInterface $mediaUrls,
    ) {}

    public function execute(): AdminCompetitionsListPageDTO
    {
        $competitions = CompetitionModel::with('country')
            ->withCount('editions')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => new CompetitionDTO(
                id: $c->id,
                name: $c->name,
                type: $c->type->label(),
                countryId: $c->country_id,
                active: $c->active,
                editionsCount: $c->editions_count,
                coverImage: $c->cover_image,
                logoImage: $c->logo_image,
                coverImageUrl: $this->mediaUrls->resolve($c->cover_image),
                logoImageUrl: $this->mediaUrls->resolve($c->logo_image),
            ));

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return new AdminCompetitionsListPageDTO(
            competitions: $competitions,
            countries: $countries,
        );
    }
}
