<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiSimulatorController extends Controller
{
    public function sendPost(Request $request)
    {
        // Validar campos requeridos
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'token' => ['nullable', 'string'],
            'body' => ['nullable', 'string'], // JSON en texto
            'headers' => ['nullable', 'array'], // array de pares clave-valor
            'headers.*.key' => ['required_with:headers', 'string'],
            'headers.*.value' => ['required_with:headers', 'string'],
        ]);

        $url = $validated['url'];
        $token = $validated['token'] ?? null;
        $bodyJson = $validated['body'] ?? '{}';
        $customHeaders = $validated['headers'] ?? [];

        // Intentar decodificar el body JSON
        $bodyArray = [];
        try {
            $bodyArray = json_decode($bodyJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return response()->json([
                'error' => 'Body JSON inválido: ' . $e->getMessage()
            ], 422);
        }

        // Construir headers para la petición externa
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        // Añadir headers personalizados
        foreach ($customHeaders as $header) {
            if (!empty($header['key']) && !empty($header['value'])) {
                $headers[$header['key']] = $header['value'];
            }
        }

        try {
            $response = Http::withHeaders($headers)->post($url, $bodyArray);

            // Retornar código, headers y body (JSON o texto)
            $contentType = $response->header('Content-Type', '');

            if (str_contains($contentType, 'application/json')) {
                $responseData = $response->json();
            } else {
                $responseData = $response->body();
            }

            return response()->json([
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $responseData,
            ]);
        } catch (\Exception $e) {
            // En caso de error con la petición externa
            return response()->json([
                'error' => 'Error en la petición externa: ' . $e->getMessage(),
            ], 500);
        }
    }
}
