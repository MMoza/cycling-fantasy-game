<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\RiderModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RiderSeeder extends Seeder
{
    public function run(): void
    {
        $riders = [
            // Alpecin - Premier Tech
            ['first' => 'Jasper', 'last' => 'Philipsen', 'country' => 'BE'],
            ['first' => 'Mathieu', 'last' => 'van der Poel', 'country' => 'NL'],
            ['first' => 'Emiel', 'last' => 'Verstrynge', 'country' => 'BE'],
            ['first' => 'Jonas', 'last' => 'Rickaert', 'country' => 'BE'],
            ['first' => 'Tim', 'last' => 'Marsman', 'country' => 'NL'],
            ['first' => 'Ramses', 'last' => 'Debruyne', 'country' => 'BE'],
            ['first' => 'Edward', 'last' => 'Planckaert', 'country' => 'BE'],
            ['first' => 'Silvan', 'last' => 'Dillier', 'country' => 'CH'],

            // Bahrain - Victorious
            ['first' => 'Antonio', 'last' => 'Tiberi', 'country' => 'IT'],
            ['first' => 'Lenny', 'last' => 'Martinez', 'country' => 'FR'],
            ['first' => 'Matej', 'last' => 'Mohorič', 'country' => 'SI'],
            ['first' => 'Phil', 'last' => 'Bauhaus', 'country' => 'DE'],
            ['first' => 'Damiano', 'last' => 'Caruso', 'country' => 'IT'],
            ['first' => 'Kamil', 'last' => 'Gradek', 'country' => 'PL'],
            ['first' => 'Robert', 'last' => 'Stannard', 'country' => 'NZ'],
            ['first' => 'Vlad', 'last' => 'Van Mechelen', 'country' => 'BE'],

            // Decathlon CMA CGM Team
            ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
            ['first' => 'Olav', 'last' => 'Kooij', 'country' => 'NL'],
            ['first' => 'Matthew', 'last' => 'Riccitello', 'country' => 'US'],
            ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
            ['first' => 'Daan', 'last' => 'Hoole', 'country' => 'NL'],
            ['first' => 'Nicolas', 'last' => 'Prodhomme', 'country' => 'FR'],
            ['first' => 'Aurélien', 'last' => 'Paret-Peintre', 'country' => 'FR'],
            ['first' => 'Cees', 'last' => 'Bol', 'country' => 'NL'],

            // EF Education - EasyPost
            ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
            ['first' => 'Kasper', 'last' => 'Asgreen', 'country' => 'DK'],
            ['first' => 'Richard', 'last' => 'Carapaz', 'country' => 'EC'],
            ['first' => 'Alex', 'last' => 'Baudin', 'country' => 'FR'],
            ['first' => 'Sean', 'last' => 'Quinn', 'country' => 'US'],
            ['first' => 'Georg', 'last' => 'Steinhauser', 'country' => 'DE'],
            ['first' => 'Max', 'last' => 'Walker', 'country' => 'GB'],
            ['first' => 'Michael', 'last' => 'Valgren', 'country' => 'DK'],

            // Groupama - FDJ United
            ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
            ['first' => 'Romain', 'last' => 'Grégoire', 'country' => 'FR'],
            ['first' => 'Clément', 'last' => 'Berthet', 'country' => 'FR'],
            ['first' => 'Clément', 'last' => 'Braz Afonso', 'country' => 'FR'],
            ['first' => 'Lorenzo', 'last' => 'Germani', 'country' => 'IT'],
            ['first' => 'Quentin', 'last' => 'Pacher', 'country' => 'FR'],
            ['first' => 'Clément', 'last' => 'Russo', 'country' => 'FR'],
            ['first' => 'Ewen', 'last' => 'Costiou', 'country' => 'FR'],

            // Netcompany INEOS
            ['first' => 'Thymen', 'last' => 'Arensman', 'country' => 'NL'],
            ['first' => 'Egan', 'last' => 'Bernal', 'country' => 'CO'],
            ['first' => 'Tobias', 'last' => 'Foss', 'country' => 'NO'],
            ['first' => 'Filippo', 'last' => 'Ganna', 'country' => 'IT'],
            ['first' => 'Dorian', 'last' => 'Godon', 'country' => 'FR'],
            ['first' => 'Michał', 'last' => 'Kwiatkowski', 'country' => 'PL'],
            ['first' => 'Joshua', 'last' => 'Tarling', 'country' => 'GB'],
            ['first' => 'Kévin', 'last' => 'Vauquelin', 'country' => 'FR'],

            // Lidl - Trek
            ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
            ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
            ['first' => 'Mathias', 'last' => 'Vacek', 'country' => 'CZ'],
            ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
            ['first' => 'Quinn', 'last' => 'Simmons', 'country' => 'US'],
            ['first' => 'Derek', 'last' => 'Gee', 'country' => 'CA'],
            ['first' => 'Carlos', 'last' => 'Verona', 'country' => 'ES'],
            ['first' => 'Toms', 'last' => 'Skujiņš', 'country' => 'LV'],

            // Lotto Intermarché
            ['first' => 'Arnaud', 'last' => 'De Lie', 'country' => 'BE'],
            ['first' => 'Lennert', 'last' => 'Van Eetvelt', 'country' => 'BE'],
            ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],
            ['first' => 'Huub', 'last' => 'Artz', 'country' => 'NL'],
            ['first' => 'Jenno', 'last' => 'Berckmoes', 'country' => 'BE'],
            ['first' => 'Liam', 'last' => 'Slock', 'country' => 'BE'],
            ['first' => 'Lars', 'last' => 'Craps', 'country' => 'BE'],
            ['first' => 'Baptiste', 'last' => 'Veistroffer', 'country' => 'FR'],

            // Movistar Team
            ['first' => 'Cian', 'last' => 'Uijtdebroeks', 'country' => 'BE'],
            ['first' => 'Raúl', 'last' => 'García Pierna', 'country' => 'ES'],
            ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
            ['first' => 'Einer', 'last' => 'Rubio', 'country' => 'CO'],
            ['first' => 'Javier', 'last' => 'Romo', 'country' => 'ES'],
            ['first' => 'Nelson', 'last' => 'Oliveira', 'country' => 'PT'],
            ['first' => 'Jefferson', 'last' => 'Cepeda', 'country' => 'EC'],
            ['first' => 'Michel', 'last' => 'Hessmann', 'country' => 'DE'],

            // NSN Cycling Team
            ['first' => 'Biniam', 'last' => 'Girmay', 'country' => 'ER'],
            ['first' => 'Jake', 'last' => 'Stewart', 'country' => 'GB'],
            ['first' => 'Lewis', 'last' => 'Askey', 'country' => 'GB'],
            ['first' => 'Krists', 'last' => 'Neilands', 'country' => 'LV'],
            ['first' => 'Marco', 'last' => 'Frigo', 'country' => 'IT'],
            ['first' => 'Matis', 'last' => 'Louvel', 'country' => 'FR'],
            ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
            ['first' => 'Tom', 'last' => 'Van Asbroeck', 'country' => 'BE'],

            // Red Bull - BORA - hansgrohe
            ['first' => 'Tim', 'last' => 'Van Dijke', 'country' => 'NL'],
            ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
            ['first' => 'Nico', 'last' => 'Denz', 'country' => 'DE'],
            ['first' => 'Florian', 'last' => 'Lipowitz', 'country' => 'DE'],
            ['first' => 'Mattia', 'last' => 'Cattaneo', 'country' => 'IT'],
            ['first' => 'Jan', 'last' => 'Tratnik', 'country' => 'SI'],
            ['first' => 'Maxim', 'last' => 'Van Gils', 'country' => 'BE'],
            ['first' => 'Jai', 'last' => 'Hindley', 'country' => 'AU'],

            // Soudal Quick-Step
            ['first' => 'Valentin', 'last' => 'Paret-Peintre', 'country' => 'FR'],
            ['first' => 'Tim', 'last' => 'Merlier', 'country' => 'BE'],
            ['first' => 'Pascal', 'last' => 'Eenkhoorn', 'country' => 'NL'],
            ['first' => 'Jasper', 'last' => 'Stuyven', 'country' => 'BE'],
            ['first' => 'Dylan', 'last' => 'Van Baarle', 'country' => 'NL'],
            ['first' => 'Bert', 'last' => 'Van Lerberghe', 'country' => 'BE'],
            ['first' => 'Ilan', 'last' => 'Van Wilder', 'country' => 'BE'],
            ['first' => 'Louis', 'last' => 'Vervaeke', 'country' => 'BE'],

            // Team Jayco AlUla
            ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
            ['first' => 'Luke', 'last' => 'Plapp', 'country' => 'AU'],
            ['first' => 'Pascal', 'last' => 'Ackermann', 'country' => 'DE'],
            ['first' => 'Ben', 'last' => 'O\'Connor', 'country' => 'AU'],
            ['first' => 'Mauro', 'last' => 'Schmid', 'country' => 'CH'],
            ['first' => 'Kelland', 'last' => 'O\'Brien', 'country' => 'AU'],
            ['first' => 'Felix', 'last' => 'Engelhardt', 'country' => 'DE'],
            ['first' => 'Luke', 'last' => 'Durbridge', 'country' => 'AU'],

            // Team Picnic PostNL
            ['first' => 'Pavel', 'last' => 'Bittner', 'country' => 'CZ'],
            ['first' => 'Warren', 'last' => 'Barguil', 'country' => 'FR'],
            ['first' => 'Frank', 'last' => 'Van den Broek', 'country' => 'NL'],
            ['first' => 'Robbe', 'last' => 'Dhondt', 'country' => 'BE'],
            ['first' => 'Julius', 'last' => 'Van den Berg', 'country' => 'NL'],
            ['first' => 'Niklas', 'last' => 'Märkl', 'country' => 'DE'],
            ['first' => 'Frits', 'last' => 'Biesterbos', 'country' => 'NL'],
            ['first' => 'John', 'last' => 'Degenkolb', 'country' => 'DE'],

            // Team Visma | Lease a Bike
            ['first' => 'Victor', 'last' => 'Campenaerts', 'country' => 'BE'],
            ['first' => 'Edoardo', 'last' => 'Affini', 'country' => 'IT'],
            ['first' => 'Per Strand', 'last' => 'Hagenes', 'country' => 'NO'],
            ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
            ['first' => 'Sepp', 'last' => 'Kuss', 'country' => 'US'],
            ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
            ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],
            ['first' => 'Jonas', 'last' => 'Vingegaard', 'country' => 'DK'],

            // UAE Team Emirates - XRG
            ['first' => 'Tadej', 'last' => 'Pogačar', 'country' => 'SI'],
            ['first' => 'Isaac', 'last' => 'Del Toro', 'country' => 'MX'],
            ['first' => 'Tim', 'last' => 'Wellens', 'country' => 'BE'],
            ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
            ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],
            ['first' => 'Florian', 'last' => 'Vermeersch', 'country' => 'BE'],
            ['first' => 'Nils', 'last' => 'Politt', 'country' => 'DE'],
            ['first' => 'Felix', 'last' => 'Großschartner', 'country' => 'AT'],

            // Uno-X Mobility
            ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
            ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
            ['first' => 'Anders', 'last' => 'Skaarseth', 'country' => 'NO'],
            ['first' => 'Søren', 'last' => 'Wærenskjold', 'country' => 'NO'],
            ['first' => 'Anthon', 'last' => 'Charmig', 'country' => 'DK'],
            ['first' => 'Jonas', 'last' => 'Abrahamsen', 'country' => 'NO'],
            ['first' => 'Torstein', 'last' => 'Træen', 'country' => 'NO'],
            ['first' => 'Anders Halland', 'last' => 'Johannessen', 'country' => 'NO'],

            // XDS Astana Team
            ['first' => 'Mike', 'last' => 'Teunissen', 'country' => 'NL'],
            ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
            ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
            ['first' => 'Max', 'last' => 'Kanter', 'country' => 'DE'],
            ['first' => 'Nicolas', 'last' => 'Vinokurov', 'country' => 'KZ'],
            ['first' => 'Davide', 'last' => 'Ballerini', 'country' => 'IT'],
            ['first' => 'Aaron', 'last' => 'Gate', 'country' => 'NZ'],
            ['first' => 'Simone', 'last' => 'Velasco', 'country' => 'IT'],

            // Cofidis
            ['first' => 'Piet', 'last' => 'Allegaert', 'country' => 'BE'],
            ['first' => 'Jenthe', 'last' => 'Biermans', 'country' => 'BE'],
            ['first' => 'Milan', 'last' => 'Fretin', 'country' => 'BE'],
            ['first' => 'Alex', 'last' => 'Kirsch', 'country' => 'LU'],
            ['first' => 'Hugo', 'last' => 'Page', 'country' => 'FR'],
            ['first' => 'Alex', 'last' => 'Aranburu', 'country' => 'ES'],
            ['first' => 'Benjamin', 'last' => 'Thomas', 'country' => 'FR'],
            ['first' => 'Ion', 'last' => 'Izagirre', 'country' => 'ES'],

            // Tudor Pro Cycling Team
            ['first' => 'Julian', 'last' => 'Alaphilippe', 'country' => 'FR'],
            ['first' => 'Matteo', 'last' => 'Trentin', 'country' => 'IT'],
            ['first' => 'Michael', 'last' => 'Storer', 'country' => 'AU'],
            ['first' => 'Rick', 'last' => 'Pluimers', 'country' => 'NL'],
            ['first' => 'Arvid', 'last' => 'De Kleijn', 'country' => 'NL'],
            ['first' => 'Marco', 'last' => 'Haller', 'country' => 'AT'],
            ['first' => 'Marc', 'last' => 'Hirschi', 'country' => 'CH'],
            ['first' => 'Yannis', 'last' => 'Voisard', 'country' => 'CH'],

            // TotalEnergies
            ['first' => 'Jordan', 'last' => 'Jegat', 'country' => 'FR'],
            ['first' => 'Alexandre', 'last' => 'Delettre', 'country' => 'FR'],
            ['first' => 'Anthony', 'last' => 'Turgis', 'country' => 'FR'],
            ['first' => 'Mattéo', 'last' => 'Vercher', 'country' => 'FR'],
            ['first' => 'Mathis', 'last' => 'Le Berre', 'country' => 'FR'],
            ['first' => 'Nicolas', 'last' => 'Breuillard', 'country' => 'FR'],
            ['first' => 'Joris', 'last' => 'Delbove', 'country' => 'FR'],
            ['first' => 'Thibault', 'last' => 'Guernalec', 'country' => 'FR'],

            // Caja Rural - Seguros RGA
            ['first' => 'Alex', 'last' => 'Molenaar', 'country' => 'NL'],
            ['first' => 'Joel', 'last' => 'Nicolau', 'country' => 'ES'],
            ['first' => 'Abel', 'last' => 'Balderstone', 'country' => 'ES'],
            ['first' => 'Sebastian', 'last' => 'Berwick', 'country' => 'AU'],
            ['first' => 'Fernando', 'last' => 'Gaviria', 'country' => 'CO'],
            ['first' => 'Stefano', 'last' => 'Oldani', 'country' => 'IT'],
            ['first' => 'Jakub', 'last' => 'Otruba', 'country' => 'CZ'],
            ['first' => 'José Félix', 'last' => 'Parra', 'country' => 'ES'],

            // Pinarello Q36.5 Pro Cycling Team
            ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
            ['first' => 'Xabier Mikel', 'last' => 'Azparren', 'country' => 'ES'],
            ['first' => 'Chris', 'last' => 'Harper', 'country' => 'AU'],
            ['first' => 'Quinten', 'last' => 'Hermans', 'country' => 'BE'],
            ['first' => 'Damien', 'last' => 'Howson', 'country' => 'AU'],
            ['first' => 'Xandro', 'last' => 'Meurisse', 'country' => 'BE'],
            ['first' => 'Brent', 'last' => 'Van Moer', 'country' => 'BE'],
            ['first' => 'Fred', 'last' => 'Wright', 'country' => 'GB'],
        ];

        $created = 0;
        foreach ($riders as $r) {
            RiderModel::firstOrCreate([
                'first_name' => $r['first'],
                'last_name' => $r['last'],
            ], [
                'id' => Str::uuid()->toString(),
                'country_id' => $r['country'],
            ]);

            $created++;
        }

        $this->command->info("{$created} riders processed (new ones created, existing skipped).");
    }
}
