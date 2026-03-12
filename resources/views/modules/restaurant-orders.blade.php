@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-title"><h5>Pilih Menu Resto</h5></div>
                    <div class="ibox-content">
                        <ul class="nav nav-tabs" id="menuTabs"></ul>
                        <div class="tab-content" id="menuTabContent"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="ibox">
                    <div class="ibox-title"><h5>Keranjang Order</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ $editingOrder ? route('restaurant-orders.update', $editingOrder) : route('restaurant-orders.store') }}" id="restaurantOrderForm" target="_blank">
                            @csrf
                            <div class="form-group">
                                <label>Meja</label>
                                <select name="dining_table_id" class="form-control js-select2" required>
                                    <option value="">Pilih meja</option>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->id }}" @selected(old('dining_table_id', $editingOrder?->dining_table_id) == $table->id)>{{ $table->name }} ({{ $table->capacity }} kursi)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sumber Order</label>
                                <select name="source" class="form-control js-select2">
                                    <option value="cashier" @selected(old('source', $editingOrder?->source) === 'cashier')>Kasir</option>
                                    <option value="qr" @selected(old('source', $editingOrder?->source) === 'qr')>Scan QR</option>
                                    <option value="takeaway" @selected(old('source', $editingOrder?->source) === 'takeaway')>Takeaway</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $editingOrder?->notes) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Diskon Rupiah</label>
                                        <input type="number" step="0.01" min="0" name="discount" id="orderDiscount" class="form-control" value="{{ old('discount', $editingOrder?->discount ?? 0) }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Uang Diterima</label>
                                        <input type="number" step="0.01" min="0" name="cash_received" id="orderCashReceived" class="form-control" value="{{ old('cash_received', $editingOrder?->cash_received ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                            <div id="orderList" class="m-b-md"></div>
                            <div id="hiddenOrderInputs"></div>
                            <div class="well well-sm">
                                <div><strong>Subtotal: <span id="orderSubtotal">Rp 0</span></strong></div>
                                <div><strong>Total Bayar: <span id="orderTotal">Rp 0</span></strong></div>
                                <div><strong>Kembalian: <span id="orderChange">Rp 0</span></strong></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">{{ $editingOrder ? 'Update Order & Cetak Nota' : 'Simpan Order & Cetak Nota' }}</button>
                            @if ($editingOrder)
                                <form method="POST" action="{{ route('restaurant-orders.destroy', $editingOrder) }}" class="m-t-sm" onsubmit="return confirm('Hapus order resto ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block">Hapus Order Ini</button>
                                </form>
                                <a href="{{ route('restaurant-orders.index') }}" class="btn btn-white btn-block">Batal Edit</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title"><h5>Daftar Order Resto</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>No Order</th><th>Meja</th><th>Status</th><th>Subtotal</th><th>Total</th><th>Aksi</th></tr></thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->table?->name ?: '-' }}</td>
                                        <td>
                                            <span class="label label-{{ $order->status === 'paid' ? 'primary' : ($order->status === 'checkout' ? 'success' : 'warning') }}">
                                                {{ strtoupper($order->status) }}
                                            </span>
                                        </td>
                                        <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($order->payment_status === 'paid')
                                                <a href="{{ route('restaurant-orders.print', $order) }}" target="_blank" class="btn btn-xs btn-primary">Cetak Nota</a>
                                            @else
                                                <span class="text-muted small">Belum dibayar / order lama</span>
                                            @endif
                                            <a href="{{ route('restaurant-orders.index', ['edit' => $order->id]) }}" class="btn btn-xs btn-warning">Edit</a>
                                            <form method="POST" action="{{ route('restaurant-orders.destroy', $order) }}" style="display:inline-block" onsubmit="return confirm('Hapus order resto ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada order resto.</td></tr>
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
    <style>
        .menu-card {
            border: 1px solid #e7eaec;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: .15s ease;
            min-height: 110px;
        }

        .menu-card:hover {
            border-color: #1c84c6;
            box-shadow: 0 8px 18px rgba(28, 132, 198, .12);
            transform: translateY(-1px);
        }

        .order-row {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 10px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f3f4;
        }

        .qty-box {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 50%;
            background: #1c84c6;
            color: #fff;
        }
    </style>

    <script>
        const restaurantMenuItems = @json($menuItemsJson);
        const editingOrder = @json($editingOrderJson);
        const orderState = {};

        function getTabKey(item) {
            const first = (item.name || '').charAt(0).toUpperCase();
            if (first <= 'F') return 'A-F';
            if (first <= 'L') return 'G-L';
            if (first <= 'R') return 'M-R';
            return 'S-Z';
        }

        function groupedMenuItems() {
            const groups = {'A-F': [], 'G-L': [], 'M-R': [], 'S-Z': []};
            restaurantMenuItems.forEach((item) => groups[getTabKey(item)].push(item));
            return groups;
        }

        function formatRupiah(value) {
            return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
        }

        function renderMenuTabs() {
            const groups = groupedMenuItems();
            const tabList = document.getElementById('menuTabs');
            const tabContent = document.getElementById('menuTabContent');
            const keys = Object.keys(groups).filter((key) => groups[key].length > 0);

            tabList.innerHTML = keys.map((key, index) => `
                <li class="${index === 0 ? 'active' : ''}">
                    <a href="#tab-${key.replace(/[^A-Z]/g, '')}" data-toggle="tab">${key}</a>
                </li>
            `).join('');

            tabContent.innerHTML = keys.map((key, index) => `
                <div id="tab-${key.replace(/[^A-Z]/g, '')}" class="tab-pane ${index === 0 ? 'active' : ''}">
                    <div class="row" style="margin-top:12px;">
                        ${groups[key].map((item) => `
                            <div class="col-md-6">
                                <div class="menu-card" onclick="addMenuToOrder(${item.id})">
                                    <strong>${item.name}</strong>
                                    <div class="text-muted small m-t-xs">${formatRupiah(item.price)}</div>
                                    <div class="m-t-sm"><span class="label label-primary">Klik untuk tambah</span></div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        }

        function addMenuToOrder(menuId) {
            const item = restaurantMenuItems.find((row) => Number(row.id) === Number(menuId));
            if (!item) return;

            if (!orderState[menuId]) {
                orderState[menuId] = { ...item, qty: 0 };
            }

            orderState[menuId].qty += 1;
            renderOrderList();
        }

        function updateQty(menuId, delta) {
            if (!orderState[menuId]) return;

            orderState[menuId].qty += delta;

            if (orderState[menuId].qty <= 0) {
                delete orderState[menuId];
            }

            renderOrderList();
        }

        function renderOrderList() {
            const list = document.getElementById('orderList');
            const hiddenInputs = document.getElementById('hiddenOrderInputs');
            const items = Object.values(orderState);

            if (!items.length) {
                list.innerHTML = '<div class="text-muted">Belum ada menu dipilih.</div>';
                hiddenInputs.innerHTML = '';
                updateOrderPaymentSummary(0);
                return;
            }

            let total = 0;

            list.innerHTML = items.map((item) => {
                const subtotal = Number(item.price) * Number(item.qty);
                total += subtotal;

                return `
                    <div class="order-row">
                        <div>
                            <strong>${item.name}</strong>
                            <div class="small text-muted">${formatRupiah(item.price)}</div>
                        </div>
                        <div class="qty-box">
                            <button type="button" class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                            <strong>${item.qty}</strong>
                            <button type="button" class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                        <div><strong>${formatRupiah(subtotal)}</strong></div>
                        <div><button type="button" class="btn btn-xs btn-danger" onclick="updateQty(${item.id}, -${item.qty})">Hapus</button></div>
                    </div>
                `;
            }).join('');

            hiddenInputs.innerHTML = items.map((item, index) => `
                <input type="hidden" name="items[${index}][menu_item_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
            `).join('');

            updateOrderPaymentSummary(total);
        }

        renderMenuTabs();
        if (editingOrder && editingOrder.items) {
            editingOrder.items.forEach((item) => {
                const menu = restaurantMenuItems.find((row) => Number(row.id) === Number(item.menu_item_id));
                if (menu) {
                    orderState[menu.id] = { ...menu, qty: Number(item.qty) };
                }
            });
        }
        renderOrderList();

        function updateOrderPaymentSummary(subtotal) {
            const discount = Number(document.getElementById('orderDiscount').value || 0);
            const cashReceived = Number(document.getElementById('orderCashReceived').value || 0);
            const grandTotal = Math.max(subtotal - discount, 0);
            const change = Math.max(cashReceived - grandTotal, 0);

            document.getElementById('orderSubtotal').textContent = formatRupiah(subtotal);
            document.getElementById('orderTotal').textContent = formatRupiah(grandTotal);
            document.getElementById('orderChange').textContent = formatRupiah(change);
        }

        document.getElementById('orderDiscount').addEventListener('input', () => {
            const subtotal = Object.values(orderState).reduce((carry, item) => carry + (Number(item.price) * Number(item.qty)), 0);
            updateOrderPaymentSummary(subtotal);
        });

        document.getElementById('orderCashReceived').addEventListener('input', () => {
            const subtotal = Object.values(orderState).reduce((carry, item) => carry + (Number(item.price) * Number(item.qty)), 0);
            updateOrderPaymentSummary(subtotal);
        });

        document.getElementById('restaurantOrderForm').addEventListener('submit', () => {
            setTimeout(() => {
                Object.keys(orderState).forEach((key) => delete orderState[key]);
                document.getElementById('restaurantOrderForm').reset();
                if (window.jQuery && window.jQuery.fn.select2) {
                    window.jQuery('#restaurantOrderForm .js-select2').val(null).trigger('change');
                }
                renderOrderList();
                window.location = '{{ route('restaurant-orders.index') }}';
            }, 200);
        });
    </script>
@endsection
