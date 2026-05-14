<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KpopController extends Controller
{
    /**
     * Fetch idols or groups from a K-pop API.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        // Using a placeholder K-pop API. Replace with your preferred provider's URL.
        $apiUrl = 'https://kpop-api-example.vercel.app/idols';

        $response = Http::get($apiUrl, [
            'name' => $search,
        ]);

        if ($response->failed()) {
            Log::error('K-pop API Request Failed', ['status' => $response->status(), 'body' => $response->body()]);

            return response()->json(['error' => 'Failed to fetch data'], 500);
        }

        return response()->json($response->json());
    }
}
