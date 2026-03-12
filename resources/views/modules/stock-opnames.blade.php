@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-5">
                <div class="ibox">
                    <div class="ibox-title"><h5>Input Stok Opname</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ route('stock-opnames.store') }}">
                            @csrf
                            <div class="form-group"><label>Tanggal</label><input type="date" name="opname_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                            <div class="form-group"><label>Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                            <hr>
                            <div class="clearfix m-b-sm">
                                <label>Item Opname</label>
                                <button type="button" class="btn btn-xs btn-primary pull-right" id="addOpnameRow">Tambah Item</button>
                            </div>
                            <div id="opnameRows"></div>
                            <button type="submit" class="btn btn-primary">Simpan Opname</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-title"><h5>Riwayat Stok Opname</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Total Item</th></tr></thead>
                            <tbody>
                                @forelse ($stockOpnames as $opname)
                                    <tr>
                                        <td>{{ $opname->opname_number }}</td>
                                        <td>{{ $opname->opname_date->format('d-m-Y') }}</td>
                                        <td>{{ $opname->items->count() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Belum ada stok opname.</td></tr>
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
    <script>
        const opnameIngredients = @json($ingredientsJson);

        function opnameOptions() {
            return opnameIngredients.map((ingredient) => `<option value="${ingredient.id}">${ingredient.name} (${ingredient.unit})</option>`).join('');
        }

        function addOpnameRow() {
            const wrapper = document.getElementById('opnameRows');
            const row = document.createElement('div');
            row.className = 'row m-b-sm';
            row.innerHTML = `
                <div class="col-sm-6">
                    <select class="form-control js-select2" name="items[][ingredient_id]" required>
                        <option value="">Pilih bahan</option>
                        ${opnameOptions()}
                    </select>
                </div>
                <div class="col-sm-3">
                    <input type="number" step="0.001" class="form-control" name="items[][actual_stock]" placeholder="Stok aktual" required>
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="items[][notes]" placeholder="Catatan">
                </div>
                <div class="col-sm-1">
                    <button type="button" class="btn btn-danger btn-block remove-row">&times;</button>
                </div>
            `;
            wrapper.appendChild(row);
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery(row.querySelector('.js-select2')).select2({ width: '100%' });
            }
            row.querySelector('.remove-row').addEventListener('click', () => row.remove());
        }

        document.getElementById('addOpnameRow').addEventListener('click', addOpnameRow);
        addOpnameRow();
    </script>
@endsection
