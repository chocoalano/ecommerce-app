<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response; // Tambahkan ini jika Anda ingin menggunakan tipe hint Response

class RajaOngkirCurlException extends \RuntimeException {}

class RajaOngkir
{

    /**
     * Helper untuk membuat permintaan HTTP
     *
     * @param string $method Metode HTTP (GET, POST, dll.)
     * @param string $endpoint Endpoint API
     * @param array $data Data yang akan dikirim (untuk GET/POST)
     * @return Response
     */
    protected function request(string $method, string $endpoint, array $data = []): Response
    {
        $url = env('RAJAONGKIR_BASE_URL') . '/' . ltrim($endpoint, '/');

        // Menggunakan Http Facade Laravel
        $client = Http::timeout(env('RAJAONGKIR_TIMEOUT', 15))->withHeaders([
            'Accept' => 'application/json',
            'key' => env('RAJAONGKIR_KEY'),
        ]);
        // Eksekusi permintaan
        $response = $client->{$method}($url, $data);
        return $response;
    }

    /**
     * Mendapatkan daftar provinsi (opsional dengan ID tertentu)
     * GET /destination/province (opsional ?id=)
     *
     * @param int|null $id ID provinsi spesifik
     * @return array
     * @throws RajaOngkirCurlException
     */
    public function provinces(): array
    {
        $endpoint = 'destination/province';
        $response = $this->request('get', $endpoint, []);
        return $response->json('data') ?? [];
    }
    public function city(?int $id = null): array
    {
        $endpoint = 'destination/city/1';
        $response = $this->request('get', $endpoint, []);
        return $response->json('data') ?? [];
    }
}
