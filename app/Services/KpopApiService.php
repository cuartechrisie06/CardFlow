<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class KpopApiService
{
    protected string $dataUrl = 'https://unpkg.com/kpopnet.json/kpopnet.min.json';

    /** @var array<string, string>|null */
    protected ?array $groupNameById = null;

    /**
     * @return array<string, mixed>
     */
    protected function fetchAll(): array
    {
        return Cache::remember('kpopnet_data', now()->addHours(24), function () {
            try {
                $response = Http::timeout(30)->get($this->dataUrl);

                if (! $response->successful()) {
                    return [];
                }

                $json = $response->json();

                return is_array($json) ? $json : [];
            } catch (Throwable) {
                return [];
            }
        });
    }

    public function isDatasetAvailable(): bool
    {
        $data = $this->fetchAll();
        $idols = $data['idols'] ?? $data['profiles'] ?? [];
        $groups = $data['groups'] ?? [];

        if (! is_array($idols) || ! is_array($groups)) {
            return false;
        }

        return count($idols) > 0 || count($groups) > 0;
    }

    /**
     * @return array<string, string>
     */
    protected function groupNameMap(): array
    {
        if ($this->groupNameById !== null) {
            return $this->groupNameById;
        }

        $data = $this->fetchAll();
        $groups = $data['groups'] ?? [];
        if (! is_array($groups)) {
            return $this->groupNameById = [];
        }

        $map = [];
        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }
            $id = $group['id'] ?? null;
            $name = $group['name'] ?? '';
            if ($id !== null && $name !== '') {
                $map[(string) $id] = (string) $name;
            }
        }

        return $this->groupNameById = $map;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getIdols(string $search = ''): array
    {
        $data = $this->fetchAll();
        $idols = $data['idols'] ?? $data['profiles'] ?? [];
        if (! is_array($idols)) {
            return [];
        }

        $needle = strtolower(trim($search));
        if ($needle !== '') {
            $idols = array_filter(
                $idols,
                static function ($idol) use ($needle) {
                    if (! is_array($idol)) {
                        return false;
                    }

                    $haystack = strtolower(implode(' ', array_filter([
                        $idol['name'] ?? '',
                        $idol['name_original'] ?? '',
                        $idol['real_name'] ?? '',
                    ])));

                    return str_contains($haystack, $needle);
                }
            );
        }

        $idols = array_slice(array_values($idols), 0, 50);
        $map = $this->groupNameMap();
        $out = [];
        foreach ($idols as $idol) {
            if (! is_array($idol)) {
                continue;
            }

            $groupDisplay = '—';
            $groupIds = $idol['groups'] ?? [];
            if (is_array($groupIds)) {
                foreach ($groupIds as $groupId) {
                    if ($groupId === null || $groupId === '') {
                        continue;
                    }
                    $key = (string) $groupId;
                    if (isset($map[$key])) {
                        $groupDisplay = $map[$key];
                        break;
                    }
                }
            }

            $out[] = array_merge($idol, [
                'group_display' => $groupDisplay,
            ]);
        }

        return $out;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getGroups(string $search = ''): array
    {
        $data = $this->fetchAll();
        $groups = $data['groups'] ?? [];
        if (! is_array($groups)) {
            return [];
        }

        $needle = strtolower(trim($search));
        if ($needle !== '') {
            $groups = array_filter(
                $groups,
                static function ($group) use ($needle) {
                    if (! is_array($group)) {
                        return false;
                    }

                    $haystack = strtolower(implode(' ', array_filter([
                        $group['name'] ?? '',
                        $group['name_original'] ?? '',
                    ])));

                    return str_contains($haystack, $needle);
                }
            );
        }

        $groups = array_slice(array_values($groups), 0, 50);
        $out = [];
        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }
            $members = $group['members'] ?? [];
            $group['member_count'] = is_array($members) ? count($members) : null;
            $out[] = $group;
        }

        return $out;
    }
}
