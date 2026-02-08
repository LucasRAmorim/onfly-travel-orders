<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $airports = [
            [
                'iata_code' => 'GRU',
                'icao_code' => 'SBGR',
                'name' => 'Guarulhos International',
                'city' => 'Sao Paulo',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'CGH',
                'icao_code' => 'SBSP',
                'name' => 'Congonhas',
                'city' => 'Sao Paulo',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'VCP',
                'icao_code' => 'SBKP',
                'name' => 'Viracopos',
                'city' => 'Campinas',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'GIG',
                'icao_code' => 'SBGL',
                'name' => 'Galeao',
                'city' => 'Rio de Janeiro',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'SDU',
                'icao_code' => 'SBRJ',
                'name' => 'Santos Dumont',
                'city' => 'Rio de Janeiro',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'BSB',
                'icao_code' => 'SBBR',
                'name' => 'Brasilia International',
                'city' => 'Brasilia',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'SSA',
                'icao_code' => 'SBSV',
                'name' => 'Deputado Luis Eduardo Magalhaes',
                'city' => 'Salvador',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'REC',
                'icao_code' => 'SBRF',
                'name' => 'Guararapes',
                'city' => 'Recife',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'FOR',
                'icao_code' => 'SBFZ',
                'name' => 'Pinto Martins',
                'city' => 'Fortaleza',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'POA',
                'icao_code' => 'SBPA',
                'name' => 'Salgado Filho',
                'city' => 'Porto Alegre',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'CNF',
                'icao_code' => 'SBCF',
                'name' => 'Tancredo Neves',
                'city' => 'Belo Horizonte',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'CWB',
                'icao_code' => 'SBCT',
                'name' => 'Afonso Pena',
                'city' => 'Curitiba',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'NAT',
                'icao_code' => 'SBNT',
                'name' => 'Sao Goncalo do Amarante',
                'city' => 'Natal',
                'country' => 'Brasil',
            ],
            [
                'iata_code' => 'MIA',
                'icao_code' => 'KMIA',
                'name' => 'Miami International',
                'city' => 'Miami',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'JFK',
                'icao_code' => 'KJFK',
                'name' => 'John F. Kennedy International',
                'city' => 'New York',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'LGA',
                'icao_code' => 'KLGA',
                'name' => 'LaGuardia',
                'city' => 'New York',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'EWR',
                'icao_code' => 'KEWR',
                'name' => 'Newark Liberty International',
                'city' => 'Newark',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'LAX',
                'icao_code' => 'KLAX',
                'name' => 'Los Angeles International',
                'city' => 'Los Angeles',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'SFO',
                'icao_code' => 'KSFO',
                'name' => 'San Francisco International',
                'city' => 'San Francisco',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'ORD',
                'icao_code' => 'KORD',
                'name' => "O'Hare International",
                'city' => 'Chicago',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'ATL',
                'icao_code' => 'KATL',
                'name' => 'Hartsfield-Jackson Atlanta International',
                'city' => 'Atlanta',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'MCO',
                'icao_code' => 'KMCO',
                'name' => 'Orlando International',
                'city' => 'Orlando',
                'country' => 'Estados Unidos',
            ],
            [
                'iata_code' => 'LHR',
                'icao_code' => 'EGLL',
                'name' => 'Heathrow',
                'city' => 'London',
                'country' => 'Reino Unido',
            ],
            [
                'iata_code' => 'CDG',
                'icao_code' => 'LFPG',
                'name' => 'Charles de Gaulle',
                'city' => 'Paris',
                'country' => 'Franca',
            ],
            [
                'iata_code' => 'AMS',
                'icao_code' => 'EHAM',
                'name' => 'Schiphol',
                'city' => 'Amsterdam',
                'country' => 'Paises Baixos',
            ],
            [
                'iata_code' => 'MAD',
                'icao_code' => 'LEMD',
                'name' => 'Adolfo Suarez Madrid-Barajas',
                'city' => 'Madrid',
                'country' => 'Espanha',
            ],
            [
                'iata_code' => 'LIS',
                'icao_code' => 'LPPT',
                'name' => 'Humberto Delgado',
                'city' => 'Lisboa',
                'country' => 'Portugal',
            ],
            [
                'iata_code' => 'FCO',
                'icao_code' => 'LIRF',
                'name' => 'Fiumicino',
                'city' => 'Roma',
                'country' => 'Italia',
            ],
            [
                'iata_code' => 'DXB',
                'icao_code' => 'OMDB',
                'name' => 'Dubai International',
                'city' => 'Dubai',
                'country' => 'Emirados Arabes Unidos',
            ],
            [
                'iata_code' => 'DOH',
                'icao_code' => 'OTHH',
                'name' => 'Hamad International',
                'city' => 'Doha',
                'country' => 'Catar',
            ],
            [
                'iata_code' => 'HND',
                'icao_code' => 'RJTT',
                'name' => 'Haneda',
                'city' => 'Tokyo',
                'country' => 'Japao',
            ],
            [
                'iata_code' => 'NRT',
                'icao_code' => 'RJAA',
                'name' => 'Narita',
                'city' => 'Tokyo',
                'country' => 'Japao',
            ],
            [
                'iata_code' => 'SYD',
                'icao_code' => 'YSSY',
                'name' => 'Kingsford Smith',
                'city' => 'Sydney',
                'country' => 'Australia',
            ],
            [
                'iata_code' => 'SCL',
                'icao_code' => 'SCEL',
                'name' => 'Arturo Merino Benitez',
                'city' => 'Santiago',
                'country' => 'Chile',
            ],
        ];

        foreach ($airports as $airport) {
            Airport::updateOrCreate(
                [
                    'iata_code' => $airport['iata_code'],
                    'icao_code' => $airport['icao_code'],
                ],
                $airport
            );
        }
    }
}
