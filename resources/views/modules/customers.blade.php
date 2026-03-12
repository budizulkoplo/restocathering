@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title"><h5>Input Customer</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ route('customers.store') }}">
                            @csrf
                            <div class="form-group"><label>Nama</label><input type="text" name="name" class="form-control" required></div>
                            <div class="form-group"><label>Telepon</label><input type="text" name="phone" class="form-control"></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                            <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="3"></textarea></div>
                            <div class="form-group"><label>Catatan</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
                            <div class="checkbox"><label><input type="checkbox" name="is_active" value="1" checked> Aktif</label></div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title"><h5>Daftar Customer</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>Kode</th><th>Nama</th><th>Telepon</th><th>Email</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->code }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->phone ?: '-' }}</td>
                                        <td>{{ $customer->email ?: '-' }}</td>
                                        <td><span class="label label-{{ $customer->is_active ? 'primary' : 'default' }}">{{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Belum ada data customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
