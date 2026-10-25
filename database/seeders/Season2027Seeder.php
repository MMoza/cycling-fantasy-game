<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\UseCases\Admin\Edition\StoreEditionUseCase;
use App\Domain\ValueObjects\CompetitionType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Base de la temporada 2027: crea/normaliza las 29 competiciones oficiales
 * (categorías PCS: Grand Tours, Major Tours, Monuments, Championships y
 * Top classics) y sus ediciones 2027 con liga oficial automática.
 *
 * - Nombres normalizados a PCS (renombrando las competiciones existentes que
 *   estaban en español, p.ej. 'Tour de Francia' → 'Tour de France').
 * - `pcs_slug` rellenado con los slugs verificados de procyclingstats.com
 *   para que los seeders de recorridos/participantes localicen cada carrera.
 * - Las ediciones se crean con `StoreEditionUseCase`, que genera la liga
 *   oficial ("Liga Oficial {carrera} 2027", scoring según tipo de carrera).
 * - NO crea etapas, recorridos ni participantes: eso vive en seeders
 *   particulares posteriores que buscarán por `pcs_slug` + año.
 *
 * Idempotente: re-ejecutable sin duplicados.
 *
 * Uso:
 *   php artisan db:seed --class=Season2027Seeder
 */
class Season2027Seeder extends Seeder
{
    /**
     * [name, type, country, pcs_slug, start, end] (+ aliases de nombres previos).
     *
     * Fechas: calendario UCI 2027 publicado en procyclingstats.com.
     * World/European Championships: TBC (PCS aún no publica fechas 2027) —
     * provisionales marcadas abajo, actualizar cuando PCS las confirme.
     */
    private const RACES = [
        // ── Grand Tours ─────────────────────────────────────────────
        ['Tour de France', CompetitionType::GC, 'FR', 'tour-de-france', '2027-07-02', '2027-07-25', ['Tour de Francia']],
        ['Giro d\'Italia', CompetitionType::GC, 'IT', 'giro-d-italia', '2027-05-08', '2027-05-30', ['Giro de Italia']],
        ['Vuelta a España', CompetitionType::GC, 'ES', 'vuelta-a-espana', '2027-09-04', '2027-09-24', ['La Vuelta', 'Vuelta de España']],

        // ── Major Tours ─────────────────────────────────────────────
        ['Paris-Nice', CompetitionType::Major, 'FR', 'paris-nice', '2027-03-07', '2027-03-14'],
        ['Tirreno-Adriatico', CompetitionType::Major, 'IT', 'tirreno-adriatico', '2027-03-08', '2027-03-14'],
        ['Volta a Catalunya', CompetitionType::Major, 'ES', 'volta-a-catalunya', '2027-03-22', '2027-03-28'],
        ['Itzulia Basque Country', CompetitionType::Major, 'ES', 'itzulia-basque-country', '2027-04-05', '2027-04-10'],
        ['Tour de Romandie', CompetitionType::Major, 'CH', 'tour-de-romandie', '2027-04-27', '2027-05-02'],
        ['Tour de Suisse', CompetitionType::Major, 'CH', 'tour-de-suisse', '2027-06-16', '2027-06-20'],
        ['Tour Auvergne - Rhône-Alpes', CompetitionType::Major, 'FR', 'tour-auvergne-rhone-alpes', '2027-06-06', '2027-06-13'],

        // ── Monuments ───────────────────────────────────────────────
        ['Milano-SanRemo', CompetitionType::Monument, 'IT', 'milano-sanremo', '2027-03-20', '2027-03-20'],
        ['Ronde van Vlaanderen', CompetitionType::Monument, 'BE', 'ronde-van-vlaanderen', '2027-04-04', '2027-04-04'],
        ['Paris-Roubaix', CompetitionType::Monument, 'FR', 'paris-roubaix', '2027-04-11', '2027-04-11'],
        ['Liège-Bastogne-Liège', CompetitionType::Monument, 'BE', 'liege-bastogne-liege', '2027-04-25', '2027-04-25'],
        ['Il Lombardia', CompetitionType::Monument, 'IT', 'il-lombardia', '2027-10-09', '2027-10-09', ['Il Lombardía', 'Giro de Lombardía']],

        // ── Championships ───────────────────────────────────────────
        // TODO(TBC): PCS aún no publica fechas 2027 — provisionales (sede: Limburg, BE).
        ['World Championships', CompetitionType::Championship, 'BE', 'world-championship', '2027-09-19', '2027-09-26', ['World Championship']],
        // TODO(TBC): PCS aún no publica fechas 2027 — provisionales (país por confirmar).
        ['European Championships', CompetitionType::Championship, 'FR', 'uec-road-european-championships', '2027-09-08', '2027-09-12'],

        // ── Top classics ────────────────────────────────────────────
        ['Omloop Het Nieuwsblad', CompetitionType::Classic, 'BE', 'omloop-het-nieuwsblad', '2027-02-27', '2027-02-27'],
        ['Strade Bianche', CompetitionType::Classic, 'IT', 'strade-bianche', '2027-03-06', '2027-03-06'],
        ['E3 Classic', CompetitionType::Classic, 'BE', 'e3-harelbeke', '2027-03-26', '2027-03-26'],
        ['Gent-Wevelgem', CompetitionType::Classic, 'BE', 'gent-wevelgem', '2027-03-28', '2027-03-28'],
        ['Dwars door Vlaanderen', CompetitionType::Classic, 'BE', 'dwars-door-vlaanderen', '2027-03-31', '2027-03-31'],
        ['Eschborn-Frankfurt', CompetitionType::Classic, 'DE', 'eschborn-frankfurt', '2027-05-01', '2027-05-01'],
        ['Amstel Gold Race', CompetitionType::Classic, 'NL', 'amstel-gold-race', '2027-04-18', '2027-04-18'],
        ['La Flèche Wallonne', CompetitionType::Classic, 'BE', 'la-fleche-wallone', '2027-04-21', '2027-04-21'],
        ['San Sebastian', CompetitionType::Classic, 'ES', 'san-sebastian', '2027-07-31', '2027-07-31'],
        ['Bretagne Classic', CompetitionType::Classic, 'FR', 'bretagne-classic', '2027-08-22', '2027-08-22'],
        ['GP Québec', CompetitionType::Classic, 'CA', 'gp-quebec', '2027-09-10', '2027-09-10'],
        ['GP Montréal', CompetitionType::Classic, 'CA', 'gp-montreal', '2027-09-12', '2027-09-12'],
    ];

