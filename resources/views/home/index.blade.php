@extends('layouts.app')

@section('title', $pageTitle)

@php
    $paymentColors = [
        'Belum Dibayar' => 'danger',
        'DP' => 'warning',
        'Lunas' => 'primary',
    ];
@endphp

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content dashboard-hero">
                        <div class="row">
                            <div class="col-sm-8">
                                <h2 class="m-b-xs">{{ $pageTitle }}</h2>
                                <p class="m-b-none">
                                    Ringkasan reservasi catering, customer, menu, bahan baku, dan nilai transaksi bulan {{ $monthLabel }}.
                                </p>
                            </div>
                            <div class="col-sm-4 text-right">
                                <a href="{{ route('catering.calendar') }}" class="btn btn-warning">
                                    <i class="fa fa-calendar"></i> Buka Kalender Catering
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
                        <h5>Status Pembayaran Catering</h5>
                    </div>
                    <div class="ibox-content">
                        @forelse ($paymentSummary as $status => $total)
                            <div class="status-row">
                                <span class="label label-{{ $paymentColors[$status] ?? 'default' }}">{{ $status }}</span>
                                <strong>{{ $total }}</strong>
                            </div>
                        @empty
                            <div class="alert alert-info m-b-none">Belum ada reservasi catering bulan ini.</div>
                        @endforelse
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Nilai Reservasi Bulan Ini</h5>
                    </div>
                    <div class="ibox-content">
                        <h2 class="text-success m-b-xs">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                        <small class="text-muted">
                            Hari tersibuk:
                            <strong>{{ $topDay ? $topDay['tanggal']->translatedFormat('d M Y') : '-' }}</strong>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Reservasi Catering {{ $monthLabel }}</h5>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal Acara</th>
                                    <th>No. Order</th>
                                    <th>Customer</th>
                                    <th>Tamu</th>
                                    <th>Pembayaran</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $order)
                                    <tr>
                                        <td>{{ $order->event_date->translatedFormat('d M Y') }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ number_format($order->guest_count) }}</td>
                                        <td>
                                            <span class="label label-{{ $paymentColors[$order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'dp' ? 'DP' : 'Belum Dibayar')] ?? 'default' }}">
                                                {{ strtoupper($order->payment_status) }}
                                            </span>
                                        </td>
                                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada reservasi catering.</td>
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
            background: linear-gradient(135deg, #0f766e, #1d4ed8);
            color: #fff;
            border-radius: 4px;
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
