@extends('layouts.app')

@section('title', $pageTitle)

@php
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
@endphp

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight cuti-page">
        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('error') }}
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
                                <p class="m-b-none">
                                    Klik tanggal pada kalender untuk memilih dokter dari tabel dan mengatur status cuti.
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('cuti.dashboard') }}" class="btn btn-white">
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
                        <h5>Kalender Cuti {{ $monthName }}</h5>
                        <div class="ibox-tools">
                            <form method="GET" class="form-inline">
                                <div class="form-group m-r-sm">
                                    <select name="month" class="form-control" onchange="this.form.submit()">
                                        @foreach ($months as $number => $label)
                                            <option value="{{ $number }}" @selected($selectedMonth === $number)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select name="year" class="form-control" onchange="this.form.submit()">
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
                            <span><i class="fa fa-square text-warning"></i> Pengajuan</span>
                            <span><i class="fa fa-square text-danger"></i> Tutup Hfis</span>
                            <span><i class="fa fa-square text-success"></i> Buka Hfis</span>
                            <span><i class="fa fa-square text-primary"></i> Selesai</span>
                        </div>
                        <div id="calendarGrid" class="calendar-grid"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Tabel Dokter</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="input-group m-b-sm">
                            <input type="text" id="doctorSearch" class="form-control" placeholder="Cari dokter...">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                        <div class="table-responsive doctor-table-wrap">
                            <table class="table table-hover table-bordered" id="doctorTable">
                                <thead>
                                    <tr>
                                        <th>Nama Dokter</th>
                                        <th>Jabatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($doctors as $doctor)
                                        <tr>
                                            <td>{{ $doctor['pegawai_nama'] }}</td>
                                            <td>{{ $doctor['jabatan'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">
                                                Data dokter belum tersedia dari API maupun tabel cuti.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="cutiModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-header-cuti">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">
                            <i class="fa fa-calendar-check-o"></i> Atur Cuti Dokter
                        </h4>
                    </div>
                    <form method="POST" action="{{ route('cuti.calendar.store') }}" id="cutiForm">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="tanggal" id="tanggalStatus">
                            <div class="selected-date" id="modalDateLabel"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="section-heading">Pilih Dokter</h4>
                                    <div class="input-group m-b-sm">
                                        <input type="text" id="modalDoctorSearch" class="form-control" placeholder="Cari dokter di modal...">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                    <div class="doctor-picker" id="doctorPicker"></div>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="section-heading">Dokter Dipilih</h4>
                                    <div id="selectedDoctors"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Perubahan
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
        .cuti-page .page-banner {
            background: linear-gradient(135deg, #1ab394, #1c84c6);
            color: #fff;
        }

        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }

        .calendar-weekday,
        .calendar-day {
            border-radius: 8px;
        }

        .calendar-weekday {
            background: #f3f3f4;
            font-weight: 700;
            text-align: center;
            padding: 10px 5px;
        }

        .calendar-day {
            min-height: 110px;
            border: 1px solid #e7eaec;
            padding: 10px;
            position: relative;
            cursor: pointer;
            transition: 0.2s ease;
            background: #fff;
        }

        .calendar-day:hover {
            border-color: #1c84c6;
            box-shadow: 0 10px 20px rgba(28, 132, 198, 0.12);
            transform: translateY(-2px);
        }

        .calendar-day.empty {
            background: #f8f8f8;
            cursor: default;
        }

        .calendar-day.today {
            border: 2px solid #1ab394;
        }

        .calendar-day-number {
            font-size: 18px;
            font-weight: 700;
        }

        .calendar-day-count {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #1c84c6;
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .calendar-status-list {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .calendar-status-bar {
            height: 6px;
            border-radius: 10px;
        }

        .doctor-table-wrap {
            max-height: 540px;
            overflow: auto;
        }

        .modal-header-cuti {
            background: linear-gradient(135deg, #1c84c6, #23c6c8);
            color: #fff;
        }

        .selected-date {
            background: #f3f7fb;
            border-left: 4px solid #1c84c6;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 700;
        }

        .section-heading {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .doctor-picker,
        .selected-doctor-list {
            max-height: 360px;
            overflow: auto;
        }

        .doctor-option,
        .selected-doctor-item {
            border: 1px solid #e7eaec;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
        }

        .doctor-option.disabled {
            opacity: 0.45;
            pointer-events: none;
        }

        .doctor-option-header,
        .selected-doctor-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .doctor-name {
            font-weight: 700;
        }

        .doctor-position {
            color: #676a6c;
            font-size: 12px;
        }

        .selected-doctor-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .empty-state {
            border: 1px dashed #d2d2d2;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            color: #676a6c;
            background: #fafafa;
        }

        @media (max-width: 991px) {
            .calendar-grid {
                gap: 8px;
            }

            .calendar-day {
                min-height: 90px;
            }
        }
    </style>

    <script>
        const calendarData = @json($calendarMap);
        const doctors = @json($doctors);
        const currentMonth = {{ $selectedMonth }};
        const currentYear = {{ $selectedYear }};
        const selectedDateState = {
            date: null,
            doctors: [],
        };

        const dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const statusBarClass = {
            'Pengajuan': 'bg-warning',
            'Tutup Hfis': 'bg-danger',
            'Buka Hfis': 'bg-success',
            'Selesai': 'bg-primary',
        };

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderCalendar() {
            const calendarGrid = document.getElementById('calendarGrid');
            const firstDay = new Date(currentYear, currentMonth - 1, 1);
            const jsStart = firstDay.getDay();
            const startOffset = jsStart === 0 ? 6 : jsStart - 1;
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const today = new Date();
            let html = '';

            dayNames.forEach((day) => {
                html += `<div class="calendar-weekday">${day}</div>`;
            });

            for (let i = 0; i < startOffset; i += 1) {
                html += '<div class="calendar-day empty"></div>';
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const items = calendarData[date] || [];
                const isToday = today.getFullYear() === currentYear
                    && (today.getMonth() + 1) === currentMonth
                    && today.getDate() === day;

                html += `<div class="calendar-day ${isToday ? 'today' : ''}" onclick="openCutiModal('${date}')">`;
                html += `<div class="calendar-day-number">${day}</div>`;

                if (items.length) {
                    html += `<span class="calendar-day-count">${items.length}</span>`;
                    html += '<div class="calendar-status-list">';

                    ['Pengajuan', 'Tutup Hfis', 'Buka Hfis', 'Selesai'].forEach((status) => {
                        const total = items.filter((item) => item.status === status).length;
                        if (total > 0) {
                            html += `<div class="calendar-status-bar ${statusBarClass[status]}" title="${status}: ${total} dokter"></div>`;
                        }
                    });

                    html += '</div>';
                }

                html += '</div>';
            }

            calendarGrid.innerHTML = html;
        }

        function renderDoctorPicker(keyword = '') {
            const container = document.getElementById('doctorPicker');
            const selectedIds = selectedDateState.doctors.map((doctor) => String(doctor.id));
            const normalizedKeyword = keyword.trim().toLowerCase();

            const filtered = doctors.filter((doctor) => {
                const matchKeyword = `${doctor.pegawai_nama} ${doctor.jabatan}`.toLowerCase().includes(normalizedKeyword);
                return matchKeyword;
            });

            if (!filtered.length) {
                container.innerHTML = '<div class="empty-state">Dokter tidak ditemukan.</div>';
                return;
            }

            container.innerHTML = filtered.map((doctor) => {
                const isSelected = selectedIds.includes(String(doctor.pegawai_id));
                const doctorId = escapeHtml(doctor.pegawai_id);
                const doctorName = escapeHtml(doctor.pegawai_nama);
                const doctorPosition = escapeHtml(doctor.jabatan);

                return `
                    <div class="doctor-option ${isSelected ? 'disabled' : ''}">
                        <div class="doctor-option-header">
                            <div>
                                <div class="doctor-name">${doctorName}</div>
                                <div class="doctor-position">${doctorPosition}</div>
                            </div>
                            <button
                                type="button"
                                class="btn btn-xs btn-primary"
                                onclick="selectDoctor('${String(doctor.pegawai_id).replace(/'/g, "\\'")}', '${String(doctor.pegawai_nama).replace(/'/g, "\\'")}', '${String(doctor.jabatan).replace(/'/g, "\\'")}')"
                                ${isSelected ? 'disabled' : ''}
                            >
                                Pilih
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderSelectedDoctors() {
            const container = document.getElementById('selectedDoctors');

            if (!selectedDateState.doctors.length) {
                container.innerHTML = '<div class="empty-state">Belum ada dokter dipilih pada tanggal ini.</div>';
                return;
            }

            container.innerHTML = `
                <div class="selected-doctor-list">
                    ${selectedDateState.doctors.map((doctor) => `
                        <div class="selected-doctor-item">
                            <div class="selected-doctor-header">
                                <div>
                                    <div class="doctor-name">${escapeHtml(doctor.nama)}</div>
                                    <div class="doctor-position">${escapeHtml(doctor.jabatan)}</div>
                                </div>
                                <button type="button" class="btn btn-xs btn-danger" onclick="removeDoctor('${doctor.id}')">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <div class="selected-doctor-actions">
                                <select class="form-control" onchange="updateDoctorStatus('${doctor.id}', this.value)">
                                    <option value="Pengajuan" ${doctor.status === 'Pengajuan' ? 'selected' : ''}>Pengajuan</option>
                                    <option value="Tutup Hfis" ${doctor.status === 'Tutup Hfis' ? 'selected' : ''}>Tutup Hfis</option>
                                    <option value="Buka Hfis" ${doctor.status === 'Buka Hfis' ? 'selected' : ''}>Buka Hfis</option>
                                    <option value="Selesai" ${doctor.status === 'Selesai' ? 'selected' : ''}>Selesai</option>
                                </select>
                            </div>
                            <input type="hidden" name="dokter_dipilih[]" value="${escapeHtml(doctor.id)}_${escapeHtml(doctor.status)}">
                            <input type="hidden" name="pegawai_nama_${escapeHtml(doctor.id)}" value="${escapeHtml(doctor.nama)}">
                            <input type="hidden" name="jabatan_${escapeHtml(doctor.id)}" value="${escapeHtml(doctor.jabatan)}">
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function selectDoctor(id, nama, jabatan) {
            if (selectedDateState.doctors.some((doctor) => String(doctor.id) === String(id))) {
                return;
            }

            selectedDateState.doctors.push({
                id,
                nama,
                jabatan,
                status: 'Pengajuan',
            });

            renderDoctorPicker(document.getElementById('modalDoctorSearch').value);
            renderSelectedDoctors();
        }

        function removeDoctor(id) {
            selectedDateState.doctors = selectedDateState.doctors.filter((doctor) => String(doctor.id) !== String(id));
            renderDoctorPicker(document.getElementById('modalDoctorSearch').value);
            renderSelectedDoctors();
        }

        function updateDoctorStatus(id, status) {
            selectedDateState.doctors = selectedDateState.doctors.map((doctor) => {
                if (String(doctor.id) === String(id)) {
                    return { ...doctor, status };
                }

                return doctor;
            });

            renderSelectedDoctors();
        }

        function openCutiModal(date) {
            selectedDateState.date = date;
            selectedDateState.doctors = (calendarData[date] || []).map((item) => ({
                id: item.pegawai_id,
                nama: item.pegawai_nama,
                jabatan: item.jabatan,
                status: item.status,
            }));

            const [year, month, day] = date.split('-');
            document.getElementById('tanggalStatus').value = date;
            document.getElementById('modalDateLabel').innerText = `${day} ${monthNames[Number(month) - 1]} ${year}`;
            document.getElementById('modalDoctorSearch').value = '';

            renderDoctorPicker();
            renderSelectedDoctors();
            $('#cutiModal').modal('show');
        }

        document.getElementById('doctorSearch').addEventListener('input', function filterDoctorTable() {
            const keyword = this.value.trim().toLowerCase();
            document.querySelectorAll('#doctorTable tbody tr').forEach((row) => {
                row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
            });
        });

        document.getElementById('modalDoctorSearch').addEventListener('input', function filterModalDoctors() {
            renderDoctorPicker(this.value);
        });

        renderCalendar();
    </script>
@endsection
