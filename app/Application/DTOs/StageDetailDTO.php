<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use Illuminate\Contracts\Support\Arrayable;

readonly class StageDetailDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public string $typeValue,
        public ?string $distance,
        public ?int $elevationGain,
        public ?string $profileImage,
        public ?string $origin,
        public ?string $destination,
        public ?int $difficulty,
        public string $status,
        public ?string $scheduledStart,
        public ?string $liveStreamUrl,
        public array $stageRules,
        public int $totalPossiblePoints,
    ) {}

    /**
     * Wire format of the `stage` prop of Stages/Show.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'date' => $this->date,
            'type' => $this->type,
            'type_value' => $this->typeValue,
            'distance' => $this->distance,
            'elevation_gain' => $this->elevationGain,
            'profile_image' => $this->profileImage,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'difficulty' => $this->difficulty,
            'status' => $this->status,
            'scheduled_start' => $this->scheduledStart,
            'live_stream_url' => $this->liveStreamUrl,
        ];
    }
}
