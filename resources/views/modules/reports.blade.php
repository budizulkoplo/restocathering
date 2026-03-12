@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-md-3"><div class="ibox"><div class="ibox-content"><h5>Total Customer</h5><h2>{{ $customerCount }}</h2></div></div></div>
            <div class="col-md-3"><div class="ibox"><div class="ibox-content"><h5>Total Bahan</h5><h2>{{ $ingredientCount }}</h2></div></div></div>
            <div class="col-md-3"><div class="ibox"><div class="ibox-content"><h5>Total Menu</h5><h2>{{ $menuCount }}</h2></div></div></div>
            <div class="col-md-3"><div class="ibox"><div class="ibox-content"><h5>Nilai Belanja</h5><h2>Rp {{ number_format($purchaseTotal, 0, ',', '.') }}</h2></div></div></div>
        </div>
        <div class="row">
            <div class="col-md-6"><div class="ibox"><div class="ibox-content"><h5>Penjualan Resto Lunas</h5><h2>Rp {{ number_format($restaurantOrderTotal, 0, ',', '.') }}</h2></div></div></div>
            <div class="col-md-6"><div class="ibox"><div class="ibox-content"><h5>Nilai Persediaan Bahan</h5><h2>Rp {{ number_format($stockValue, 0, ',', '.') }}</h2></div></div></div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title"><h5>Laporan Stok Bahan Baku</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Bahan</th><th>Stok</th><th>Harga Terakhir</th><th>Value</th></tr></thead>
                            <tbody>
                                @forelse ($stockRows as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ format_qty($row->stock) }} {{ $row->unit }}</td>
                                        <td>Rp {{ number_format($row->latest_purchase_price, 0, ',', '.') }} / {{ format_qty($row->price_unit_quantity) }} {{ $row->unit }}</td>
                                        <td>Rp {{ number_format($row->stock * $row->unit_cost, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada data stok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
