<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CutiDokterController extends Controller
{
    private const DOKTER_API_URL = 'https://mobile.rspkuboja.com/api/pegawai';
    private const DOKTER_API_KEY = 'RSPKU-PEGAWAI-2026-JOSJIS';

    public function dashboard(): View
    {
        $now = now()->locale('id');
        $month = (int) $now->month;
        $year = (int) $now->year;
        $records = $this->getLeaveRecords($month, $year);

        $statusSummary = collect([
            'Pengajuan' => $records->where('status', 'Pengajuan')->count(),
            'Tutup Hfis' => $records->where('status', 'Tutup Hfis')->count(),
            'Buka Hfis' => $records->where('status', 'Buka Hfis')->count(),
            'Selesai' => $records->where('status', 'Selesai')->count(),
        ]);

        $dailySummary = $records
            ->groupBy(fn (array $item) => $item['tanggal']->format('Y-m-d'))
            ->map(fn (Collection $items, string $date) => [
                'tanggal' => Carbon::parse($date),
                'total' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $topDay = $dailySummary->first();

        return view('home.index', [
            'pageTitle' => 'Dashboard Cuti Dokter',
            'monthLabel' => $now->translatedFormat('F Y'),
            'records' => $records,
            'statusSummary' => $statusSummary,
            'summaryCards' => [
                [
                    'label' => 'Dokter Cuti Bulan Ini',
                    'value' => $records->pluck('pegawai_id')->unique()->count(),
                    'icon' => 'fa-user-md',
                    'color' => 'navy',
                ],
                [
                    'label' => 'Total Entri Cuti',
                    'value' => $records->count(),
                    'icon' => 'fa-calendar-check-o',
                    'color' => 'blue',
                ],
                [
                    'label' => 'Masih Pengajuan',
                    'value' => $statusSummary->get('Pengajuan', 0),
                    'icon' => 'fa-hourglass-half',
                    'color' => 'yellow',
                ],
                [
                    'label' => 'Hari Tersibuk',
                    'value' => $topDay ? $topDay['tanggal']->translatedFormat('d M') : '-',
                    'icon' => 'fa-line-chart',
                    'color' => 'red',
                    'subtext' => $topDay ? $topDay['total'] . ' dokter' : 'Belum ada data',
                ],
            ],
            'topDay' => $topDay,
        ]);
    }

    public function calendar(Request $request): View
    {
        $selectedMonth = max(1, min(12, (int) $request->integer('month', now()->month)));
        $selectedYear = max(2024, min(2100, (int) $request->integer('year', now()->year)));

        $doctors = $this->getDoctors();
        $records = $this->getLeaveRecords($selectedMonth, $selectedYear);

        $calendarMap = $records
            ->groupBy(fn (array $item) => $item['tanggal']->format('Y-m-d'))
            ->map(fn (Collection $items) => $items->values())
            ->toArray();

        return view('home.calendar', [
            'pageTitle' => 'Kalender Cuti Dokter',
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'doctors' => $doctors->values(),
            'calendarMap' => $calendarMap,
            'monthName' => Carbon::create($selectedYear, $selectedMonth, 1)->locale('id')->translatedFormat('F Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'dokter_dipilih' => ['nullable', 'array'],
            'dokter_dipilih.*' => ['string'],
        ]);

        if (! Schema::hasTable('cuti')) {
            return back()->with('error', 'Tabel cuti belum tersedia. Jalankan migration terlebih dahulu.');
        }

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();
        $selected = collect($validated['dokter_dipilih'] ?? [])
            ->map(function (string $item) use ($request) {
                [$pegawaiId, $status] = array_pad(explode('_', $item, 2), 2, 'Pengajuan');

                return [
                    'pegawai_id' => trim($pegawaiId),
                    'pegawai_nama' => (string) $request->input('pegawai_nama_' . $pegawaiId, ''),
                    'jabatan' => (string) $request->input('jabatan_' . $pegawaiId, ''),
                    'status' => trim($status) ?: 'Pengajuan',
                ];
            })
            ->filter(fn (array $item) => $item['pegawai_id'] !== '' && $item['pegawai_nama'] !== '')
            ->values();

        DB::transaction(function () use ($tanggal, $selected, $request) {
            $keepIds = $selected->pluck('pegawai_id')->all();

            Cuti::query()
                ->whereDate('tanggal', $tanggal)
                ->when(
                    ! empty($keepIds),
                    fn ($query) => $query->whereNotIn('pegawai_id', $keepIds),
                    fn ($query) => $query
                )
                ->delete();

            foreach ($selected as $doctor) {
                Cuti::query()->updateOrCreate(
                    [
                        'tanggal' => $tanggal,
                        'pegawai_id' => $doctor['pegawai_id'],
                    ],
                    [
                        'pegawai_nama' => $doctor['pegawai_nama'],
                        'jabatan' => $doctor['jabatan'],
                        'status' => $doctor['status'],
                        'created_by' => (int) ($request->session()->get('id') ?? 1),
                    ]
                );
            }
        });

        return redirect()
            ->route('cuti.calendar', [
                'month' => Carbon::parse($tanggal)->month,
                'year' => Carbon::parse($tanggal)->year,
            ])
            ->with('success', 'Data cuti dokter berhasil disimpan.');
    }

    private function getDoctors(): Collection
    {
        try {
            $response = Http::timeout(20)
                ->withoutVerifying()
                ->get(self::DOKTER_API_URL, [
                    'api_key' => self::DOKTER_API_KEY,
                ]);

            if ($response->successful()) {
                return collect($response->json())
                    ->filter(function (array $pegawai) {
                        $nama = strtolower((string) ($pegawai['pegawai_nama'] ?? ''));
                        $jabatan = strtolower((string) ($pegawai['jabatan'] ?? ''));

                        return str_starts_with($nama, 'dr.') || str_contains($jabatan, 'dokter');
                    })
                    ->map(function (array $pegawai) {
                        return [
                            'pegawai_id' => (string) ($pegawai['pegawai_id'] ?? ''),
                            'pegawai_nama' => (string) ($pegawai['pegawai_nama'] ?? 'Tanpa Nama'),
                            'jabatan' => (string) ($pegawai['jabatan'] ?? 'Dokter'),
                        ];
                    })
                    ->filter(fn (array $pegawai) => $pegawai['pegawai_id'] !== '')
                    ->sortBy('pegawai_nama')
                    ->values();
            }
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengambil data dokter dari API.', [
                'message' => $exception->getMessage(),
            ]);
        }

        if (! Schema::hasTable('cuti')) {
            return collect();
        }

        return Cuti::query()
            ->select('pegawai_id', 'pegawai_nama', 'jabatan')
            ->distinct()
            ->orderBy('pegawai_nama')
            ->get()
            ->map(fn (Cuti $item) => [
                'pegawai_id' => (string) $item->pegawai_id,
                'pegawai_nama' => (string) $item->pegawai_nama,
                'jabatan' => (string) $item->jabatan,
            ]);
    }

    private function getLeaveRecords(int $month, int $year): Collection
    {
        if (! Schema::hasTable('cuti')) {
            return collect();
        }

        return Cuti::query()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal')
            ->orderBy('pegawai_nama')
            ->get()
            ->map(function (Cuti $cuti) {
                return [
                    'id' => $cuti->id,
                    'tanggal' => Carbon::parse($cuti->tanggal),
                    'pegawai_id' => (string) $cuti->pegawai_id,
                    'pegawai_nama' => (string) $cuti->pegawai_nama,
                    'jabatan' => (string) $cuti->jabatan,
                    'status' => (string) $cuti->status,
                ];
            });
    }
}
