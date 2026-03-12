<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto Catering - @yield('title')</title>


    <link rel="stylesheet" href="{!! asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! asset('css/app.css') !!}" />
    <link rel="stylesheet" href="{{ asset('css/plugins/select2/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/plugins/dataTables/datatables.min.css') }}" />

</head>
<body>

  <!-- Wrapper-->
    <div id="wrapper">

        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Page wraper -->
        <div id="page-wrapper" class="gray-bg">

            <!-- Page wrapper -->
            @include('layouts.topnavbar')

            <!-- Main view  -->
            @yield('content')

            <!-- Footer -->
            @include('layouts.footer')

        </div>
        <!-- End page wrapper-->

    </div>
    <!-- End wrapper-->

<script src="{!! asset('js/app.js') !!}" type="text/javascript"></script>
<script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
<script src="{{ asset('js/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
<script>
    (function () {
        if (!window.jQuery) {
            return;
        }

        window.jQuery(function ($) {
            if ($.fn && $.fn.metisMenu) {
                $('#side-menu').metisMenu();
            }

            const searchInput = document.getElementById('menuSearch');
            const searchResultCount = document.getElementById('searchResultCount');

            if (searchInput) {
                let searchTimeout;

                searchInput.addEventListener('keyup', function () {
                    clearTimeout(searchTimeout);

                    searchTimeout = setTimeout(() => {
                        const keyword = this.value.toLowerCase().trim();
                        const menuItems = document.querySelectorAll('#side-menu > li:not(.nav-header):not(.search-bar)');
                        let visibleCount = 0;

                        menuItems.forEach((item) => {
                            const menuText = item.innerText.toLowerCase();

                            if (keyword === '') {
                                item.style.display = '';
                                visibleCount++;

                                const submenu = item.querySelector('.nav-second-level');
                                if (submenu) {
                                    submenu.style.display = 'none';
                                    submenu.classList.remove('in');
                                }
                            } else if (menuText.includes(keyword)) {
                                item.style.display = '';
                                visibleCount++;

                                const submenu = item.querySelector('.nav-second-level');
                                if (submenu) {
                                    submenu.style.display = 'block';
                                    submenu.classList.add('in');
                                }
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        if (searchResultCount) {
                            searchResultCount.innerText = keyword === ''
                                ? ''
                                : (visibleCount > 0 ? visibleCount + ' menu ditemukan' : 'Tidak ada');
                        }
                    }, 100);
                });
            }

            $('#side-menu li a').on('click', function (event) {
                if ($(this).find('.fa.arrow').length === 0) {
                    return;
                }

                const arrow = $(this).find('.fa.arrow');
                const submenu = $(this).parent().find('.nav-second-level');

                if (submenu.length > 0 && !$(this).attr('data-toggle')) {
                    event.preventDefault();

                    if (submenu.hasClass('in')) {
                        submenu.removeClass('in').css('display', 'none');
                        arrow.css('transform', 'rotate(0deg)');
                    } else {
                        submenu.addClass('in').css('display', 'block');
                        arrow.css('transform', 'rotate(90deg)');
                    }
                }
            });

            $('.js-select2').each(function () {
                const $element = $(this);

                if ($element.hasClass('select2-hidden-accessible')) {
                    return;
                }

                $element.select2({
                    width: '100%',
                    dropdownAutoWidth: true,
                });
            });

            $('.js-datatable').each(function () {
                const $table = $(this);

                if ($.fn.DataTable.isDataTable(this)) {
                    return;
                }

                $table.DataTable({
                    pageLength: 10,
                    order: [],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        paginate: {
                            previous: 'Prev',
                            next: 'Next',
                        },
                        zeroRecords: 'Data tidak ditemukan',
                        infoEmpty: 'Belum ada data',
                    },
                });
            });
        });
    }());
</script>

@section('scripts')
@show

</body>
</html>
