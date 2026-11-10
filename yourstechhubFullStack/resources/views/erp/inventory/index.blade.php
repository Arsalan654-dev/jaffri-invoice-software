@extends('erp.layouts.app')

@section('title', 'Inventory')
@section('subtitle', 'Manage your inventory')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h2>Inventory Management</h2>
        <button class="btn btn-primary" data-icon="plus" onclick="openModal('itemModal')"><span>Add Item</span></button>
    </div>
    <div class="filters">
        @include('erp.partials.search-bar', ['action' => route('erp.inventory.index'), 'placeholder' => 'Search inventory...'])
    </div>
    <div class="inventory-grid">
        @forelse($inventoryItems as $item)
            @php $lowStock = $item->quantity <= ($item->min_stock_level ?? 5); @endphp
            <div class="inventory-card">
                <div class="card-image-container">
                    <div class="placeholder-image" data-icon="box"></div>
                    <span class="badge-sku">{{ $item->sku }}</span>
                </div>
                <div class="card-content">
                    <h3>{{ $item->name }}</h3>
                    <p class="description">{{ $item->description ?? 'No description provided.' }}</p>
                    @if($lowStock)
                        <div class="stock-alert">Low stock: only {{ $item->quantity }} unit(s) left</div>
                    @endif
                    <div class="card-stats">
                        <div class="stat">
                            <span class="label">Quantity</span>
                            <span class="value {{ $lowStock ? 'low-stock' : '' }}">{{ $item->quantity }}</span>
                        </div>
                        <div class="stat">
                            <span class="label">Price</span>
                            <span class="value">Rs. {{ number_format($item->selling_price ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="{{ route('erp.inventory.pdf', $item) }}" class="btn-edit-card" title="Download PDF" style="flex:0 0 auto;padding:10px 14px">PDF</a>
                        <button class="btn-edit-card" onclick="editItem({{ $item->id }}, '{{ addslashes($item->sku) }}', '{{ addslashes($item->name) }}', '{{ addslashes($item->category ?? '') }}', '{{ addslashes($item->unit ?? '') }}', {{ $item->quantity ?? 0 }}, {{ $item->min_stock_level ?? 5 }}, {{ $item->purchase_price ?? 0 }}, {{ $item->selling_price ?? 0 }}, '{{ addslashes($item->description ?? '') }}')">
                            Edit
                        </button>
                        <form action="{{ route('erp.inventory.destroy', $item) }}" method="POST" class="d-inline" style="flex:1" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-card">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon" data-icon="box"></div>
                <h3>No inventory items</h3>
                <p>Click "Add Item" to add your first product.</p>
            </div>
        @endforelse
    </div>
    @include('erp.partials.pagination', ['paginator' => $inventoryItems])
</div>

<!-- Add Item Modal -->
<div class="modal-overlay" id="itemModal">
    <div class="modal large">
        <div class="modal-header">
            <h3 id="itemModalTitle">Add New Item</h3>
            <button class="close-btn" onclick="closeModal('itemModal')">&times;</button>
        </div>
        <form id="itemForm" method="POST" action="{{ route('erp.inventory.store') }}">
            @csrf
            <input type="hidden" name="_method" id="itemMethod" value="POST">
            <input type="hidden" id="itemId" name="inventory_id" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" id="itemSku" required placeholder="SKU-001">
                </div>
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" id="itemName" required placeholder="Product name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="itemCategory" placeholder="e.g. Electronics, Clothing">
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="unit" id="itemUnit" placeholder="e.g. pcs, kg, box">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" id="itemQty" value="0">
                </div>
                <div class="form-group">
                    <label>Min Stock Level</label>
                    <input type="number" name="min_stock_level" id="itemMinStock" value="5">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Purchase Price</label>
                    <input type="number" name="purchase_price" id="itemPurchasePrice" value="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Selling Price</label>
                    <input type="number" name="selling_price" id="itemSellingPrice" value="0" step="0.01">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="itemDesc" rows="3" placeholder="Optional description..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('itemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editItem(id, sku, name, category, unit, quantity, minStock, purchasePrice, sellingPrice, description) {
    document.getElementById('itemModalTitle').textContent = 'Edit Item';
    document.getElementById('itemId').value = id;
    document.getElementById('itemSku').value = sku;
    document.getElementById('itemName').value = name;
    document.getElementById('itemCategory').value = category;
    document.getElementById('itemUnit').value = unit;
    document.getElementById('itemQty').value = quantity;
    document.getElementById('itemMinStock').value = minStock;
    document.getElementById('itemPurchasePrice').value = purchasePrice;
    document.getElementById('itemSellingPrice').value = sellingPrice;
    document.getElementById('itemDesc').value = description;
    document.getElementById('itemMethod').value = 'PUT';
    document.getElementById('itemForm').action = '/inventory/' + id;
    openModal('itemModal');
}

document.getElementById('itemModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
    }
});
</script>
@endpush
