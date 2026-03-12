@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            @forelse ($items as $item)
                <div class="col-lg-4">
                    <div class="ibox">
                        <div class="ibox-content">
                            <h4 class="m-b-xs">{{ $item->menu_name }}</h4>
                            <p class="m-b-xs">Meja: {{ $item->order?->table?->name ?: '-' }}</p>
                            <p class="m-b-xs">Qty: {{ format_qty($item->qty) }}</p>
                            <p class="m-b-sm">Status: <span class="label label-{{ $item->status === 'served' ? 'primary' : ($item->status === 'ready' ? 'success' : ($item->status === 'preparing' ? 'warning' : 'default')) }}">{{ strtoupper($item->status) }}</span></p>
                            <form method="POST" action="{{ route('kitchen.status', $item) }}">
                                @csrf
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="queued" @selected($item->status === 'queued')>Queued</option>
                                        <option value="preparing" @selected($item->status === 'preparing')>Preparing</option>
                                        <option value="ready" @selected($item->status === 'ready')>Ready</option>
                                        <option value="served" @selected($item->status === 'served')>Served</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-lg-12">
                    <div class="alert alert-info">Belum ada order yang masuk ke display dapur.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
