<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\Admin\AdminStageDTO;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Stages/Index page.
 */
readonly class AdminStagesListPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, year: int, competition_id: string, competition: string}  $edition
     * @param  Collection<int, AdminStageDTO>  $stages
     */
    public function __construct(
        public mixed $edition,
        public mixed $stages,
    ) {}

    public function toArray(): array
    {
        return [
            'edition' => $this->edition,
            'stages' => $this->stages,
        ];
    }
}
