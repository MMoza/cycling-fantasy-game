<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $type = $request->get('type', 'all');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        if (in_array($type, ['all', 'users'])) {
            $results['users'] = User::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->limit(5)
                ->get()
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'type' => 'user']);
        }

        if (in_array($type, ['all', 'riders'])) {
            $results['riders'] = RiderModel::where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->limit(5)
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => "{$r->first_name} {$r->last_name}",
                    'type' => 'rider',
                ]);
        }

        if (in_array($type, ['all', 'teams'])) {
            $results['teams'] = TeamModel::where('name', 'like', "%{$q}%")
                ->limit(5)
                ->get()
                ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'type' => 'team']);
        }

        if (in_array($type, ['all', 'competitions'])) {
            $results['competitions'] = CompetitionModel::where('name', 'like', "%{$q}%")
                ->limit(5)
                ->get()
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'type' => 'competition']);
        }

        return response()->json($results);
    }
}
