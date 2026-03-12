@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @include('modules.partials.alerts')
        <div class="row">
            <div class="col-lg-5">
                <div class="ibox">
                    <div class="ibox-title"><h5>{{ $editing ? 'Edit Menu' : 'Input Menu' }}</h5></div>
                    <div class="ibox-content">
                        <form method="POST" action="{{ $editing ? route('menu-items.update', $editing) : route('menu-items.store') }}" id="menuForm">
                            @csrf
                            <div class="form-group"><label>Nama Menu</label><input type="text" name="name" class="form-control" value="{{ old('name', $editing?->name) }}" required></div>
                            <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ old('description', $editing?->description) }}</textarea></div>
                            <div class="checkbox"><label><input type="checkbox" name="is_resto" value="1" @checked(old('is_resto', $editing?->is_resto))> Menu Resto</label></div>
                            <div class="checkbox"><label><input type="checkbox" name="is_catering" value="1" @checked(old('is_catering', $editing?->is_catering))> Menu Catering</label></div>
                            <div class="checkbox"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing?->is_active ?? true))> Aktif</label></div>
                            <hr>
                            <div class="clearfix m-b-sm">
                                <label>Komposisi Bahan</label>
                                <button type="button" class="btn btn-xs btn-primary pull-right" id="addIngredientRow">Tambah Bahan</button>
                            </div>
                            <p class="text-muted small">Bahan baku tidak wajib. Jika dipilih, biaya komposisi akan dihitung otomatis untuk membantu melihat HPP.</p>
                            <div id="ingredientRows"></div>
                            <div class="well well-sm">
                                <div><strong>Estimasi HPP: <span id="hppPreview">Rp 0</span></strong></div>
                            </div>
                            <div class="form-group"><label>Harga Jual</label><input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $editing?->selling_price ?? 0) }}"></div>
                            <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Menu' : 'Simpan Menu' }}</button>
                            @if ($editing)
                                <a href="{{ route('menu-items.index') }}" class="btn btn-white">Batal</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-title"><h5>Daftar Menu</h5></div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped js-datatable">
                            <thead><tr><th>Kode</th><th>Menu</th><th>Kategori</th><th>HPP</th><th>Harga Jual</th><th>Aksi</th></tr></thead>
                            <tbody>
                                @forelse ($menuItems as $menu)
                                    <tr>
                                        <td>{{ $menu->code }}</td>
                                        <td>
                                            <strong>{{ $menu->name }}</strong>
                                            <div class="small text-muted">
                                                @foreach ($menu->ingredients as $composition)
                                                    {{ $composition->ingredient?->name }} ({{ format_qty($composition->quantity) }} {{ $composition->unit }})@if (! $loop->last), @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>{{ $menu->category_label }}</td>
                                        <td>Rp {{ number_format($menu->hpp, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($menu->selling_price, 0, ',', '.') }}</td>
                                        <td>
                                            <a href="{{ route('menu-items.index', ['edit' => $menu->id]) }}" class="btn btn-xs btn-warning">Edit</a>
                                            <form method="POST" action="{{ route('menu-items.destroy', $menu) }}" style="display:inline-block" onsubmit="return confirm('Hapus menu ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada menu.</td></tr>
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
        const ingredientOptions = @json($ingredientsJson);
        const editingIngredients = @json($editingIngredientsJson);
        let ingredientRowIndex = 0;

        function formatRupiah(value) {
            return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
        }

        function getIngredient(ingredientId) {
            return ingredientOptions.find((ingredient) => Number(ingredient.id) === Number(ingredientId)) || null;
        }

        function renderIngredientOptions(selectedId = '') {
            return ingredientOptions.map((ingredient) => `
                <option value="${ingredient.id}" ${Number(selectedId) === Number(ingredient.id) ? 'selected' : ''}>
                    ${ingredient.name} (${ingredient.unit}) - Rp ${Number(ingredient.display_price).toLocaleString('id-ID')} / ${Number(ingredient.price_unit_quantity).toLocaleString('id-ID')} ${ingredient.unit}
                </option>
            `).join('');
        }

        function addIngredientRow(selectedId = '', quantity = '') {
            const wrapper = document.getElementById('ingredientRows');
            const row = document.createElement('div');
            const index = ingredientRowIndex;
            ingredientRowIndex += 1;
            row.className = 'row m-b-sm';
            row.innerHTML = `
                <div class="col-sm-7">
                    <select class="form-control js-select2" name="ingredients[${index}][ingredient_id]">
                        <option value="">Pilih bahan</option>
                        ${renderIngredientOptions(selectedId)}
                    </select>
                </div>
                <div class="col-sm-4">
                    <input type="number" step="0.001" class="form-control" name="ingredients[${index}][quantity]" placeholder="Takaran" value="${quantity}">
                </div>
                <div class="col-sm-1">
                    <button type="button" class="btn btn-danger btn-block remove-row">&times;</button>
                </div>
                <div class="col-sm-12 m-t-xs">
                    <div class="small text-muted ingredient-cost-preview">Biaya bahan: Rp 0</div>
                </div>
            `;
            wrapper.appendChild(row);
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery(row.querySelector('.js-select2')).select2({ width: '100%' });
            }
            const updateRowCost = () => {
                const ingredient = getIngredient(row.querySelector('select').value);
                const qtyValue = Number(row.querySelector('input[name$="[quantity]"]').value || 0);
                const cost = qtyValue * Number(ingredient?.price || 0);
                row.querySelector('.ingredient-cost-preview').textContent = ingredient
                    ? `Biaya bahan: ${formatRupiah(cost)} (${qtyValue || 0} ${ingredient.unit} x ${formatRupiah(ingredient.price)} / ${ingredient.unit})`
                    : 'Biaya bahan: Rp 0';
                recalcHpp();
            };

            row.querySelector('select').addEventListener('change', updateRowCost);
            row.querySelector('input[name$="[quantity]"]').addEventListener('input', updateRowCost);
            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery(row.querySelector('select')).on('change', updateRowCost);
            }
            row.querySelector('.remove-row').addEventListener('click', () => {
                row.remove();
                recalcHpp();
            });
            updateRowCost();
        }

        function recalcHpp() {
            const total = Array.from(document.querySelectorAll('#ingredientRows .row')).reduce((carry, row) => {
                const ingredient = getIngredient(row.querySelector('select').value);
                const qtyValue = Number(row.querySelector('input[name$="[quantity]"]').value || 0);
                return carry + (qtyValue * Number(ingredient?.price || 0));
            }, 0);

            document.getElementById('hppPreview').textContent = formatRupiah(total);
        }

        document.getElementById('addIngredientRow').addEventListener('click', () => addIngredientRow());

        if (editingIngredients.length) {
            editingIngredients.forEach((row) => addIngredientRow(row.ingredient_id, row.quantity));
        } else {
            addIngredientRow();
        }
    </script>
@endsection
