<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Faker\Factory as Faker;

class PostRandomDataController extends Controller
{
    public function postRandomData()
    {
        try {
            $url = 'http://127.0.0.1:8000/api/mahasiswa/random-data';

            $totalData = 2500;
            $batchSize = 50;
            $totalBatches = ceil($totalData / $batchSize);

            $faker = Faker::create();
            $successCount = 0;
            $errorCount = 0;

            for ($batch = 1; $batch <= $totalBatches; $batch++) {
                $data = [];

                for ($i = 0; $i < $batchSize; $i++) {
                    $randomData = [
                        'nim' => random_int(1000000000,  9999999999),
                        'nama' => $faker->name,
                        'no_hp' => $faker->phoneNumber,
                        'prodi' => $faker->randomElement(['Teknik Informatika', 'Teknik Sipil', 'Teknik Industri', 'Teknik Elektro', 'Teknik Arsitektur', 'Teknik Mesin']),
                        'agama' => $faker->randomElement(['Islam', 'Protestan', 'Katholik', 'Budha', 'Hindu', 'Konghucu']),
                        'status' => 'Aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $data[] = $randomData;
                }

                Mahasiswa::insert($data);

                Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post($url, $data);

            }
            return response()->json([
                'success' => true,
                'message' => "Proses selesai. Data berhasil dikirim dan disimpan ke database."
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
