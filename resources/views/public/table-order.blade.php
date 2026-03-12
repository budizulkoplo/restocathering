<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Meja {{ $table->name }}</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background: #f5f7fb; padding: 20px; }
        .card { max-width: 860px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
    </style>
</head>
<body>
    <div class="card">
        <h2>Pesan dari {{ $table->name }}</h2>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('restaurant-orders.public.store', $table->qr_token) }}">
            @csrf
            <div class="form-group">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: tanpa pedas"></textarea>
            </div>
            <hr>
            <div class="clearfix" style="margin-bottom:12px;">
                <label>Menu</label>
                <button type="button" class="btn btn-primary btn-xs pull-right" id="addPublicRow">Tambah Menu</button>
            </div>
            <div id="publicRows"></div>
            <button class="btn btn-success">Kirim Pesanan</button>
        </form>
    </div>
    <script>
        const publicMenuItems = @json($menuItemsJson);
        let publicRowCounter = 0;

        function renderPublicOptions() {
            return publicMenuItems.map((item) => `<option value="${item.id}">${item.name} - Rp ${Number(item.price).toLocaleString('id-ID')}</option>`).join('');
        }

        function addPublicRow() {
            const wrapper = document.getElementById('publicRows');
            const rowIndex = publicRowCounter;
            publicRowCounter += 1;
            const row = document.createElement('div');
            row.className = 'row';
            row.style.marginBottom = '10px';
            row.innerHTML = `
                <div class="col-sm-8">
                    <select class="form-control js-select2" name="items[${rowIndex}][menu_item_id]" required>
                        <option value="">Pilih menu</option>
                        ${renderPublicOptions()}
                    </select>
                </div>
                <div class="col-sm-3">
                    <input type="number" class="form-control" min="1" step="1" value="1" name="items[${rowIndex}][qty]" required>
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

        document.getElementById('addPublicRow').addEventListener('click', addPublicRow);
        addPublicRow();
    </script>
</body>
</html>
