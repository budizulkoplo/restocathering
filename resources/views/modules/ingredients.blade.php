@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title"><h5>{{ $editing ? 'Edit Bahan Baku' : 'Input Bahan Baku' }}</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ $editing ? route('ingredients.update', $editing) : route('ingredients.store') }}">
                            @csrf
                            <div class="form-group"><label>Nama</label><input type="text" name="name" class="form-control" value="{{ old('name', $editing?->name) }}" required></div>
                            <div class="form-group"><label>Satuan Dasar</label><input type="text" name="unit" class="form-control" placeholder="gram, pcs, ml" value="{{ old('unit', $editing?->unit) }}" required></div>
                            <div class="form-group"><label>Stok Awal</label><input type="number" step="0.01" name="stock" class="form-control" value="{{ old('stock', $editing?->stock ?? 0) }}"></div>
                            <div class="form-group"><label>Minimum Stok</label><input type="number" step="0.01" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', $editing?->minimum_stock ?? 0) }}"></div>
                            <div class="form-group"><label>Harga</label><input type="number" step="0.01" name="latest_purchase_price" class="form-control" value="{{ old('latest_purchase_price', $editing?->latest_purchase_price ?? 0) }}"></div>
                            <div class="form-group"><label>Harga Berlaku Untuk Qty</label><input type="number" step="0.01" name="price_unit_quantity" class="form-control" value="{{ old('price_unit_quantity', $editing?->price_unit_quantity ?? 1) }}"></div>
                            <p class="text-muted small">Contoh: Tepung satuan `gram`, stok `5000`, minimum `1000`, harga `8000`, qty harga `1000`. Artinya Rp 8 per gram.</p>
                            <div class="checkbox"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing?->is_active ?? true))> Aktif</label></div>
                            <button type="submit" class="btn btn-primary">{{ $editing ? 'Update' : 'Simpan' }}</button>
                            @if ($editing)
                                <a href="{{ route('ingredients.index') }}" class="btn btn-white">Batal</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title"><h5>Daftar Bahan Baku</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>Kode</th><th>Nama</th><th>Stok</th><th>Harga</th><th>Biaya per Satuan</th><th>Aksi</th></tr></thead>
                            <tbody>
                                @forelse ($ingredients as $ingredient)
                                    <tr>
                                        <td>{{ $ingredient->code }}</td>
                                        <td>{{ $ingredient->name }}</td>
                                        <td>{{ format_qty($ingredient->stock) }} {{ $ingredient->unit }}</td>
                                        <td>Rp {{ number_format($ingredient->latest_purchase_price, 0, ',', '.') }} / {{ format_qty($ingredient->price_unit_quantity) }} {{ $ingredient->unit }}</td>
                                        <td>Rp {{ number_format($ingredient->unit_cost, 2, ',', '.') }} / {{ $ingredient->unit }}</td>
                                        <td>
                                            <a href="{{ route('ingredients.index', ['edit' => $ingredient->id]) }}" class="btn btn-xs btn-warning">Edit</a>
                                            <form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" style="display:inline-block" onsubmit="return confirm('Hapus bahan baku ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada bahan baku.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
