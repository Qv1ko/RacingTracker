<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JolpicaService
{
    private const API_URL = 'https://api.jolpi.ca/ergast/f1';

    private const PAGE_SIZE = 100;

    private const REQUEST_DELAY_MS = 300;

    private const STATUS_MAP = [
        'R' => 'DNF',
        'W' => 'DNS',
        'D' => 'DQ',
        'E' => 'EXC',
        'F' => 'DNQ',
        'N' => 'NC',
    ];

    /** @return Collection<int, object> Races ordered by date, each with merged Results */
    public function seasonRaces(int $season): Collection
    {
        $racesByRound = [];
        $offset = 0;
        $total = null;

        do {
            $data = $this->get("{$season}/results.json?limit=".self::PAGE_SIZE."&offset={$offset}")->MRData;
            $total ??= (int) $data->total;

            foreach ($data->RaceTable->Races as $race) {
                if (isset($racesByRound[$race->round])) {
                    $racesByRound[$race->round]->Results = array_merge(
                        $racesByRound[$race->round]->Results ?? [],
                        $race->Results ?? [],
                    );
                } else {
                    $racesByRound[$race->round] = $race;
                }
            }

            $offset += self::PAGE_SIZE;

            ksort($racesByRound);
        } while ($offset < $total);

        return collect($racesByRound)->sortBy('date')->values();
    }

    public function resolvePositionAndStatus(object $result): array
    {
        if (is_numeric($result->positionText)) {
            return [(int) $result->positionText, (string) $result->positionText];
        }

        return [null, $this->resolveStatus($result)];
    }

    private function resolveStatus(object $result): string
    {
        $status = strtolower($result->status ?? '');

        $mapped = match (true) {
            str_contains($status, 'not qualified') || str_contains($status, 'did not qualify') => 'DNQ',
            str_contains($status, 'not start') || str_contains($status, 'withdrew') => 'DNS',
            str_contains($status, 'disqualif') => 'DQ',
            str_contains($status, 'exclu') => 'EXC',
            str_contains($status, 'not classified') => 'NC',
            default => self::STATUS_MAP[$result->positionText] ?? 'DNF',
        };

        return substr($mapped, 0, 10);
    }

    private function get(string $path): object
    {
        usleep(self::REQUEST_DELAY_MS * 1000);

        for ($attempt = 1; ; $attempt++) {
            try {
                $response = Http::timeout(60)->get(self::API_URL.'/'.$path);
            } catch (ConnectionException $e) {
                if ($attempt >= 6) {
                    throw $e;
                }

                sleep(15 * $attempt);

                continue;
            }

            if ($response->status() === 429 && $attempt < 6) {
                Log::warning('Jolpica rate limit hit, waiting 60 seconds...');

                sleep(60);

                continue;
            }

            if ($response->successful()) {
                return json_decode($response->body());
            }

            if ($attempt >= 4) {
                $response->throw();
            }

            sleep(10);
        }
    }
}