    public function run(): void
    {
        $this->call([CountrySeeder::class, ScoringSystemSeeder::class, AdminUserSeeder::class]);

        $this->ensureCountries();

        $admin = User::where('is_admin', true)->firstOrFail();
        $storeEdition = new StoreEditionUseCase;

        foreach (self::RACES as $race) {
            [$name, $type, $country, $slug, $start, $end] = $race;
            $aliases = $race[6] ?? [];

            $competition = $this->resolveCompetition(
                name: $name,
                type: $type,
                countryId: $country,
                slug: $slug,
                aliases: $aliases,
            );

            $edition = EditionModel::where('competition_id', $competition->id)
                ->where('year', 2027)
                ->first();

            if ($edition !== null) {
                continue;
            }

            $storeEdition->execute($competition->id, [
                'year' => 2027,
                'start_date' => $start,
                'end_date' => $end,
            ], $admin->id);
        }

        $this->command?->info('Season2027Seeder: '.count(self::RACES).' competiciones con su edición 2027 y liga oficial.');
    }

    /**
     * Busca la competición por nombre actual, alias históricos o slug PCS
     * (renombrándola al nombre PCS si venía en español) o la crea.
     *
     * @param  array<int, string>  $aliases
     */
    private function resolveCompetition(
        string $name,
        CompetitionType $type,
        string $countryId,
        string $slug,
        array $aliases,
    ): CompetitionModel {
        $competition = CompetitionModel::whereIn('name', array_merge([$name], $aliases))->first()
            ?? CompetitionModel::where('pcs_slug', $slug)->first();

        if ($competition !== null) {
            $competition->update([
                'name' => $name,
                'type' => $type,
                'country_id' => $countryId,
                'pcs_slug' => $slug,
                'active' => true,
            ]);

            return $competition;
        }

        return CompetitionModel::create([
            'id' => Str::uuid()->toString(),
            'name' => $name,
            'type' => $type,
            'country_id' => $countryId,
            'pcs_slug' => $slug,
            'active' => true,
        ]);
    }

    /** @var array<string, string> */
    private const COUNTRIES = [
        'FR' => 'Francia',
        'IT' => 'Italia',
        'ES' => 'España',
        'CH' => 'Suiza',
        'BE' => 'Bélgica',
        'NL' => 'Países Bajos',
        'DE' => 'Alemania',
        'CA' => 'Canadá',
    ];

    private function ensureCountries(): void
    {
        foreach (self::COUNTRIES as $code => $name) {
            CountryModel::firstOrCreate(['id' => $code], ['name' => $name]);
        }
    }
}
