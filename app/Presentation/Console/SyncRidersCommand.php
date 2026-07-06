<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Infrastructure\Persistence\Models\RiderModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncRidersCommand extends Command
{
    protected $signature = 'race:sync-riders';

    protected $description = 'Sync TDF 2026 startlist riders from PCS. Adds missing riders without deleting existing ones.';

    public function handle(): int
    {
        Log::info('race:sync-riders started');

        $riders = $this->getStartlist();

        $added = 0;
        $skipped = 0;

        foreach ($riders as $r) {
            $existing = RiderModel::where('first_name', $r['first'])
                ->where('last_name', $r['last'])
                ->first();

            if ($existing) {
                $skipped++;

                continue;
            }

            RiderModel::create([
                'id' => Str::uuid()->toString(),
                'first_name' => $r['first'],
                'last_name' => $r['last'],
                'country_id' => $r['country'],
            ]);

            $this->info("Added: {$r['first']} {$r['last']} ({$r['country']})");
            $added++;
        }

        $total = $added + $skipped;
        $this->info("Done. Added: {$added}, Skipped: {$skipped}, Total in startlist: {$total}");
        Log::info('race:sync-riders finished', ['added' => $added, 'skipped' => $skipped]);

        return self::SUCCESS;
    }

    private function getStartlist(): array
    {
        return [
            // UAE Team Emirates - XRG
            ['first' => 'Tadej', 'last' => 'Pogačar', 'country' => 'SI'],
            ['first' => 'Isaac', 'last' => 'Del Toro', 'country' => 'MX'],
            ['first' => 'Felix', 'last' => 'Großschartner', 'country' => 'AT'],
            ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
            ['first' => 'Nils', 'last' => 'Politt', 'country' => 'DE'],
            ['first' => 'Florian', 'last' => 'Vermeersch', 'country' => 'BE'],
            ['first' => 'Tim', 'last' => 'Wellens', 'country' => 'BE'],
            ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],

            // Team Visma | Lease a Bike
            ['first' => 'Jonas', 'last' => 'Vingegaard', 'country' => 'DK'],
            ['first' => 'Edoardo', 'last' => 'Affini', 'country' => 'IT'],
            ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
            ['first' => 'Victor', 'last' => 'Campenaerts', 'country' => 'BE'],
            ['first' => 'Per Strand', 'last' => 'Hagenes', 'country' => 'NO'],
            ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
            ['first' => 'Sepp', 'last' => 'Kuss', 'country' => 'US'],
            ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],

            // Red Bull - BORA - hansgrohe
            ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
            ['first' => 'Mattia', 'last' => 'Cattaneo', 'country' => 'IT'],
            ['first' => 'Nico', 'last' => 'Denz', 'country' => 'DE'],
            ['first' => 'Jai', 'last' => 'Hindley', 'country' => 'AU'],
            ['first' => 'Florian', 'last' => 'Lipowitz', 'country' => 'DE'],
            ['first' => 'Jan', 'last' => 'Tratnik', 'country' => 'SI'],
            ['first' => 'Tim', 'last' => 'Van Dijke', 'country' => 'NL'],
            ['first' => 'Maxim', 'last' => 'Van Gils', 'country' => 'BE'],

            // Lidl - Trek
            ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
            ['first' => 'Derek', 'last' => 'Gee', 'country' => 'CA'],
            ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
            ['first' => 'Quinn', 'last' => 'Simmons', 'country' => 'US'],
            ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
            ['first' => 'Toms', 'last' => 'Skujiņš', 'country' => 'LV'],
            ['first' => 'Mathias', 'last' => 'Vacek', 'country' => 'CZ'],
            ['first' => 'Carlos', 'last' => 'Verona', 'country' => 'ES'],

            // EF Education - EasyPost
            ['first' => 'Richard', 'last' => 'Carapaz', 'country' => 'EC'],
            ['first' => 'Kasper', 'last' => 'Asgreen', 'country' => 'DK'],
            ['first' => 'Alex', 'last' => 'Baudin', 'country' => 'FR'],
            ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
            ['first' => 'Sean', 'last' => 'Quinn', 'country' => 'US'],
            ['first' => 'Georg', 'last' => 'Steinhauser', 'country' => 'DE'],
            ['first' => 'Michael', 'last' => 'Valgren', 'country' => 'DK'],
            ['first' => 'Max', 'last' => 'Walker', 'country' => 'GB'],

            // Decathlon CMA CGM Team
            ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
            ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
            ['first' => 'Cees', 'last' => 'Bol', 'country' => 'NL'],
            ['first' => 'Daan', 'last' => 'Hoole', 'country' => 'NL'],
            ['first' => 'Olav', 'last' => 'Kooij', 'country' => 'NL'],
            ['first' => 'Aurélien', 'last' => 'Paret-Peintre', 'country' => 'FR'],
            ['first' => 'Nicolas', 'last' => 'Prodhomme', 'country' => 'FR'],
            ['first' => 'Matthew', 'last' => 'Riccitello', 'country' => 'US'],

            // XDS Astana Team
            ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
            ['first' => 'Davide', 'last' => 'Ballerini', 'country' => 'IT'],
            ['first' => 'Aaron', 'last' => 'Gate', 'country' => 'NZ'],
            ['first' => 'Max', 'last' => 'Kanter', 'country' => 'DE'],
            ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
            ['first' => 'Mike', 'last' => 'Teunissen', 'country' => 'NL'],
            ['first' => 'Simone', 'last' => 'Velasco', 'country' => 'IT'],
            ['first' => 'Nicolas', 'last' => 'Vinokurov', 'country' => 'KZ'],

            // Bahrain - Victorious
            ['first' => 'Lenny', 'last' => 'Martinez', 'country' => 'FR'],
            ['first' => 'Phil', 'last' => 'Bauhaus', 'country' => 'DE'],
            ['first' => 'Damiano', 'last' => 'Caruso', 'country' => 'IT'],
            ['first' => 'Kamil', 'last' => 'Gradek', 'country' => 'PL'],
            ['first' => 'Matej', 'last' => 'Mohorič', 'country' => 'SI'],
            ['first' => 'Robert', 'last' => 'Stannard', 'country' => 'AU'],
            ['first' => 'Antonio', 'last' => 'Tiberi', 'country' => 'IT'],
            ['first' => 'Vlad', 'last' => 'Van Mechelen', 'country' => 'BE'],

            // Netcompany INEOS
            ['first' => 'Egan', 'last' => 'Bernal', 'country' => 'CO'],
            ['first' => 'Thymen', 'last' => 'Arensman', 'country' => 'NL'],
            ['first' => 'Tobias', 'last' => 'Foss', 'country' => 'NO'],
            ['first' => 'Filippo', 'last' => 'Ganna', 'country' => 'IT'],
            ['first' => 'Dorian', 'last' => 'Godon', 'country' => 'FR'],
            ['first' => 'Michał', 'last' => 'Kwiatkowski', 'country' => 'PL'],
            ['first' => 'Joshua', 'last' => 'Tarling', 'country' => 'GB'],
            ['first' => 'Kévin', 'last' => 'Vauquelin', 'country' => 'FR'],

            // Soudal Quick-Step
            ['first' => 'Tim', 'last' => 'Merlier', 'country' => 'BE'],
            ['first' => 'Pascal', 'last' => 'Eenkhoorn', 'country' => 'NL'],
            ['first' => 'Valentin', 'last' => 'Paret-Peintre', 'country' => 'FR'],
            ['first' => 'Jasper', 'last' => 'Stuyven', 'country' => 'BE'],
            ['first' => 'Dylan', 'last' => 'Van Baarle', 'country' => 'NL'],
            ['first' => 'Bert', 'last' => 'Van Lerberghe', 'country' => 'BE'],
            ['first' => 'Ilan', 'last' => 'Van Wilder', 'country' => 'BE'],
            ['first' => 'Louis', 'last' => 'Vervaeke', 'country' => 'BE'],

            // Alpecin - Premier Tech
            ['first' => 'Mathieu', 'last' => 'Van der Poel', 'country' => 'NL'],
            ['first' => 'Ramses', 'last' => 'Debruyne', 'country' => 'BE'],
            ['first' => 'Silvan', 'last' => 'Dillier', 'country' => 'CH'],
            ['first' => 'Tim', 'last' => 'Marsman', 'country' => 'NL'],
            ['first' => 'Jasper', 'last' => 'Philipsen', 'country' => 'BE'],
            ['first' => 'Edward', 'last' => 'Planckaert', 'country' => 'BE'],
            ['first' => 'Jonas', 'last' => 'Rickaert', 'country' => 'BE'],
            ['first' => 'Emiel', 'last' => 'Verstrynge', 'country' => 'BE'],

            // Team Jayco AlUla
            ['first' => 'Ben', 'last' => "O'Connor", 'country' => 'AU'],
            ['first' => 'Pascal', 'last' => 'Ackermann', 'country' => 'DE'],
            ['first' => 'Luke', 'last' => 'Durbridge', 'country' => 'AU'],
            ['first' => 'Felix', 'last' => 'Engelhardt', 'country' => 'DE'],
            ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
            ['first' => 'Kelland', 'last' => "O'Brien", 'country' => 'AU'],
            ['first' => 'Luke', 'last' => 'Plapp', 'country' => 'AU'],
            ['first' => 'Mauro', 'last' => 'Schmid', 'country' => 'CH'],

            // Uno-X Mobility
            ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
            ['first' => 'Jonas', 'last' => 'Abrahamsen', 'country' => 'NO'],
            ['first' => 'Anthon', 'last' => 'Charmig', 'country' => 'DK'],
            ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
            ['first' => 'Anders Halland', 'last' => 'Johannessen', 'country' => 'NO'],
            ['first' => 'Anders', 'last' => 'Skaarseth', 'country' => 'NO'],
            ['first' => 'Torstein', 'last' => 'Træen', 'country' => 'NO'],
            ['first' => 'Søren', 'last' => 'Wærenskjold', 'country' => 'NO'],

            // NSN Cycling Team
            ['first' => 'Biniam', 'last' => 'Girmay', 'country' => 'ER'],
            ['first' => 'Lewis', 'last' => 'Askey', 'country' => 'GB'],
            ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
            ['first' => 'Marco', 'last' => 'Frigo', 'country' => 'IT'],
            ['first' => 'Matis', 'last' => 'Louvel', 'country' => 'FR'],
            ['first' => 'Krists', 'last' => 'Neilands', 'country' => 'LV'],
            ['first' => 'Jake', 'last' => 'Stewart', 'country' => 'GB'],
            ['first' => 'Tom', 'last' => 'Van Asbroeck', 'country' => 'BE'],

            // Movistar Team
            ['first' => 'Cian', 'last' => 'Uijtdebroeks', 'country' => 'BE'],
            ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
            ['first' => 'Jefferson', 'last' => 'Cepeda', 'country' => 'EC'],
            ['first' => 'Raúl', 'last' => 'García Pierna', 'country' => 'ES'],
            ['first' => 'Michel', 'last' => 'Hessmann', 'country' => 'DE'],
            ['first' => 'Nelson', 'last' => 'Oliveira', 'country' => 'PT'],
            ['first' => 'Javier', 'last' => 'Romo', 'country' => 'ES'],
            ['first' => 'Einer', 'last' => 'Rubio', 'country' => 'CO'],

            // Lotto Intermarché
            ['first' => 'Arnaud', 'last' => 'De Lie', 'country' => 'BE'],
            ['first' => 'Huub', 'last' => 'Artz', 'country' => 'NL'],
            ['first' => 'Jenno', 'last' => 'Berckmoes', 'country' => 'BE'],
            ['first' => 'Lars', 'last' => 'Craps', 'country' => 'BE'],
            ['first' => 'Liam', 'last' => 'Slock', 'country' => 'BE'],
            ['first' => 'Lennert', 'last' => 'Van Eetvelt', 'country' => 'BE'],
            ['first' => 'Baptiste', 'last' => 'Veistroffer', 'country' => 'FR'],
            ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],

            // Cofidis
            ['first' => 'Ion', 'last' => 'Izagirre', 'country' => 'ES'],
            ['first' => 'Piet', 'last' => 'Allegaert', 'country' => 'BE'],
            ['first' => 'Alex', 'last' => 'Aranburu', 'country' => 'ES'],
            ['first' => 'Jenthe', 'last' => 'Biermans', 'country' => 'BE'],
            ['first' => 'Milan', 'last' => 'Fretin', 'country' => 'FR'],
            ['first' => 'Alex', 'last' => 'Kirsch', 'country' => 'LU'],
            ['first' => 'Hugo', 'last' => 'Page', 'country' => 'FR'],
            ['first' => 'Benjamin', 'last' => 'Thomas', 'country' => 'FR'],

            // Pinarello Q36.5 Pro Cycling Team
            ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
            ['first' => 'Xabier Mikel', 'last' => 'Azparren', 'country' => 'ES'],
            ['first' => 'Chris', 'last' => 'Harper', 'country' => 'AU'],
            ['first' => 'Quinten', 'last' => 'Hermans', 'country' => 'BE'],
            ['first' => 'Damien', 'last' => 'Howson', 'country' => 'AU'],
            ['first' => 'Xandro', 'last' => 'Meurisse', 'country' => 'BE'],
            ['first' => 'Brent', 'last' => 'Van Moer', 'country' => 'BE'],
            ['first' => 'Fred', 'last' => 'Wright', 'country' => 'GB'],

            // Groupama - FDJ United
            ['first' => 'Romain', 'last' => 'Grégoire', 'country' => 'FR'],
            ['first' => 'Clément', 'last' => 'Berthet', 'country' => 'FR'],
            ['first' => 'Clément', 'last' => 'Braz Afonso', 'country' => 'FR'],
            ['first' => 'Ewen', 'last' => 'Costiou', 'country' => 'FR'],
            ['first' => 'Lorenzo', 'last' => 'Germani', 'country' => 'IT'],
            ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
            ['first' => 'Quentin', 'last' => 'Pacher', 'country' => 'FR'],
            ['first' => 'Clément', 'last' => 'Russo', 'country' => 'FR'],

            // Tudor Pro Cycling Team
            ['first' => 'Julian', 'last' => 'Alaphilippe', 'country' => 'FR'],
            ['first' => 'Arvid', 'last' => 'De Kleijn', 'country' => 'NL'],
            ['first' => 'Marco', 'last' => 'Haller', 'country' => 'AT'],
            ['first' => 'Marc', 'last' => 'Hirschi', 'country' => 'CH'],
            ['first' => 'Rick', 'last' => 'Pluimers', 'country' => 'NL'],
            ['first' => 'Michael', 'last' => 'Storer', 'country' => 'CH'],
            ['first' => 'Matteo', 'last' => 'Trentin', 'country' => 'IT'],
            ['first' => 'Yannis', 'last' => 'Voisard', 'country' => 'CH'],

            // TotalEnergies
            ['first' => 'Jordan', 'last' => 'Jegat', 'country' => 'FR'],
            ['first' => 'Nicolas', 'last' => 'Breuillard', 'country' => 'FR'],
            ['first' => 'Joris', 'last' => 'Delbove', 'country' => 'FR'],
            ['first' => 'Alexandre', 'last' => 'Delettre', 'country' => 'FR'],
            ['first' => 'Thibault', 'last' => 'Guernalec', 'country' => 'FR'],
            ['first' => 'Mathis', 'last' => 'Le Berre', 'country' => 'FR'],
            ['first' => 'Anthony', 'last' => 'Turgis', 'country' => 'FR'],
            ['first' => 'Mattéo', 'last' => 'Vercher', 'country' => 'FR'],

            // Team Picnic PostNL
            ['first' => 'Warren', 'last' => 'Barguil', 'country' => 'FR'],
            ['first' => 'Frits', 'last' => 'Biesterbos', 'country' => 'NL'],
            ['first' => 'Pavel', 'last' => 'Bittner', 'country' => 'CZ'],
            ['first' => 'John', 'last' => 'Degenkolb', 'country' => 'DE'],
            ['first' => 'Robbe', 'last' => 'Dhondt', 'country' => 'BE'],
            ['first' => 'Niklas', 'last' => 'Märkl', 'country' => 'DE'],
            ['first' => 'Julius', 'last' => 'Van den Berg', 'country' => 'NL'],
            ['first' => 'Frank', 'last' => 'Van den Broek', 'country' => 'NL'],

            // Caja Rural - Seguros RGA
            ['first' => 'Fernando', 'last' => 'Gaviria', 'country' => 'CO'],
            ['first' => 'Abel', 'last' => 'Balderstone', 'country' => 'ES'],
            ['first' => 'Sebastian', 'last' => 'Berwick', 'country' => 'AU'],
            ['first' => 'Alex', 'last' => 'Molenaar', 'country' => 'NL'],
            ['first' => 'Joel', 'last' => 'Nicolau', 'country' => 'ES'],
            ['first' => 'Stefano', 'last' => 'Oldani', 'country' => 'IT'],
            ['first' => 'Jakub', 'last' => 'Otruba', 'country' => 'CZ'],
            ['first' => 'José Félix', 'last' => 'Parra', 'country' => 'ES'],
        ];
    }
}
