<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Stage progress of the edition: {current_stage, total_stages}.
 */
readonly class StageProgressViewDTO implements Arrayable
{
    public function __construct(
        public int $currentStage,
        public int $totalStages,
    ) {}

    public function toArray(): array
    {
        return [
            'current_stage' => $this->currentStage,
            'total_stages' => $this->totalStages,
        ];
    }
}
