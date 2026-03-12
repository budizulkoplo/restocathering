@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-5">
                <div class="ibox">
                    <div class="ibox-title"><h5>Input Belanja Bahan</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ route('purchases.store') }}">
                            @csrf
                            <div class="form-group"><label>Tanggal</label><input type="date" name="purchase_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                            <div class="form-group"><label>Supplier</label><input type="text" name="supplier_name" class="form-control"></div>
                            <div class="form-group"><label>Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                            <hr>
                            <div class="clearfix m-b-sm">
                                <label>Item Belanja</label>
                                <button type="button" class="btn btn-xs btn-primary pull-right" id="addPurchaseRow">Tambah Item</button>
                            </div>
                            <div id="purchaseRows"></div>
                            <button type="submit" class="btn btn-primary">Simpan Belanja</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-title"><h5>Riwayat Belanja</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>Invoice</th><th>Tanggal</th><th>Supplier</th><th>Total</th></tr></thead>
                            <tbody>
                                @forelse ($purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->invoice_number }}</td>
                                        <td>{{ $purchase->purchase_date->format('d-m-Y') }}</td>
                                        <td>{{ $purchase->supplier_name ?: '-' }}</td>
                                        <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada transaksi belanja.</td></tr>
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
        const purchaseIngredients = @json($ingredientsJson);

        function purchaseOptions() {
            return purchaseIngredients.map((ingredient) => `<option value="${ingredient.id}">${ingredient.name} (${ingredient.unit})</option>`).join('');
        }

        function addPurchaseRow() {
            const wrapper = document.getElementById('purchaseRows');
            const row = document.createElement('div');
            row.className = 'row m-b-sm';
            row.innerHTML = `
                <div class="col-sm-6">
                    <select class="form-control js-select2" name="items[][ingredient_id]" required>
                        <option value="">Pilih bahan</option>
                        ${purchaseOptions()}
                    </select>
                </div>
                <div class="col-sm-3">
                    <input type="number" step="0.001" class="form-control" name="items[][qty]" placeholder="Qty" required>
                </div>
                <div class="col-sm-2">
                    <input type="number" step="0.01" class="form-control" name="items[][unit_price]" placeholder="Harga" required>
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

        document.getElementById('addPurchaseRow').addEventListener('click', addPurchaseRow);
        addPurchaseRow();
    </script>
@endsection
