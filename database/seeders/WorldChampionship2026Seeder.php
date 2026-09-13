<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorldChampionship2026Seeder extends Seeder
{
    private const YEAR = 2026;

    private array $teamIds = [];

    public function run(): void
    {
        $edition = EditionModel::whereHas('competition', fn ($q) => $q->where('name', 'Mundial 2026'))
            ->where('year', self::YEAR)
            ->first();

        if (! $edition) {
            return;
        }

        $this->createTeams();
        $this->createRosters();
        $this->createParticipants($edition->id);
        $this->createStages($edition->id);
    }

    private function createTeams(): void
    {
        $teams = [
            ['name' => 'Bélgica', 'abbr' => 'BEL', 'country' => 'BE'],
            ['name' => 'Dinamarca', 'abbr' => 'DEN', 'country' => 'DK'],
            ['name' => 'Italia', 'abbr' => 'ITA', 'country' => 'IT'],
            ['name' => 'Países Bajos', 'abbr' => 'NED', 'country' => 'NL'],
            ['name' => 'Eslovenia', 'abbr' => 'SLO', 'country' => 'SI'],
            ['name' => 'España', 'abbr' => 'ESP', 'country' => 'ES'],
            ['name' => 'Estados Unidos', 'abbr' => 'USA', 'country' => 'US'],
            ['name' => 'Reino Unido', 'abbr' => 'GBR', 'country' => 'GB'],
            ['name' => 'Francia', 'abbr' => 'FRA', 'country' => 'FR'],
            ['name' => 'Australia', 'abbr' => 'AUS', 'country' => 'AU'],
            ['name' => 'Canadá', 'abbr' => 'CAN', 'country' => 'CA'],
            ['name' => 'Colombia', 'abbr' => 'COL', 'country' => 'CO'],
            ['name' => 'México', 'abbr' => 'MEX', 'country' => 'MX'],
            ['name' => 'Noruega', 'abbr' => 'NOR', 'country' => 'NO'],
            ['name' => 'Suiza', 'abbr' => 'SUI', 'country' => 'CH'],
            ['name' => 'Alemania', 'abbr' => 'GER', 'country' => 'DE'],
            ['name' => 'Nueva Zelanda', 'abbr' => 'NZL', 'country' => 'NZ'],
            ['name' => 'Portugal', 'abbr' => 'POR', 'country' => 'PT'],
            ['name' => 'Eritrea', 'abbr' => 'ERI', 'country' => 'ER'],
            ['name' => 'Ecuador', 'abbr' => 'ECU', 'country' => 'EC'],
            ['name' => 'Irlanda', 'abbr' => 'IRL', 'country' => 'IE'],
            ['name' => 'Polonia', 'abbr' => 'POL', 'country' => 'PL'],
            ['name' => 'Letonia', 'abbr' => 'LAT', 'country' => 'LV'],
            ['name' => 'Austria', 'abbr' => 'AUT', 'country' => 'AT'],
            ['name' => 'Chequia', 'abbr' => 'CZE', 'country' => 'CZ'],
            ['name' => 'Bermudas', 'abbr' => 'BER', 'country' => 'BM'],
            ['name' => 'Guatemala', 'abbr' => 'GUA', 'country' => 'GT'],
            ['name' => 'Kazajistán', 'abbr' => 'KAZ', 'country' => 'KZ'],
            ['name' => 'Uruguay', 'abbr' => 'URU', 'country' => 'UY'],
            ['name' => 'Venezuela', 'abbr' => 'VEN', 'country' => 'VE'],
            ['name' => 'Brasil', 'abbr' => 'BRA', 'country' => 'BR'],
            ['name' => 'Costa Rica', 'abbr' => 'CRC', 'country' => 'CR'],
            ['name' => 'Hungría', 'abbr' => 'HUN', 'country' => 'HU'],
            ['name' => 'Japón', 'abbr' => 'JPN', 'country' => 'JP'],
            ['name' => 'Mauricio', 'abbr' => 'MRI', 'country' => 'MU'],
            ['name' => 'Mónaco', 'abbr' => 'MON', 'country' => 'MC'],
            ['name' => 'Sudáfrica', 'abbr' => 'RSA', 'country' => 'ZA'],
            ['name' => 'Suecia', 'abbr' => 'SWE', 'country' => 'SE'],
            ['name' => 'Argelia', 'abbr' => 'ALG', 'country' => 'DZ'],
            ['name' => 'Belice', 'abbr' => 'BIZ', 'country' => 'BZ'],
            ['name' => 'Chile', 'abbr' => 'CHI', 'country' => 'CL'],
            ['name' => 'China', 'abbr' => 'CHN', 'country' => 'CN'],
            ['name' => 'Chipre', 'abbr' => 'CYP', 'country' => 'CY'],
            ['name' => 'Dominica', 'abbr' => 'DMA', 'country' => 'DM'],
            ['name' => 'Estonia', 'abbr' => 'EST', 'country' => 'EE'],
            ['name' => 'Guinea-Bisáu', 'abbr' => 'GBS', 'country' => 'GW'],
            ['name' => 'Grecia', 'abbr' => 'GRE', 'country' => 'GR'],
            ['name' => 'Honduras', 'abbr' => 'HON', 'country' => 'HN'],
            ['name' => 'Israel', 'abbr' => 'ISR', 'country' => 'IL'],
            ['name' => 'Arabia Saudita', 'abbr' => 'KSA', 'country' => 'SA'],
            ['name' => 'Luxemburgo', 'abbr' => 'LUX', 'country' => 'LU'],
            ['name' => 'Mongolia', 'abbr' => 'MGL', 'country' => 'MN'],
            ['name' => 'Panamá', 'abbr' => 'PAN', 'country' => 'PA'],
            ['name' => 'Rumanía', 'abbr' => 'ROU', 'country' => 'RO'],
            ['name' => 'Serbia', 'abbr' => 'SRB', 'country' => 'RS'],
            ['name' => 'Eslovaquia', 'abbr' => 'SVK', 'country' => 'SK'],
            ['name' => 'Tailandia', 'abbr' => 'THA', 'country' => 'TH'],
            ['name' => 'Ucrania', 'abbr' => 'UKR', 'country' => 'UA'],
            ['name' => 'Uzbekistán', 'abbr' => 'UZB', 'country' => 'UZ'],
            ['name' => 'Ruanda', 'abbr' => 'RWA', 'country' => 'RW'],
            ['name' => 'Jordania', 'abbr' => 'JOR', 'country' => 'JO'],
            ['name' => 'Islas Caimán', 'abbr' => 'CAY', 'country' => 'KY'],
        ];

        foreach ($teams as $t) {
            $team = TeamModel::firstOrCreate(['name' => $t['name']], [
                'id' => Str::uuid()->toString(),
                'abbreviation' => $t['abbr'],
                'country_id' => $t['country'],
            ]);

            $this->teamIds[$t['abbr']] = $team->id;
        }
    }

    private function createRosters(): void
    {
        $competition = CompetitionModel::where('name', 'Mundial 2026')->first();
        if ($competition) {
            $edition = EditionModel::where('competition_id', $competition->id)
                ->where('year', self::YEAR)
                ->first();
            if ($edition) {
                CompetitionParticipantModel::where('edition_id', $edition->id)->delete();
            }
        }

        $ridersByTeam = [
            'BEL' => [
                ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
                ['first' => 'Wout', 'last' => 'Van Aert', 'country' => 'BE'],
                ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
                ['first' => 'Quinten', 'last' => 'Hermans', 'country' => 'BE'],
                ['first' => 'Thibau', 'last' => 'Nys', 'country' => 'BE'],
                ['first' => 'Alec', 'last' => 'Segaert', 'country' => 'BE'],
                ['first' => 'Maxim', 'last' => 'Van Gils', 'country' => 'BE'],
                ['first' => 'Gianni', 'last' => 'Vermeersch', 'country' => 'BE'],
                ['first' => 'Rune', 'last' => 'Herregodts', 'country' => 'BE'],
            ],
            'DEN' => [
                ['first' => 'Mikkel Frolich', 'last' => 'Honore', 'country' => 'DK'],
                ['first' => 'Kasper', 'last' => 'Asgreen', 'country' => 'DK'],
                ['first' => 'Mikkel', 'last' => 'Bjerg', 'country' => 'DK'],
                ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
                ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
                ['first' => 'Andreas', 'last' => 'Kron', 'country' => 'DK'],
                ['first' => 'Michael', 'last' => 'Valgren', 'country' => 'DK'],
                ['first' => 'Soren', 'last' => 'Kragh Andersen', 'country' => 'DK'],
            ],
            'ITA' => [
                ['first' => 'Alberto', 'last' => 'Bettiol', 'country' => 'IT'],
                ['first' => 'Mattia', 'last' => 'Cattaneo', 'country' => 'IT'],
                ['first' => 'Giulio', 'last' => 'Ciccone', 'country' => 'IT'],
                ['first' => 'Lorenzo Mark', 'last' => 'Finn', 'country' => 'IT'],
                ['first' => 'Giulio', 'last' => 'Pellizzari', 'country' => 'IT'],
                ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],
                ['first' => 'Christian', 'last' => 'Scaroni', 'country' => 'IT'],
                ['first' => 'Matteo', 'last' => 'Trentin', 'country' => 'IT'],
                ['first' => 'Matteo', 'last' => 'Sobrero', 'country' => 'IT'],
                ['first' => 'Filippo', 'last' => 'Ganna', 'country' => 'IT'],
            ],
            'NED' => [
                ['first' => 'Mathieu', 'last' => 'Van Der Poel', 'country' => 'NL'],
                ['first' => 'Daan', 'last' => 'Hoole', 'country' => 'NL'],
                ['first' => 'Bart', 'last' => 'Lemmen', 'country' => 'NL'],
                ['first' => 'Tim', 'last' => 'Van Dijke', 'country' => 'NL'],
                ['first' => 'Bauke', 'last' => 'Mollema', 'country' => 'NL'],
                ['first' => 'Pascal', 'last' => 'Eenkhoorn', 'country' => 'NL'],
                ['first' => 'Menno', 'last' => 'Huising', 'country' => 'NL'],
                ['first' => 'Mathijs', 'last' => 'Paasschens', 'country' => 'NL'],
            ],
            'SLO' => [
                ['first' => 'Tilen', 'last' => 'Finkst', 'country' => 'SI'],
                ['first' => 'Gal', 'last' => 'Glivar', 'country' => 'SI'],
                ['first' => 'Matevz', 'last' => 'Govekar', 'country' => 'SI'],
                ['first' => 'Luka', 'last' => 'Mezgec', 'country' => 'SI'],
                ['first' => 'Matej', 'last' => 'Mohoric', 'country' => 'SI'],
                ['first' => 'Jan', 'last' => 'Tratnik', 'country' => 'SI'],
                ['first' => 'Primoz', 'last' => 'Roglic', 'country' => 'SI'],
                ['first' => 'Jakob', 'last' => 'Omrcel', 'country' => 'SI'],
            ],
            'ESP' => [
                ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
                ['first' => 'Ivan', 'last' => 'Romeo', 'country' => 'ES'],
                ['first' => 'Raul', 'last' => 'Garcia Pierna', 'country' => 'ES'],
                ['first' => 'Carlos', 'last' => 'Verona', 'country' => 'ES'],
                ['first' => 'Igor', 'last' => 'Arrieta', 'country' => 'ES'],
                ['first' => 'Markel', 'last' => 'Beloki', 'country' => 'ES'],
                ['first' => 'Enric', 'last' => 'Mas', 'country' => 'ES'],
                ['first' => 'Marcel', 'last' => 'Camprubi', 'country' => 'ES'],
                ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
            ],
            'USA' => [
                ['first' => 'Quinn', 'last' => 'Simmons', 'country' => 'US'],
                ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
                ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
                ['first' => 'Neilson', 'last' => 'Powless', 'country' => 'US'],
                ['first' => 'Sean', 'last' => 'Quinn', 'country' => 'US'],
                ['first' => 'Artem', 'last' => 'Shmidt', 'country' => 'US'],
                ['first' => 'Kevin', 'last' => 'Vermaerke', 'country' => 'US'],
                ['first' => 'Larry', 'last' => 'Warbasse', 'country' => 'US'],
            ],
            'GBR' => [
                ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
                ['first' => 'Mark', 'last' => 'Donovan', 'country' => 'GB'],
                ['first' => 'Oscar', 'last' => 'Onley', 'country' => 'GB'],
                ['first' => 'Finlay', 'last' => 'Pickering', 'country' => 'GB'],
                ['first' => 'James', 'last' => 'Shaw', 'country' => 'GB'],
                ['first' => 'Callum', 'last' => 'Thornley', 'country' => 'GB'],
                ['first' => 'Fred', 'last' => 'Wright', 'country' => 'GB'],
                ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],
                ['first' => 'Ethan', 'last' => 'Hayter', 'country' => 'GB'],
            ],
            'FRA' => [
                ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
                ['first' => 'Pavel', 'last' => 'Sivakov', 'country' => 'FR'],
                ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
                ['first' => 'Jordan', 'last' => 'Labrosse', 'country' => 'FR'],
                ['first' => 'Valentin', 'last' => 'Paret-Peintre', 'country' => 'FR'],
                ['first' => 'Nicolas', 'last' => 'Prodhomme', 'country' => 'FR'],
                ['first' => 'Alex', 'last' => 'Baudin', 'country' => 'FR'],
            ],
            'AUS' => [
                ['first' => 'Sebastian', 'last' => 'Berwick', 'country' => 'AU'],
                ['first' => 'Jack', 'last' => 'Haig', 'country' => 'AU'],
                ['first' => 'Jai', 'last' => 'Hindley', 'country' => 'AU'],
                ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
                ['first' => 'Ben', 'last' => 'O\'Connor', 'country' => 'AU'],
                ['first' => 'Michael', 'last' => 'Storer', 'country' => 'AU'],
                ['first' => 'Luke', 'last' => 'Tuckwell', 'country' => 'AU'],
                ['first' => 'Conor', 'last' => 'Leahy', 'country' => 'AU'],
            ],
            'CAN' => [
                ['first' => 'Derek', 'last' => 'Gee-West', 'country' => 'CA'],
                ['first' => 'Hugo', 'last' => 'Houle', 'country' => 'CA'],
                ['first' => 'Michael', 'last' => 'Leonard', 'country' => 'CA'],
                ['first' => 'Michael', 'last' => 'Woods', 'country' => 'CA'],
                ['first' => 'Nickolas', 'last' => 'Zukowsky', 'country' => 'CA'],
                ['first' => 'Pier-Andre', 'last' => 'Cote', 'country' => 'CA'],
            ],
            'COL' => [
                ['first' => 'Nairo', 'last' => 'Quintana', 'country' => 'CO'],
                ['first' => 'Brandon Smith', 'last' => 'Rivera', 'country' => 'CO'],
                ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
                ['first' => 'Santiago', 'last' => 'Buitrago', 'country' => 'CO'],
                ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
                ['first' => 'Wilmar Andres', 'last' => 'Paredes', 'country' => 'CO'],
                ['first' => 'Walter', 'last' => 'Vargas', 'country' => 'CO'],
            ],
            'MEX' => [
                ['first' => 'Isaac', 'last' => 'Del Toro', 'country' => 'MX'],
                ['first' => 'Eder', 'last' => 'Frayre', 'country' => 'MX'],
                ['first' => 'Edgar David', 'last' => 'Cadena', 'country' => 'MX'],
                ['first' => 'Ulises Alfredo', 'last' => 'Castillo', 'country' => 'MX'],
                ['first' => 'Jose Antonio', 'last' => 'Escarcega', 'country' => 'MX'],
                ['first' => 'Carlos Alfonso', 'last' => 'Garcia', 'country' => 'MX'],
            ],
            'NOR' => [
                ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
                ['first' => 'Tobias', 'last' => 'Foss', 'country' => 'NO'],
                ['first' => 'Embret', 'last' => 'Svestad-Bardseng', 'country' => 'NO'],
                ['first' => 'Jorgen', 'last' => 'Nordhagen', 'country' => 'NO'],
                ['first' => 'Andreas', 'last' => 'Leknessund', 'country' => 'NO'],
                ['first' => 'Anders', 'last' => 'Skaareth', 'country' => 'NO'],
            ],
            'SUI' => [
                ['first' => 'Fabio', 'last' => 'Christen', 'country' => 'CH'],
                ['first' => 'Jan', 'last' => 'Christen', 'country' => 'CH'],
                ['first' => 'Marc', 'last' => 'Hirschi', 'country' => 'CH'],
                ['first' => 'Mauro', 'last' => 'Schmid', 'country' => 'CH'],
                ['first' => 'Stefan', 'last' => 'Bissegger', 'country' => 'CH'],
                ['first' => 'Stefan', 'last' => 'Kung', 'country' => 'CH'],
            ],
            'GER' => [
                ['first' => 'Florian', 'last' => 'Lipowitz', 'country' => 'DE'],
                ['first' => 'Marco', 'last' => 'Brenner', 'country' => 'DE'],
                ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],
                ['first' => 'Felix', 'last' => 'Engelhardt', 'country' => 'DE'],
                ['first' => 'Nico', 'last' => 'Denz', 'country' => 'DE'],
                ['first' => 'Maximilian', 'last' => 'Schachmann', 'country' => 'DE'],
                ['first' => 'Max', 'last' => 'Walscheid', 'country' => 'DE'],
                ['first' => 'Jasha', 'last' => 'Sutterlin', 'country' => 'DE'],
            ],
            'NZL' => [
                ['first' => 'Ben', 'last' => 'Oliver', 'country' => 'NZ'],
                ['first' => 'Corbin', 'last' => 'Strong', 'country' => 'NZ'],
                ['first' => 'Finn', 'last' => 'Fisher-Black', 'country' => 'NZ'],
                ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
                ['first' => 'Laurence', 'last' => 'Pithie', 'country' => 'NZ'],
            ],
            'POR' => [
                ['first' => 'Afonso', 'last' => 'Eulalio', 'country' => 'PT'],
                ['first' => 'Ivo', 'last' => 'Oliveira', 'country' => 'PT'],
                ['first' => 'Tiago', 'last' => 'Antunes', 'country' => 'PT'],
                ['first' => 'Nelson', 'last' => 'Oliveira', 'country' => 'PT'],
                ['first' => 'Antonio', 'last' => 'Morgado', 'country' => 'PT'],
            ],
            'ERI' => [
                ['first' => 'Amanuel', 'last' => 'Ghebreigzabhier', 'country' => 'ER'],
                ['first' => 'Biniam', 'last' => 'Girmay', 'country' => 'ER'],
                ['first' => 'Merhawi', 'last' => 'Kudus', 'country' => 'ER'],
                ['first' => 'Henok', 'last' => 'Mulubrhan', 'country' => 'ER'],
                ['first' => 'Natnael', 'last' => 'Tesfatsion', 'country' => 'ER'],
            ],
            'ECU' => [
                ['first' => 'Richard', 'last' => 'Carapaz', 'country' => 'EC'],
                ['first' => 'Jhonatan', 'last' => 'Narvaez', 'country' => 'EC'],
                ['first' => 'Jefferson Alveiro', 'last' => 'Cepeda', 'country' => 'EC'],
                ['first' => 'Jefferson Alexander', 'last' => 'Cepeda', 'country' => 'EC'],
            ],
            'IRL' => [
                ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
                ['first' => 'Darren', 'last' => 'Rafferty', 'country' => 'IE'],
                ['first' => 'Jamie', 'last' => 'Meehan', 'country' => 'IE'],
                ['first' => 'Ryan', 'last' => 'Mullen', 'country' => 'IE'],
            ],
            'POL' => [
                ['first' => 'Michal', 'last' => 'Kwiatkowski', 'country' => 'PL'],
                ['first' => 'Mateusz', 'last' => 'Gajdulewicz', 'country' => 'PL'],
                ['first' => 'Jakub', 'last' => 'Kaczmarek', 'country' => 'PL'],
                ['first' => 'Piotr', 'last' => 'Pekala', 'country' => 'PL'],
            ],
            'LAT' => [
                ['first' => 'Kristians', 'last' => 'Belohvosciks', 'country' => 'LV'],
                ['first' => 'Emils', 'last' => 'Liepins', 'country' => 'LV'],
                ['first' => 'Martins', 'last' => 'Pluto', 'country' => 'LV'],
                ['first' => 'Toms', 'last' => 'Skujins', 'country' => 'LV'],
            ],
            'AUT' => [
                ['first' => 'Felix', 'last' => 'Gall', 'country' => 'AT'],
                ['first' => 'Felix', 'last' => 'Grossschartner', 'country' => 'AT'],
                ['first' => 'Patrick', 'last' => 'Konrad', 'country' => 'AT'],
            ],
            'CZE' => [
                ['first' => 'Mathias', 'last' => 'Vacek', 'country' => 'CZ'],
                ['first' => 'Jakub', 'last' => 'Otruba', 'country' => 'CZ'],
                ['first' => 'Pavel', 'last' => 'Novak', 'country' => 'CZ'],
            ],
            'BER' => [
                ['first' => 'Kaden', 'last' => 'Hopkins', 'country' => 'BM'],
                ['first' => 'Nicholas', 'last' => 'Narraway', 'country' => 'BM'],
            ],
            'GUA' => [
                ['first' => 'Manuel', 'last' => 'Rodas', 'country' => 'GT'],
                ['first' => 'Juan Mardoqueo', 'last' => 'Vasquez', 'country' => 'GT'],
            ],
            'KAZ' => [
                ['first' => 'Anton', 'last' => 'Kuzmin', 'country' => 'KZ'],
                ['first' => 'Daniil', 'last' => 'Marukhin', 'country' => 'KZ'],
            ],
            'URU' => [
                ['first' => 'Eric Antonio', 'last' => 'Fagundez', 'country' => 'UY'],
                ['first' => 'Guillermo Thomas', 'last' => 'Silva', 'country' => 'UY'],
            ],
            'VEN' => [
                ['first' => 'Orluis', 'last' => 'Aular', 'country' => 'VE'],
                ['first' => 'Francisco Joel', 'last' => 'Penuela', 'country' => 'VE'],
            ],
            'BRA' => [
                ['first' => 'Henrique da Silva', 'last' => 'Avancini', 'country' => 'BR'],
            ],
            'CRC' => [
                ['first' => 'Luis Daniel', 'last' => 'Oses', 'country' => 'CR'],
            ],
            'HUN' => [
                ['first' => 'Attila', 'last' => 'Valter', 'country' => 'HU'],
                ['first' => 'Janos', 'last' => 'Pelikan', 'country' => 'HU'],
                ['first' => 'Barnabas', 'last' => 'Peak', 'country' => 'HU'],
            ],
            'JPN' => [
                ['first' => 'Jo', 'last' => 'Hashikawa', 'country' => 'JP'],
            ],
            'MRI' => [
                ['first' => 'Alexandre', 'last' => 'Mayer', 'country' => 'MU'],
            ],
            'MON' => [
                ['first' => 'Victor', 'last' => 'Langelotti', 'country' => 'MC'],
            ],
            'RSA' => [
                ['first' => 'Byron', 'last' => 'Munton', 'country' => 'ZA'],
            ],
            'SWE' => [
                ['first' => 'Jakob', 'last' => 'Soderqvist', 'country' => 'SE'],
            ],
            'ALG' => [
                ['first' => 'Oussama Abdellah', 'last' => 'Mimouni', 'country' => 'DZ'],
            ],
            'BIZ' => [
                ['first' => 'Derrick', 'last' => 'Chavarria', 'country' => 'BZ'],
            ],
            'CHI' => [
                ['first' => 'Vicente', 'last' => 'Rojas', 'country' => 'CL'],
            ],
            'CHN' => [
                ['first' => 'You', 'last' => 'Li', 'country' => 'CN'],
                ['first' => 'Chenglu', 'last' => 'Liu', 'country' => 'CN'],
                ['first' => 'Houwang', 'last' => 'Cao', 'country' => 'CN'],
            ],
            'CYP' => [
                ['first' => 'Andreas', 'last' => 'Miltiadis', 'country' => 'CY'],
            ],
            'DMA' => [
                ['first' => 'Kohath', 'last' => 'Baron', 'country' => 'DM'],
            ],
            'EST' => [
                ['first' => 'Madis', 'last' => 'Mihkels', 'country' => 'EE'],
            ],
            'GBS' => [
                ['first' => 'Gil Landim', 'last' => 'Gomes', 'country' => 'GW'],
                ['first' => 'CA', 'last' => 'Apolinario', 'country' => 'GW'],
            ],
            'GRE' => [
                ['first' => 'Nikiforos', 'last' => 'Arvanitou', 'country' => 'GR'],
            ],
            'HON' => [
                ['first' => 'Fredd', 'last' => 'Matute', 'country' => 'HN'],
            ],
            'ISR' => [
                ['first' => 'Nadav', 'last' => 'Raisberg', 'country' => 'IL'],
            ],
            'KSA' => [
                ['first' => 'Ali', 'last' => 'Al Shaikhahmed', 'country' => 'SA'],
            ],
            'LUX' => [
                ['first' => 'Arno', 'last' => 'Wallenborn', 'country' => 'LU'],
                ['first' => 'Arthur', 'last' => 'Kluckers', 'country' => 'LU'],
            ],
            'MGL' => [
                ['first' => 'Maral-Erdene', 'last' => 'Batmunkh', 'country' => 'MN'],
            ],
            'PAN' => [
                ['first' => 'Christofer Robin', 'last' => 'Jurado', 'country' => 'PA'],
            ],
            'ROU' => [
                ['first' => 'Iustin-Ioan', 'last' => 'Vaidian', 'country' => 'RO'],
            ],
            'SRB' => [
                ['first' => 'Mihajlo', 'last' => 'Stolic', 'country' => 'RS'],
                ['first' => 'Ognjen', 'last' => 'Ilic', 'country' => 'RS'],
            ],
            'SVK' => [
                ['first' => 'Martin', 'last' => 'Svrcek', 'country' => 'SK'],
            ],
            'THA' => [
                ['first' => 'Athit', 'last' => 'Poulard', 'country' => 'TH'],
            ],
            'UKR' => [
                ['first' => 'Heorhii', 'last' => 'Antonenko', 'country' => 'UA'],
            ],
            'UZB' => [
                ['first' => 'Samandar', 'last' => 'Janikulov', 'country' => 'UZ'],
            ],
            'RWA' => [
                ['first' => 'Samuel', 'last' => 'Niyonkuru', 'country' => 'RW'],
                ['first' => 'Shemu', 'last' => 'Nsengiyumva', 'country' => 'RW'],
            ],
            'JOR' => [
                ['first' => 'Majid', 'last' => 'Abu Harrah', 'country' => 'JO'],
            ],
            'CAY' => [
                ['first' => 'Christopher', 'last' => 'Bodden', 'country' => 'KY'],
            ],
        ];

        foreach ($ridersByTeam as $abbr => $riders) {
            $teamId = $this->teamIds[$abbr] ?? null;
            if (! $teamId) {
                continue;
            }

            foreach ($riders as $r) {
                $rider = RiderModel::firstOrCreate([
                    'first_name' => $r['first'],
                    'last_name' => $r['last'],
                ], [
                    'id' => Str::uuid()->toString(),
                    'country_id' => $r['country'],
                ]);

                TeamRosterModel::firstOrCreate([
                    'team_id' => $teamId,
                    'rider_id' => $rider->id,
                    'year' => self::YEAR,
                ], [
                    'id' => Str::uuid()->toString(),
                ]);
            }
        }
    }

    private function createParticipants(string $editionId): void
    {
        $competition = CompetitionModel::where('name', 'Mundial 2026')->first();
        if (! $competition) {
            return;
        }

        $rosters = TeamRosterModel::where('year', self::YEAR)
            ->whereIn('team_id', array_values($this->teamIds))
            ->get();

        foreach ($rosters as $roster) {
            CompetitionParticipantModel::firstOrCreate([
                'competition_id' => $competition->id,
                'edition_id' => $editionId,
                'team_id' => $roster->team_id,
                'rider_id' => $roster->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
            ]);
        }
    }

    private function createStages(string $editionId): void
    {
        $stages = [
            [
                'num' => 1,
                'name' => 'CRI - ME',
                'date' => '2026-09-20',
                'type' => StageType::TimeTrial,
                'dist' => 40.0,
                'origin' => 'Montreal',
                'dest' => 'Montreal',
                'diff' => 3,
                'elev' => 110,
                'scheduled_start' => '2026-09-20 10:00:00',
            ],
            [
                'num' => 2,
                'name' => 'Ruta - ME',
                'date' => '2026-09-27',
                'type' => StageType::Hill,
                'dist' => 273.0,
                'origin' => 'Montreal',
                'dest' => 'Montreal',
                'diff' => 3,
                'elev' => 4300,
                'scheduled_start' => '2026-09-27 10:00:00',
            ],
        ];

        foreach ($stages as $s) {
            $existing = DB::table('stages')
                ->where('edition_id', $editionId)
                ->where('number', $s['num'])
                ->exists();

            if ($existing) {
                continue;
            }

            DB::table('stages')->insert([
                'id' => Str::uuid()->toString(),
                'edition_id' => $editionId,
                'number' => $s['num'],
                'name' => $s['name'],
                'date' => $s['date'],
                'type' => $s['type']->value,
                'distance' => $s['dist'],
                'origin' => $s['origin'],
                'destination' => $s['dest'],
                'difficulty' => $s['diff'],
                'elevation_gain' => $s['elev'],
                'scheduled_start' => $s['scheduled_start'],
                'status' => StageStatus::Upcoming->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->createStageParticipants($editionId);
    }

    private function createStageParticipants(string $editionId): void
    {
        $competition = CompetitionModel::where('name', 'Mundial 2026')->first();
        if (! $competition) {
            return;
        }

        $ttStage = DB::table('stages')
            ->where('edition_id', $editionId)
            ->where('number', 1)
            ->first();

        $roadStage = DB::table('stages')
            ->where('edition_id', $editionId)
            ->where('number', 2)
            ->first();

        if (! $ttStage || ! $roadStage) {
            return;
        }

        StageParticipantModel::where('stage_id', $ttStage->id)->delete();
        StageParticipantModel::where('stage_id', $roadStage->id)->delete();

        $ttRiders = $this->getTTRiders();
        $ttParticipants = $this->findParticipantsByNames($editionId, $ttRiders);

        foreach ($ttParticipants as $p) {
            StageParticipantModel::firstOrCreate([
                'stage_id' => $ttStage->id,
                'rider_id' => $p->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
                'team_id' => $p->team_id,
            ]);
        }

        $roadRiders = $this->getRoadRaceRiders();
        $roadParticipants = $this->findParticipantsByNames($editionId, $roadRiders);

        foreach ($roadParticipants as $rider) {
            StageParticipantModel::firstOrCreate([
                'stage_id' => $roadStage->id,
                'rider_id' => $rider->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
                'team_id' => $rider->team_id,
            ]);
        }
    }

    private function getTTRiders(): array
    {
        return [
            ['first' => 'Remco', 'last' => 'Evenepoel'],
            ['first' => 'Rune', 'last' => 'Herregodts'],
            ['first' => 'Alec', 'last' => 'Segaert'],
            ['first' => 'Derek', 'last' => 'Gee-West'],
            ['first' => 'Michael', 'last' => 'Leonard'],
            ['first' => 'Brandon Smith', 'last' => 'Rivera'],
            ['first' => 'Walter', 'last' => 'Vargas'],
            ['first' => 'Mikkel', 'last' => 'Bjerg'],
            ['first' => 'Kasper', 'last' => 'Asgreen'],
            ['first' => 'Paul', 'last' => 'Seixas'],
            ['first' => 'Bruno', 'last' => 'Armirail'],
            ['first' => 'Filippo', 'last' => 'Ganna'],
            ['first' => 'Matteo', 'last' => 'Sobrero'],
            ['first' => 'Janos', 'last' => 'Pelikan'],
            ['first' => 'Barnabas', 'last' => 'Peak'],
            ['first' => 'Tobias', 'last' => 'Foss'],
            ['first' => 'Andreas', 'last' => 'Leknessund'],
            ['first' => 'Pablo', 'last' => 'Castrillo'],
            ['first' => 'Ivan', 'last' => 'Romeo'],
            ['first' => 'Stefan', 'last' => 'Bissegger'],
            ['first' => 'Stefan', 'last' => 'Kung'],
            ['first' => 'Artem', 'last' => 'Shmidt'],
            ['first' => 'Brandon', 'last' => 'McNulty'],
            ['first' => 'Jasha', 'last' => 'Sutterlin'],
            ['first' => 'Max', 'last' => 'Walscheid'],
            ['first' => 'Nicholas', 'last' => 'Narraway'],
            ['first' => 'Kaden', 'last' => 'Hopkins'],
            ['first' => 'Chenglu', 'last' => 'Liu'],
            ['first' => 'Houwang', 'last' => 'Cao'],
            ['first' => 'Callum', 'last' => 'Thornley'],
            ['first' => 'Ethan', 'last' => 'Hayter'],
            ['first' => 'Gil Landim', 'last' => 'Gomes'],
            ['first' => 'CA', 'last' => 'Apolinario'],
            ['first' => 'Anton', 'last' => 'Kuzmin'],
            ['first' => 'Daniil', 'last' => 'Marukhin'],
            ['first' => 'Edgar David', 'last' => 'Cadena'],
            ['first' => 'Isaac', 'last' => 'Del Toro'],
            ['first' => 'Ivo', 'last' => 'Oliveira'],
            ['first' => 'Nelson', 'last' => 'Oliveira'],
            ['first' => 'Samuel', 'last' => 'Niyonkuru'],
            ['first' => 'Shemu', 'last' => 'Nsengiyumva'],
            ['first' => 'Henrique da Silva', 'last' => 'Avancini'],
            ['first' => 'Mathias', 'last' => 'Vacek'],
            ['first' => 'Ryan', 'last' => 'Mullen'],
            ['first' => 'Jo', 'last' => 'Hashikawa'],
            ['first' => 'Alexandre', 'last' => 'Mayer'],
            ['first' => 'Daan', 'last' => 'Hoole'],
            ['first' => 'Michal', 'last' => 'Kwiatkowski'],
            ['first' => 'Jan', 'last' => 'Tratnik'],
            ['first' => 'Byron', 'last' => 'Munton'],
            ['first' => 'Jakob', 'last' => 'Soderqvist'],
            ['first' => 'Conor', 'last' => 'Leahy'],
            ['first' => 'Derrick', 'last' => 'Chavarria'],
            ['first' => 'Christopher', 'last' => 'Bodden'],
            ['first' => 'Madis', 'last' => 'Mihkels'],
            ['first' => 'Fredd', 'last' => 'Matute'],
            ['first' => 'Majid', 'last' => 'Abu Harrah'],
            ['first' => 'Arthur', 'last' => 'Kluckers'],
            ['first' => 'Maral-Erdene', 'last' => 'Batmunkh'],
            ['first' => 'Ognjen', 'last' => 'Ilic'],
            ['first' => 'Heorhii', 'last' => 'Antonenko'],
            ['first' => 'Eric Antonio', 'last' => 'Fagundez'],
            ['first' => 'Florian', 'last' => 'Lipowitz'],
            ['first' => 'Marco', 'last' => 'Brenner'],
            ['first' => 'Georg', 'last' => 'Zimmermann'],
            ['first' => 'Felix', 'last' => 'Engelhardt'],
            ['first' => 'Nico', 'last' => 'Denz'],
            ['first' => 'Maximilian', 'last' => 'Schachmann'],
        ];
    }

    private function getRoadRaceRiders(): array
    {
        return [
            // Belgium
            ['first' => 'Remco', 'last' => 'Evenepoel'],
            ['first' => 'Wout', 'last' => 'Van Aert'],
            ['first' => 'Tiesj', 'last' => 'Benoot'],
            ['first' => 'Quinten', 'last' => 'Hermans'],
            ['first' => 'Thibau', 'last' => 'Nys'],
            ['first' => 'Alec', 'last' => 'Segaert'],
            ['first' => 'Maxim', 'last' => 'Van Gils'],
            ['first' => 'Gianni', 'last' => 'Vermeersch'],
            // Denmark
            ['first' => 'Mikkel Frolich', 'last' => 'Honore'],
            ['first' => 'Kasper', 'last' => 'Asgreen'],
            ['first' => 'Mikkel', 'last' => 'Bjerg'],
            ['first' => 'Mads', 'last' => 'Pedersen'],
            ['first' => 'Mattias', 'last' => 'Skjelmose'],
            ['first' => 'Andreas', 'last' => 'Kron'],
            ['first' => 'Michael', 'last' => 'Valgren'],
            ['first' => 'Soren', 'last' => 'Kragh Andersen'],
            // Italy
            ['first' => 'Alberto', 'last' => 'Bettiol'],
            ['first' => 'Mattia', 'last' => 'Cattaneo'],
            ['first' => 'Giulio', 'last' => 'Ciccone'],
            ['first' => 'Lorenzo Mark', 'last' => 'Finn'],
            ['first' => 'Giulio', 'last' => 'Pellizzari'],
            ['first' => 'Davide', 'last' => 'Piganzoli'],
            ['first' => 'Christian', 'last' => 'Scaroni'],
            ['first' => 'Matteo', 'last' => 'Trentin'],
            // Netherlands
            ['first' => 'Mathieu', 'last' => 'Van Der Poel'],
            ['first' => 'Daan', 'last' => 'Hoole'],
            ['first' => 'Bart', 'last' => 'Lemmen'],
            ['first' => 'Tim', 'last' => 'Van Dijke'],
            ['first' => 'Bauke', 'last' => 'Mollema'],
            ['first' => 'Pascal', 'last' => 'Eenkhoorn'],
            ['first' => 'Menno', 'last' => 'Huising'],
            ['first' => 'Mathijs', 'last' => 'Paasschens'],
            // Slovenia
            ['first' => 'Tilen', 'last' => 'Finkst'],
            ['first' => 'Gal', 'last' => 'Glivar'],
            ['first' => 'Matevz', 'last' => 'Govekar'],
            ['first' => 'Luka', 'last' => 'Mezgec'],
            ['first' => 'Matej', 'last' => 'Mohoric'],
            ['first' => 'Jan', 'last' => 'Tratnik'],
            ['first' => 'Primoz', 'last' => 'Roglic'],
            ['first' => 'Jakob', 'last' => 'Omrcel'],
            // Spain
            ['first' => 'Juan', 'last' => 'Ayuso'],
            ['first' => 'Ivan', 'last' => 'Romeo'],
            ['first' => 'Raul', 'last' => 'Garcia Pierna'],
            ['first' => 'Carlos', 'last' => 'Verona'],
            ['first' => 'Igor', 'last' => 'Arrieta'],
            ['first' => 'Markel', 'last' => 'Beloki'],
            ['first' => 'Enric', 'last' => 'Mas'],
            ['first' => 'Marcel', 'last' => 'Camprubi'],
            // United States
            ['first' => 'Quinn', 'last' => 'Simmons'],
            ['first' => 'Matteo', 'last' => 'Jorgenson'],
            ['first' => 'Brandon', 'last' => 'McNulty'],
            ['first' => 'Neilson', 'last' => 'Powless'],
            ['first' => 'Sean', 'last' => 'Quinn'],
            ['first' => 'Artem', 'last' => 'Shmidt'],
            ['first' => 'Kevin', 'last' => 'Vermaerke'],
            ['first' => 'Larry', 'last' => 'Warbasse'],
            // Great Britain
            ['first' => 'Tom', 'last' => 'Pidcock'],
            ['first' => 'Mark', 'last' => 'Donovan'],
            ['first' => 'Oscar', 'last' => 'Onley'],
            ['first' => 'Finlay', 'last' => 'Pickering'],
            ['first' => 'James', 'last' => 'Shaw'],
            ['first' => 'Callum', 'last' => 'Thornley'],
            ['first' => 'Fred', 'last' => 'Wright'],
            ['first' => 'Adam', 'last' => 'Yates'],
            // France
            ['first' => 'Paul', 'last' => 'Seixas'],
            ['first' => 'Pavel', 'last' => 'Sivakov'],
            ['first' => 'Bruno', 'last' => 'Armirail'],
            ['first' => 'Jordan', 'last' => 'Labrosse'],
            ['first' => 'Valentin', 'last' => 'Paret-Peintre'],
            ['first' => 'Nicolas', 'last' => 'Prodhomme'],
            ['first' => 'Alex', 'last' => 'Baudin'],
            // Australia
            ['first' => 'Sebastian', 'last' => 'Berwick'],
            ['first' => 'Jack', 'last' => 'Haig'],
            ['first' => 'Jai', 'last' => 'Hindley'],
            ['first' => 'Michael', 'last' => 'Matthews'],
            ['first' => 'Ben', 'last' => 'O\'Connor'],
            ['first' => 'Michael', 'last' => 'Storer'],
            ['first' => 'Luke', 'last' => 'Tuckwell'],
            // Canada
            ['first' => 'Derek', 'last' => 'Gee-West'],
            ['first' => 'Hugo', 'last' => 'Houle'],
            ['first' => 'Michael', 'last' => 'Leonard'],
            ['first' => 'Michael', 'last' => 'Woods'],
            ['first' => 'Nickolas', 'last' => 'Zukowsky'],
            ['first' => 'Pier-Andre', 'last' => 'Cote'],
            // Colombia
            ['first' => 'Nairo', 'last' => 'Quintana'],
            ['first' => 'Brandon Smith', 'last' => 'Rivera'],
            ['first' => 'Harold', 'last' => 'Tejada'],
            ['first' => 'Santiago', 'last' => 'Buitrago'],
            ['first' => 'Sergio', 'last' => 'Higuita'],
            ['first' => 'Wilmar Andres', 'last' => 'Paredes'],
            // Mexico
            ['first' => 'Isaac', 'last' => 'Del Toro'],
            ['first' => 'Eder', 'last' => 'Frayre'],
            ['first' => 'Edgar David', 'last' => 'Cadena'],
            ['first' => 'Ulises Alfredo', 'last' => 'Castillo'],
            ['first' => 'Jose Antonio', 'last' => 'Escarcega'],
            ['first' => 'Carlos Alfonso', 'last' => 'Garcia'],
            // Norway
            ['first' => 'Tobias Halland', 'last' => 'Johannessen'],
            ['first' => 'Tobias', 'last' => 'Foss'],
            ['first' => 'Embret', 'last' => 'Svestad-Bardseng'],
            ['first' => 'Jorgen', 'last' => 'Nordhagen'],
            ['first' => 'Andreas', 'last' => 'Leknessund'],
            ['first' => 'Anders', 'last' => 'Skaareth'],
            // Switzerland
            ['first' => 'Fabio', 'last' => 'Christen'],
            ['first' => 'Jan', 'last' => 'Christen'],
            ['first' => 'Marc', 'last' => 'Hirschi'],
            ['first' => 'Mauro', 'last' => 'Schmid'],
            ['first' => 'Stefan', 'last' => 'Bissegger'],
            ['first' => 'Stefan', 'last' => 'Kung'],
            // Germany
            ['first' => 'Florian', 'last' => 'Lipowitz'],
            ['first' => 'Marco', 'last' => 'Brenner'],
            ['first' => 'Georg', 'last' => 'Zimmermann'],
            ['first' => 'Felix', 'last' => 'Engelhardt'],
            ['first' => 'Nico', 'last' => 'Denz'],
            ['first' => 'Maximilian', 'last' => 'Schachmann'],
            // New Zealand
            ['first' => 'Ben', 'last' => 'Oliver'],
            ['first' => 'Corbin', 'last' => 'Strong'],
            ['first' => 'Finn', 'last' => 'Fisher-Black'],
            ['first' => 'George', 'last' => 'Bennett'],
            ['first' => 'Laurence', 'last' => 'Pithie'],
            // Portugal
            ['first' => 'Afonso', 'last' => 'Eulalio'],
            ['first' => 'Ivo', 'last' => 'Oliveira'],
            ['first' => 'Tiago', 'last' => 'Antunes'],
            ['first' => 'Nelson', 'last' => 'Oliveira'],
            ['first' => 'Antonio', 'last' => 'Morgado'],
            // Eritrea
            ['first' => 'Amanuel', 'last' => 'Ghebreigzabhier'],
            ['first' => 'Biniam', 'last' => 'Girmay'],
            ['first' => 'Merhawi', 'last' => 'Kudus'],
            ['first' => 'Henok', 'last' => 'Mulubrhan'],
            ['first' => 'Natnael', 'last' => 'Tesfatsion'],
            // Ecuador
            ['first' => 'Richard', 'last' => 'Carapaz'],
            ['first' => 'Jhonatan', 'last' => 'Narvaez'],
            ['first' => 'Jefferson Alveiro', 'last' => 'Cepeda'],
            ['first' => 'Jefferson Alexander', 'last' => 'Cepeda'],
            // Ireland
            ['first' => 'Ben', 'last' => 'Healy'],
            ['first' => 'Darren', 'last' => 'Rafferty'],
            ['first' => 'Jamie', 'last' => 'Meehan'],
            ['first' => 'Ryan', 'last' => 'Mullen'],
            // Poland
            ['first' => 'Michal', 'last' => 'Kwiatkowski'],
            ['first' => 'Mateusz', 'last' => 'Gajdulewicz'],
            ['first' => 'Jakub', 'last' => 'Kaczmarek'],
            ['first' => 'Piotr', 'last' => 'Pekala'],
            // Latvia
            ['first' => 'Kristians', 'last' => 'Belohvosciks'],
            ['first' => 'Emils', 'last' => 'Liepins'],
            ['first' => 'Martins', 'last' => 'Pluto'],
            ['first' => 'Toms', 'last' => 'Skujins'],
            // Austria
            ['first' => 'Felix', 'last' => 'Gall'],
            ['first' => 'Felix', 'last' => 'Grossschartner'],
            ['first' => 'Patrick', 'last' => 'Konrad'],
            // Czech Republic
            ['first' => 'Mathias', 'last' => 'Vacek'],
            ['first' => 'Jakub', 'last' => 'Otruba'],
            ['first' => 'Pavel', 'last' => 'Novak'],
            // Bermuda
            ['first' => 'Kaden', 'last' => 'Hopkins'],
            ['first' => 'Nicholas', 'last' => 'Narraway'],
            // Guatemala
            ['first' => 'Manuel', 'last' => 'Rodas'],
            ['first' => 'Juan Mardoqueo', 'last' => 'Vasquez'],
            // Kazakhstan
            ['first' => 'Anton', 'last' => 'Kuzmin'],
            ['first' => 'Daniil', 'last' => 'Marukhin'],
            // Uruguay
            ['first' => 'Eric Antonio', 'last' => 'Fagundez'],
            ['first' => 'Guillermo Thomas', 'last' => 'Silva'],
            // Venezuela
            ['first' => 'Orluis', 'last' => 'Aular'],
            ['first' => 'Francisco Joel', 'last' => 'Penuela'],
            // Brazil
            ['first' => 'Henrique da Silva', 'last' => 'Avancini'],
            // Costa Rica
            ['first' => 'Luis Daniel', 'last' => 'Oses'],
            // Hungary
            ['first' => 'Attila', 'last' => 'Valter'],
            // Japan
            ['first' => 'Jo', 'last' => 'Hashikawa'],
            // Mauritius
            ['first' => 'Alexandre', 'last' => 'Mayer'],
            // Monaco
            ['first' => 'Victor', 'last' => 'Langelotti'],
            // South Africa
            ['first' => 'Byron', 'last' => 'Munton'],
            // Sweden
            ['first' => 'Jakob', 'last' => 'Soderqvist'],
            // Algeria
            ['first' => 'Oussama Abdellah', 'last' => 'Mimouni'],
            // Belize
            ['first' => 'Derrick', 'last' => 'Chavarria'],
            // Chile
            ['first' => 'Vicente', 'last' => 'Rojas'],
            // China
            ['first' => 'You', 'last' => 'Li'],
            // Cyprus
            ['first' => 'Andreas', 'last' => 'Miltiadis'],
            // Dominica
            ['first' => 'Kohath', 'last' => 'Baron'],
            // Estonia
            ['first' => 'Madis', 'last' => 'Mihkels'],
            // Guinea-Bissau
            ['first' => 'Gil Landim', 'last' => 'Gomes'],
            // Greece
            ['first' => 'Nikiforos', 'last' => 'Arvanitou'],
            // Honduras
            ['first' => 'Fredd', 'last' => 'Matute'],
            // Israel
            ['first' => 'Nadav', 'last' => 'Raisberg'],
            // Saudi Arabia
            ['first' => 'Ali', 'last' => 'Al Shaikhahmed'],
            // Luxembourg
            ['first' => 'Arno', 'last' => 'Wallenborn'],
            // Mongolia
            ['first' => 'Maral-Erdene', 'last' => 'Batmunkh'],
            // Panama
            ['first' => 'Christofer Robin', 'last' => 'Jurado'],
            // Romania
            ['first' => 'Iustin-Ioan', 'last' => 'Vaidian'],
            // Serbia
            ['first' => 'Mihajlo', 'last' => 'Stolic'],
            // Slovakia
            ['first' => 'Martin', 'last' => 'Svrcek'],
            // Thailand
            ['first' => 'Athit', 'last' => 'Poulard'],
            // Ukraine
            ['first' => 'Heorhii', 'last' => 'Antonenko'],
            // Uzbekistan
            ['first' => 'Samandar', 'last' => 'Janikulov'],
            // Rwanda
            ['first' => 'Samuel', 'last' => 'Niyonkuru'],
            ['first' => 'Shemu', 'last' => 'Nsengiyumva'],
            // Jordan
            ['first' => 'Majid', 'last' => 'Abu Harrah'],
            // Cayman Islands
            ['first' => 'Christopher', 'last' => 'Bodden'],
        ];
    }

    private function findParticipantsByNames(string $editionId, array $riders): Collection
    {
        $riderIds = [];

        foreach ($riders as $r) {
            $rider = RiderModel::where('first_name', $r['first'])
                ->where('last_name', $r['last'])
                ->first();

            if ($rider) {
                $riderIds[] = $rider->id;
            }
        }

        return DB::table('competition_participants')
            ->where('edition_id', $editionId)
            ->whereIn('rider_id', $riderIds)
            ->get();
    }
}
