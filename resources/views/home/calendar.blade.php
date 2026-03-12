@extends('layouts.app')

@section('title', $pageTitle)

@php
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight catering-page">
        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content page-banner">
                        <div class="row">
                            <div class="col-md-8">
                                <h2 class="m-b-xs">{{ $pageTitle }}</h2>
                                <p class="m-b-none">Klik tanggal untuk input reservasi catering, pilih customer, tentukan menu, DP/lunas, lalu simpan nota reservasi.</p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('dashboard') }}" class="btn btn-danger">
                                    <i class="fa fa-bar-chart"></i> Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Kalender Reservasi {{ $monthName }}</h5>
                        <div class="ibox-tools">
                            <form method="GET" class="form-inline">
                                <div class="form-group m-r-sm">
                                    <select name="month" class="form-control js-select2" onchange="this.form.submit()">
                                        @foreach ($months as $number => $label)
                                            <option value="{{ $number }}" @selected($selectedMonth === $number)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select name="year" class="form-control js-select2" onchange="this.form.submit()">
                                        @for ($year = now()->year - 2; $year <= now()->year + 2; $year++)
                                            <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="calendar-legend">
                            <span><i class="fa fa-square text-info"></i> Reserved</span>
                            <span><i class="fa fa-square text-primary"></i> Confirmed</span>
                            <span><i class="fa fa-square text-success"></i> Completed</span>
                            <span><i class="fa fa-square text-danger"></i> Cancelled</span>
                        </div>
                        <div id="calendarGrid" class="calendar-grid"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Master Customer</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="input-group m-b-sm">
                            <input type="text" id="customerSearch" class="form-control" placeholder="Cari customer...">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                        <div class="table-responsive customer-table-wrap">
                            <table class="table table-hover table-bordered" id="customerTable">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Telepon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($customers as $customer)
                                        <tr>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->phone ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Belum ada customer.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cuti-modal-overlay" id="reservationModal">
            <div class="cuti-modal-dialog">
                <div class="cuti-modal-content">
                    <div class="modal-header modal-header-cuti">
                        <button type="button" class="close cuti-modal-close" id="closeReservationModal">&times;</button>
                        <h4 class="modal-title">
                            <i class="fa fa-calendar-plus-o"></i> Reservasi Catering
                        </h4>
                    </div>
                    <form method="POST" action="{{ route('catering.calendar.store') }}" id="reservationForm" target="_blank" data-create-action="{{ route('catering.calendar.store') }}" data-update-template="{{ url('/kalender-catering') }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="event_date" id="eventDate">
                            <div class="selected-date" id="modalDateLabel"></div>
                            <div class="row">
                                <div class="col-md-5">
                                    <h4 class="section-heading">Data Customer</h4>
                                    <div class="form-group">
                                        <label>Pilih Customer</label>
                                        <select class="form-control js-select2" name="customer_id" id="customerId">
                                            <option value="">Input customer baru</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Nama Customer</label>
                                        <input type="text" class="form-control" name="customer_name" id="customerName">
                                    </div>
                                    <div class="form-group">
                                        <label>Telepon</label>
                                        <input type="text" class="form-control" name="customer_phone" id="customerPhone">
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea class="form-control" name="customer_address" id="customerAddress" rows="3"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Jumlah Tamu</label>
                                                <input type="number" class="form-control" name="guest_count" value="0" min="0">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>DP</label>
                                                <input type="number" class="form-control" name="down_payment" value="0" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="status">
                                                    <option value="reserved">Reserved</option>
                                                    <option value="confirmed">Confirmed</option>
                                                    <option value="completed">Completed</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Pembayaran</label>
                                                <select class="form-control" name="payment_status">
                                                    <option value="unpaid">Belum Dibayar</option>
                                                    <option value="dp">DP</option>
                                                    <option value="paid">Lunas</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Diskon</label>
                                        <input type="number" class="form-control" name="discount" value="0" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Uang Diterima</label>
                                        <input type="number" class="form-control" name="cash_received" id="cashReceived" value="0" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Catatan</label>
                                        <textarea class="form-control" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="flex-head">
                                        <h4 class="section-heading">Menu Catering</h4>
                                        <button type="button" class="btn btn-primary btn-sm" id="addItemRow">
                                            <i class="fa fa-plus"></i> Tambah Menu
                                        </button>
                                    </div>
                                    <div id="itemRows"></div>
                                    <div class="order-summary">
                                        <div class="summary-row"><span>Subtotal</span><strong id="subtotalPreview">Rp 0</strong></div>
                                        <div class="summary-row"><span>Total Bayar</span><strong id="grandTotalPreview">Rp 0</strong></div>
                                        <div class="summary-row"><span>Kembalian</span><strong id="changePreview">Rp 0</strong></div>
                                    </div>
                                    <hr>
                                    <h4 class="section-heading">Reservasi Pada Tanggal Ini</h4>
                                    <div id="dailyOrders"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" id="cancelReservationModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <span id="reservationSubmitLabel">Simpan Reservasi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <style>
        .catering-page .page-banner { background: linear-gradient(135deg, #0f766e, #2563eb); color: #fff; }
        .calendar-legend, .flex-head, .summary-row { display: flex; align-items: center; justify-content: space-between; }
        .calendar-legend { flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; }
        .calendar-weekday, .calendar-day { border-radius: 8px; }
        .calendar-weekday { background: #f3f3f4; font-weight: 700; text-align: center; padding: 10px 5px; }
        .calendar-day { min-height: 155px; border: 1px solid #e7eaec; padding: 10px; cursor: pointer; background: #fff; }
        .calendar-day.empty { background: #f8f8f8; cursor: default; }
        .calendar-day:hover { border-color: #2563eb; box-shadow: 0 10px 20px rgba(37, 99, 235, .12); }
        .calendar-day.today { border: 2px solid #0f766e; }
        .calendar-day-number { font-size: 18px; font-weight: 700; }
        .calendar-day-count { float: right; background: #2563eb; color: #fff; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; }
        .calendar-status-list { margin-top: 15px; display: flex; flex-direction: column; gap: 6px; }
        .calendar-detail-item { background: #f8fafb; border-left: 3px solid #2563eb; border-radius: 6px; padding: 6px 8px; }
        .calendar-detail-name { display: block; font-size: 12px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .calendar-detail-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px; }
        .customer-table-wrap, .selected-doctor-list { max-height: 540px; overflow: auto; }
        .modal-header-cuti { background: linear-gradient(135deg, #2563eb, #0f766e); color: #fff; }
        .cuti-modal-overlay { position: fixed; inset: 0; background: rgba(43, 53, 66, .55); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 24px; }
        .cuti-modal-overlay.active { display: flex; }
        .cuti-modal-dialog { width: min(1200px, 100%); max-height: calc(100vh - 48px); }
        .cuti-modal-content { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, .18); }
        .cuti-modal-content .modal-body { max-height: calc(100vh - 190px); overflow: auto; }
        .cuti-modal-close { color: #fff; opacity: 1; }
        .selected-date { background: #eff6ff; border-left: 4px solid #2563eb; padding: 12px 15px; margin-bottom: 20px; border-radius: 4px; font-weight: 700; }
        .section-heading { margin-top: 0; margin-bottom: 15px; }
        .item-row, .existing-order, .empty-state { border: 1px solid #e7eaec; border-radius: 6px; padding: 12px; margin-bottom: 10px; background: #fff; }
        .item-row-grid { display: grid; grid-template-columns: 1.8fr .8fr auto; gap: 10px; align-items: end; }
        .order-summary { background: #f8fafc; border-radius: 8px; padding: 12px 14px; margin-top: 15px; }
        @media (max-width: 991px) { .calendar-day { min-height: 120px; } .item-row-grid { grid-template-columns: 1fr; } }
    </style>

    <script>
        const calendarData = @json($calendarMap);
        const menuItems = @json($menuItemsJson);
        const customerMap = @json($customersJson);
        const currentMonth = {{ $selectedMonth }};
        const currentYear = {{ $selectedYear }};
        const dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const reservationModal = document.getElementById('reservationModal');
        const editingOrder = @json($editingOrderJson);
        let itemRowCounter = 0;
        const statusClass = {
            reserved: 'label-info',
            confirmed: 'label-primary',
            completed: 'label-success',
            cancelled: 'label-danger',
        };

        function formatRupiah(value) {
            return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
        }

        function renderCalendar() {
            const calendarGrid = document.getElementById('calendarGrid');
            const firstDay = new Date(currentYear, currentMonth - 1, 1);
            const jsStart = firstDay.getDay();
            const startOffset = jsStart === 0 ? 6 : jsStart - 1;
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const today = new Date();
            let html = '';

            dayNames.forEach((day) => html += `<div class="calendar-weekday">${day}</div>`);
            for (let i = 0; i < startOffset; i += 1) html += '<div class="calendar-day empty"></div>';

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const items = calendarData[date] || [];
                const isToday = today.getFullYear() === currentYear && (today.getMonth() + 1) === currentMonth && today.getDate() === day;

                html += `<div class="calendar-day ${isToday ? 'today' : ''}" onclick="openReservationModal('${date}')">`;
                html += `<div class="calendar-day-number">${day}${items.length ? `<span class="calendar-day-count">${items.length}</span>` : ''}</div>`;
                if (items.length) {
                    html += '<div class="calendar-status-list">';
                    items.forEach((item) => {
                        html += `
                            <div class="calendar-detail-item">
                                <span class="calendar-detail-name">${item.customer_name}</span>
                                <div class="calendar-detail-meta">
                                    <span class="label ${statusClass[item.status] || 'label-default'}">${item.status}</span>
                                    <span class="label label-default">${item.payment_status}</span>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
                html += '</div>';
            }
            calendarGrid.innerHTML = html;
        }

        function buildItemOptions() {
            return menuItems.map((item) => `<option value="${item.id}" data-price="${item.price}">${item.name} - ${formatRupiah(item.price)}</option>`).join('');
        }

        function getMenuItem(menuId) {
            return menuItems.find((item) => Number(item.id) === Number(menuId)) || null;
        }

        function syncSelectedCustomer() {
            const customerId = document.getElementById('customerId').value;
            const customer = customerMap[customerId] || null;

            document.getElementById('customerName').value = customer?.name || '';
            document.getElementById('customerPhone').value = customer?.phone || '';
            document.getElementById('customerAddress').value = customer?.address || '';
        }

        function addItemRow(defaultItemId = '') {
            const wrap = document.getElementById('itemRows');
            const rowIndex = itemRowCounter;
            itemRowCounter += 1;
            const row = document.createElement('div');
            row.className = 'item-row';
            row.innerHTML = `
                <div class="item-row-grid">
                    <div class="form-group">
                        <label>Menu</label>
                        <select class="form-control menu-select js-select2" name="items[${rowIndex}][menu_item_id]" required>
                            <option value="">Pilih menu catering</option>
                            ${buildItemOptions()}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Qty</label>
                        <input type="number" class="form-control qty-input" name="items[${rowIndex}][qty]" min="1" step="1" value="1" required>
                    </div>
                    <button type="button" class="btn btn-danger remove-item"><i class="fa fa-trash"></i></button>
                </div>
                <div class="text-muted small item-meta">Harga jual: Rp 0</div>
            `;
            wrap.appendChild(row);
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery(row.querySelector('.js-select2')).select2({ width: '100%' });
            }
            if (defaultItemId) {
                row.querySelector('.menu-select').value = defaultItemId;
                if (window.jQuery && window.jQuery.fn.select2) {
                    window.jQuery(row.querySelector('.menu-select')).val(defaultItemId).trigger('change');
                }
            }
            syncItemMeta(row);
            bindRowEvents(row);
            recalcOrder();
        }

        function bindRowEvents(row) {
            const onMenuChange = () => {
                syncItemMeta(row);
                recalcOrder();
            };
            row.querySelector('.menu-select').addEventListener('change', onMenuChange);
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery(row.querySelector('.menu-select')).on('change', onMenuChange);
            }
            row.querySelector('.qty-input').addEventListener('input', recalcOrder);
            row.querySelector('.remove-item').addEventListener('click', () => {
                row.remove();
                recalcOrder();
            });
        }

        function syncItemMeta(row) {
            const select = row.querySelector('.menu-select');
            const item = getMenuItem(select.value);
            const price = Number(item?.price || 0);
            row.querySelector('.item-meta').textContent = `Harga jual: ${formatRupiah(price)}`;
        }

        function recalcOrder() {
            let subtotal = 0;
            document.querySelectorAll('#itemRows .item-row').forEach((row) => {
                const item = getMenuItem(row.querySelector('.menu-select').value);
                const qty = Number(row.querySelector('.qty-input').value || 0);
                subtotal += Number(item?.price || 0) * qty;
            });

            document.getElementById('subtotalPreview').textContent = formatRupiah(subtotal);
            updatePaymentSummary(subtotal);
        }

        function updatePaymentSummary(subtotal) {
            const discount = Number(document.querySelector('input[name="discount"]').value || 0);
            const cashReceived = Number(document.getElementById('cashReceived').value || 0);
            const paymentStatus = document.querySelector('select[name="payment_status"]').value;
            const downPayment = Number(document.querySelector('input[name="down_payment"]').value || 0);
            const grandTotal = Math.max(subtotal - discount, 0);
            const payableNow = paymentStatus === 'paid' ? grandTotal : downPayment;
            const change = Math.max(cashReceived - payableNow, 0);

            document.getElementById('grandTotalPreview').textContent = formatRupiah(grandTotal);
            document.getElementById('changePreview').textContent = formatRupiah(change);
        }

        function renderDailyOrders(date) {
            const dailyOrders = calendarData[date] || [];
            const container = document.getElementById('dailyOrders');

            if (!dailyOrders.length) {
                container.innerHTML = '<div class="empty-state">Belum ada reservasi pada tanggal ini.</div>';
                return;
            }

            container.innerHTML = dailyOrders.map((order) => `
                <div class="existing-order">
                    <strong>${order.customer_name}</strong>
                    <div class="small text-muted">${order.order_number} | ${order.guest_count} tamu | ${formatRupiah(order.total)}</div>
                    <div class="m-t-xs">
                        <span class="label ${statusClass[order.status] || 'label-default'}">${order.status}</span>
                        <span class="label label-default">${order.payment_status}</span>
                    </div>
                    <div class="m-t-xs">
                        <a href="${new URL(window.location.href.split('?')[0]).pathname}?month=${currentMonth}&year=${currentYear}&edit=${order.id}" class="btn btn-xs btn-warning">Edit</a>
                        <form method="POST" action="${document.getElementById('reservationForm').dataset.updateTemplate}/${order.id}/delete" style="display:inline-block" onsubmit="return confirm('Hapus reservasi ini?')">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            `).join('');
        }

        function openReservationModal(date) {
            const [year, month, day] = date.split('-');
            document.getElementById('eventDate').value = date;
            document.getElementById('modalDateLabel').innerText = `${day} ${monthNames[Number(month) - 1]} ${year}`;
            document.getElementById('reservationForm').reset();
            document.getElementById('reservationForm').action = document.getElementById('reservationForm').dataset.createAction;
            document.getElementById('reservationSubmitLabel').textContent = 'Simpan Reservasi';
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery('#customerId').val('').trigger('change');
            }
            document.getElementById('itemRows').innerHTML = '';
            itemRowCounter = 0;
            addItemRow();
            renderDailyOrders(date);
            reservationModal.classList.add('active');
        }

        function closeReservationModal() {
            reservationModal.classList.remove('active');
        }

        document.getElementById('customerSearch').addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            document.querySelectorAll('#customerTable tbody tr').forEach((row) => {
                row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
            });
        });

        document.getElementById('customerId').addEventListener('change', function () {
            syncSelectedCustomer();
        });
        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery('#customerId').on('change', syncSelectedCustomer);
        }

        document.querySelector('input[name="discount"]').addEventListener('input', () => {
            const subtotal = Array.from(document.querySelectorAll('#itemRows .item-row')).reduce((carry, row) => {
                const item = getMenuItem(row.querySelector('.menu-select').value);
                return carry + (Number(item?.price || 0) * Number(row.querySelector('.qty-input').value || 0));
            }, 0);
            updatePaymentSummary(subtotal);
        });

        document.querySelector('input[name="down_payment"]').addEventListener('input', () => {
            const subtotal = Array.from(document.querySelectorAll('#itemRows .item-row')).reduce((carry, row) => {
                const item = getMenuItem(row.querySelector('.menu-select').value);
                return carry + (Number(item?.price || 0) * Number(row.querySelector('.qty-input').value || 0));
            }, 0);
            updatePaymentSummary(subtotal);
        });

        document.getElementById('cashReceived').addEventListener('input', () => {
            const subtotal = Array.from(document.querySelectorAll('#itemRows .item-row')).reduce((carry, row) => {
                const item = getMenuItem(row.querySelector('.menu-select').value);
                return carry + (Number(item?.price || 0) * Number(row.querySelector('.qty-input').value || 0));
            }, 0);
            updatePaymentSummary(subtotal);
        });

        document.querySelector('select[name="payment_status"]').addEventListener('change', () => {
            const subtotal = Array.from(document.querySelectorAll('#itemRows .item-row')).reduce((carry, row) => {
                const item = getMenuItem(row.querySelector('.menu-select').value);
                return carry + (Number(item?.price || 0) * Number(row.querySelector('.qty-input').value || 0));
            }, 0);
            updatePaymentSummary(subtotal);
        });

        document.getElementById('addItemRow').addEventListener('click', () => addItemRow());
        document.getElementById('closeReservationModal').addEventListener('click', closeReservationModal);
        document.getElementById('cancelReservationModal').addEventListener('click', closeReservationModal);
        reservationModal.addEventListener('click', function (event) {
            if (event.target === reservationModal) closeReservationModal();
        });

        document.getElementById('reservationForm').addEventListener('submit', () => {
            setTimeout(() => {
                document.getElementById('reservationForm').reset();
                if (window.jQuery && window.jQuery.fn.select2) {
                    window.jQuery('#customerId').val('').trigger('change');
                }
                document.getElementById('itemRows').innerHTML = '';
                closeReservationModal();
                window.location = '{{ route('catering.calendar', ['month' => $selectedMonth, 'year' => $selectedYear]) }}';
            }, 200);
        });

        renderCalendar();

        if (editingOrder) {
            openReservationModal(editingOrder.event_date);
            document.getElementById('reservationForm').action = `${document.getElementById('reservationForm').dataset.updateTemplate}/${editingOrder.id}/update`;
            document.getElementById('reservationSubmitLabel').textContent = 'Update Reservasi';
            document.getElementById('customerId').value = editingOrder.customer_id || '';
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery('#customerId').val(editingOrder.customer_id || '').trigger('change');
            } else {
                document.getElementById('customerId').dispatchEvent(new Event('change'));
            }
            document.getElementById('customerName').value = editingOrder.customer_name || '';
            document.getElementById('customerPhone').value = editingOrder.customer_phone || '';
            document.getElementById('customerAddress').value = editingOrder.customer_address || '';
            document.querySelector('input[name="guest_count"]').value = editingOrder.guest_count || 0;
            document.querySelector('select[name="status"]').value = editingOrder.status;
            document.querySelector('select[name="payment_status"]').value = editingOrder.payment_status;
            document.querySelector('input[name="down_payment"]').value = editingOrder.down_payment || 0;
            document.querySelector('input[name="discount"]').value = editingOrder.discount || 0;
            document.getElementById('cashReceived').value = editingOrder.cash_received || 0;
            document.querySelector('textarea[name="notes"]').value = editingOrder.notes || '';
            document.getElementById('itemRows').innerHTML = '';
            itemRowCounter = 0;
            (editingOrder.items || []).forEach((item) => addItemRow(item.menu_item_id));
            document.querySelectorAll('#itemRows .qty-input').forEach((input, index) => {
                input.value = editingOrder.items[index].qty;
            });
            recalcOrder();
        }
    </script>
@endsection
