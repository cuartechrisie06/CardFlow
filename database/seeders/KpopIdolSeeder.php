<?php

namespace Database\Seeders;

use App\Models\KpopGroup;
use App\Models\KpopIdol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KpopIdolSeeder extends Seeder
{
    public function run(): void
    {
        KpopGroup::query()->delete();
        KpopIdol::query()->delete();

        $rows = $this->loadRows();

        foreach ($rows as $row) {
            KpopIdol::query()->updateOrCreate(
                [
                    'stage_name' => $row['Stage Name'] ?? $row['stage_name'] ?? '',
                    'group_name' => $row['Group'] ?? $row['group_name'] ?? null,
                ],
                [
                    'full_name' => $this->nullable($row['Full Name'] ?? $row['full_name'] ?? null),
                    'korean_name' => $this->nullable($row['Korean Name'] ?? $row['korean_name'] ?? null),
                    'group_name' => $this->nullable($row['Group'] ?? $row['group_name'] ?? null),
                    'debut_date' => $this->nullableDate($row['Debut'] ?? $row['debut_date'] ?? null),
                    'birth_date' => $this->nullableDate($row['Date of Birth'] ?? $row['birth_date'] ?? null),
                    'company' => $this->nullable($row['Company'] ?? $row['company'] ?? null),
                    'country' => $this->nullable($row['Country'] ?? $row['country'] ?? null),
                    'height' => $this->nullableInt($row['Height'] ?? $row['height'] ?? null),
                    'gender' => $this->nullable($row['Gender'] ?? $row['gender'] ?? null),
                ]
            );
        }

        KpopIdol::query()
            ->select('group_name')
            ->whereNotNull('group_name')
            ->distinct()
            ->chunk(100, function ($groups) {
                foreach ($groups as $item) {
                    $groupName = trim((string) $item->group_name);

                    if ($groupName === '') {
                        continue;
                    }

                    $members = KpopIdol::query()->where('group_name', $groupName)->count();
                    $first = KpopIdol::query()->where('group_name', $groupName)->orderBy('id')->first();

                    KpopGroup::query()->updateOrCreate(
                        ['name' => $groupName],
                        [
                            'debut_date' => $first?->debut_date,
                            'company' => $first?->company,
                            'member_count' => $members,
                            'gender' => $first?->gender,
                        ]
                    );
                }
            });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function loadRows(): array
    {
        $csvPath = database_path('data/kpop_idols.csv');

        if (is_file($csvPath)) {
            return $this->loadCsv($csvPath);
        }

        $fallbackPath = resource_path('data/kpopnet-fallback.json');

        if (! is_file($fallbackPath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($fallbackPath), true);

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded['idols'] ?? [])
            ->map(function (array $idol) use ($decoded) {
                $groupName = null;

                foreach ((array) ($idol['groups'] ?? []) as $groupId) {
                    $group = collect($decoded['groups'] ?? [])->firstWhere('id', $groupId);

                    if (is_array($group) && ! empty($group['name'])) {
                        $groupName = $group['name'];
                        break;
                    }
                }

                return [
                    'Stage Name' => $idol['name'] ?? null,
                    'Full Name' => $idol['real_name'] ?? null,
                    'Korean Name' => $idol['name_original'] ?? null,
                    'Group' => $groupName,
                    'Debut' => $idol['debut_date'] ?? null,
                    'Date of Birth' => $idol['birth_date'] ?? null,
                    'Company' => $idol['company'] ?? null,
                    'Country' => $idol['country'] ?? null,
                    'Height' => $idol['height'] ?? null,
                    'Gender' => $idol['gender'] ?? null,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function loadCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $headers = [];
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($headers === []) {
                $headers = array_map(function ($header) {
                    $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header) ?? (string) $header;

                    return trim($header);
                }, $row);
                continue;
            }

            if (count($row) !== count($headers)) {
                continue;
            }

            $rows[] = array_combine($headers, $row);
        }

        fclose($handle);

        return array_values(array_filter($rows, fn ($row) => is_array($row)));
    }

    protected function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        $value = preg_replace('/[^\d]/', '', (string) $value);

        return $value === '' ? null : (int) $value;
    }

    protected function nullableDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
