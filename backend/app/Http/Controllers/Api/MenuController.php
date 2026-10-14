<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => MenuItem::query()
                ->where('is_available', true)
                ->orderBy('id')
                ->get(),
        ]);
    }
}