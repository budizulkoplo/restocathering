@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content module-hero">
                        <h2 class="m-b-xs">{{ $pageTitle }}</h2>
                        <p class="m-b-none">{{ $module['description'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse ($module['stats'] as $stat)
                <div class="col-md-4">
                    <div class="ibox">
                        <div class="ibox-content">
                            <h5 class="text-muted">{{ $stat['label'] }}</h5>
                            <h2 class="no-margins">{{ $stat['value'] }}</h2>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-lg-12">
                    <div class="alert alert-info">
                        Modul ini sudah disiapkan di navigasi dan struktur databasenya, tetapi CRUD/detail prosesnya belum diimplementasikan pada tahap ini.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <style>
        .module-hero {
            background: linear-gradient(135deg, #1e293b, #0f766e);
            color: #fff;
            border-radius: 4px;
        }
    </style>
@endsection
