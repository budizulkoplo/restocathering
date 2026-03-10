@extends('layouts.app')

@section('title', $pageTitle)

@php
    $statusColors = [
        'Pengajuan' => 'warning',
        'Tutup Hfis' => 'danger',
        'Buka Hfis' => 'success',
        'Selesai' => 'primary',
    ];
@endphp

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content dashboard-hero">
                        <div class="row">
                            <div class="col-sm-8">
                                <h2 class="m-b-xs">{{ $pageTitle }}</h2>
                                <p class="text-muted m-b-none">
                                    Ringkasan statistik dan detail dokter cuti untuk periode {{ $monthLabel }}.
                                </p>
                            </div>
                            <div class="col-sm-4 text-right">
                                <a href="{{ route('cuti.calendar') }}" class="btn btn-primary">
                                    <i class="fa fa-calendar"></i> Buka Kalender Cuti
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach ($summaryCards as $card)
                <div class="col-lg-3 col-md-6">
                    <div class="ibox">
                        <div class="ibox-content">
                            <h5 class="text-muted">{{ $card['label'] }}</h5>
                            <div class="stat-card">
                                <div>
                                    <h2 class="no-margins text-{{ $card['color'] }}">{{ $card['value'] }}</h2>
                                    @if (!empty($card['subtext']))
                                        <small class="text-muted">{{ $card['subtext'] }}</small>
                                    @endif
                                </div>
                                <div class="stat-icon bg-{{ $card['color'] }}">
                                    <i class="fa {{ $card['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Status Bulan Berjalan</h5>
                    </div>
                    <div class="ibox-content">
                        @forelse ($statusSummary as $status => $total)
                            <div class="status-row">
                                <span class="label label-{{ $statusColors[$status] ?? 'default' }}">{{ $status }}</span>
                                <strong>{{ $total }}</strong>
                            </div>
                        @empty
                            <div class="alert alert-info m-b-none">
                                Belum ada data cuti pada bulan ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Rincian Dokter Cuti {{ $monthLabel }}</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Dokter</th>
                                    <th>Jabatan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $record)
                                    <tr>
                                        <td>{{ $record['tanggal']->translatedFormat('d M Y') }}</td>
                                        <td>{{ $record['pegawai_nama'] }}</td>
                                        <td>{{ $record['jabatan'] }}</td>
                                        <td>
                                            <span class="label label-{{ $statusColors[$record['status']] ?? 'default' }}">
                                                {{ $record['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada data cuti dokter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <style>
        .dashboard-hero {
            background: linear-gradient(135deg, #1c84c6, #23c6c8);
            color: #fff;
            border-radius: 4px;
        }

        .dashboard-hero .text-muted {
            color: rgba(255, 255, 255, 0.85);
        }

        .stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f3f4;
        }

        .status-row:last-child {
            border-bottom: 0;
        }
    </style>
@endsection
