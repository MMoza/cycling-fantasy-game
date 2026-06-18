<?php

declare(strict_types=1);

use App\Domain\Entities\League;

test('creates league with factory method', function () {
    $league = League::create(
        name: 'Amigos del Tour',
        editionId: 'edition-uuid',
        scoringSystemId: 'scoring-uuid',
        ownerId: 'user-uuid',
    );

    expect($league->id)->toBeUuid();
    expect($league->name)->toBe('Amigos del Tour');
    expect($league->editionId)->toBe('edition-uuid');
    expect($league->scoringSystemId)->toBe('scoring-uuid');
    expect($league->ownerId)->toBe('user-uuid');
    expect($league->inviteCode)->toHaveLength(8);
});

test('regenerates invite code', function () {
    $league = League::create(
        name: 'Amigos del Tour',
        editionId: 'edition-uuid',
        scoringSystemId: 'scoring-uuid',
        ownerId: 'user-uuid',
    );

    $newLeague = $league->regenerateInviteCode();

    expect($newLeague->inviteCode)->not->toBe($league->inviteCode);
    expect($newLeague->inviteCode)->toHaveLength(8);
    expect($newLeague->id)->toBe($league->id);
});
