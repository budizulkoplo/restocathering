@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title"><h5>Profil Resto</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            <div class="form-group"><label>Nama Resto</label><input type="text" name="name" class="form-control" value="{{ old('name', $profile?->name) }}" required></div>
                            <div class="form-group"><label>Telepon</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $profile?->phone) }}"></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $profile?->email) }}"></div>
                            <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="3">{{ old('address', $profile?->address) }}</textarea></div>
                            <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ old('description', $profile?->description) }}</textarea></div>
                            <button type="submit" class="btn btn-primary">Simpan Setting</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
