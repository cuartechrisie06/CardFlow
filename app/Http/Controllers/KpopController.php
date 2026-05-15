<?php

namespace App\Http\Controllers;

use App\Models\KpopGroup;
use App\Models\KpopIdol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KpopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', 'idols');
        $isGroups = $type === 'groups';

        $data = $isGroups
            ? KpopGroup::query()
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(fn (KpopGroup $group) => $group->toArray())
                ->all()
            : KpopIdol::query()
                ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search) {
                    $nested->where('stage_name', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('korean_name', 'like', "%{$search}%")
                        ->orWhere('group_name', 'like', "%{$search}%");
                }))
                ->orderBy('stage_name')
                ->limit(50)
                ->get()
                ->map(fn (KpopIdol $idol) => $idol->toArray())
                ->all();

        return response()->json([
            'ok' => count($data) > 0,
            'type' => $isGroups ? 'groups' : 'idols',
            'data' => $data,
        ]);
    }
}
