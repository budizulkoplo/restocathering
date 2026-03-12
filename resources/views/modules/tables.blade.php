@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title"><h5>Input Meja</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ route('tables.store') }}">
                            @csrf
                            <div class="form-group"><label>Nama Meja</label><input type="text" name="name" class="form-control" required></div>
                            <div class="form-group"><label>Kapasitas</label><input type="number" name="capacity" class="form-control" min="1" value="1" required></div>
                            <div class="form-group"><label>Lokasi</label><input type="text" name="location" class="form-control"></div>
                            <div class="checkbox"><label><input type="checkbox" name="is_active" value="1" checked> Aktif</label></div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title"><h5>Daftar Meja</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>Kode</th><th>Nama</th><th>Kapasitas</th><th>Lokasi</th><th>QR Token</th><th>Cetak</th></tr></thead>
                            <tbody>
                                @forelse ($tables as $table)
                                    <tr>
                                        <td>{{ $table->code }}</td>
                                        <td>{{ $table->name }}</td>
                                        <td>{{ $table->capacity }}</td>
                                        <td>{{ $table->location ?: '-' }}</td>
                                        <td><code>{{ $table->qr_token }}</code></td>
                                        <td><a href="{{ route('tables.print', $table) }}" target="_blank" class="btn btn-xs btn-primary">Cetak</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada meja.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
