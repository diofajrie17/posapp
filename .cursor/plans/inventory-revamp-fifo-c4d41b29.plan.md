<!-- c4d41b29-0b15-48aa-8d79-22ae71497780 8eadde5b-e0c7-499c-bae8-7cee591b2b7d -->
# Enable Custom Unit Conversion in Purchases

## Current Problem

Currently, when making a purchase, the system only allows buying in the product's assigned base unit. Cannot use derived units (like Box, Carton) with flexible conversion factors.

## Desired Behavior

**Example Flow:**

1. Admin defines "Box" unit in Units page (just the name, no fixed conversion)
2. Product "Coca Cola" is defined with base unit "pcs"
3. In Purchase page:

   - Select product: Coca Cola (pcs)
   - Select unit: Box
   - Enter custom conversion: 12 (meaning 1 Box = 12 pcs for THIS purchase)
   - Enter quantity: 5 boxes
   - Enter unit cost: Rp 100,000 per box
   - Result: Adds 60 pcs to inventory (5 × 12), with unit cost Rp 8,333.33 per pcs

## Changes Required

### 1. Update purchase_items Table Schema

**File:** Create new migration `2025_10_17_add_unit_fields_to_purchase_items_table.php`

Add columns to store unit information:

```php
Schema::table('purchase_items', function (Blueprint $table) {
    $table->string('unit_name')->nullable()->after('product_id'); // e.g., "Box"
    $table->decimal('unit_conversion', 10, 4)->default(1)->after('unit_name'); // e.g., 12.0000
    $table->decimal('quantity_in_base_unit', 10, 2)->after('quantity'); // converted quantity
});
```

### 2. Update PurchaseItem Model

**File:** `app/Models/PurchaseItem.php`

Add new fields to fillable and casts:

```php
protected $fillable = [
    'purchase_id',
    'product_id',
    'unit_name',         // NEW
    'unit_conversion',   // NEW
    'quantity',
    'quantity_in_base_unit', // NEW
    'unit_cost',
    'subtotal',
];

protected $casts = [
    'quantity' => 'decimal:2',
    'unit_conversion' => 'decimal:4',  // NEW
    'quantity_in_base_unit' => 'decimal:2',  // NEW
    'unit_cost' => 'decimal:2',
    'subtotal' => 'decimal:2',
];
```

### 3. Update PurchaseController

**File:** `app/Http/Controllers/PurchaseController.php`

In `create()` method:

- Pass all units (both base and derived) to the view

In `store()` method:

- Accept `unit_name` and `unit_conversion` for each item
- Calculate `quantity_in_base_unit = quantity × unit_conversion`
- Use `quantity_in_base_unit` for stock updates
- Calculate `unit_cost_in_base = unit_cost ÷ unit_conversion` for FIFO batches
```php
// Example validation in store():
'items.*.unit_name' => 'required|string',
'items.*.unit_conversion' => 'required|numeric|min:0.0001',
'items.*.quantity' => 'required|numeric|min:0.01',

// In the loop:
$quantityInBase = $item['quantity'] * $item['unit_conversion'];
$unitCostInBase = $item['unit_cost'] / $item['unit_conversion'];

// Store in purchase_items
PurchaseItem::create([
    'unit_name' => $item['unit_name'],
    'unit_conversion' => $item['unit_conversion'],
    'quantity' => $item['quantity'],
    'quantity_in_base_unit' => $quantityInBase,
    // ... rest
]);

// Update product stock with base quantity
$product->increment('stock', $quantityInBase);

// Create FIFO batch with base unit cost
InventoryBatch::create([
    'quantity_remaining' => $quantityInBase,
    'unit_cost' => $unitCostInBase,
    // ... rest
]);
```


### 4. Update Purchase Create Form

**File:** `resources/js/Pages/Purchases/Create.vue`

For each item in the purchase:

- Add dropdown to select from ALL units (not just product's unit)
- Add numeric input for "Jumlah per Unit" (conversion factor)
- Show live preview: "1 Box = 12 pcs"
- Auto-calculate total base quantity
```vue
<!-- For each item -->
<select v-model="item.unit_name">
  <option v-for="unit in units" :value="unit.name">
    {{ unit.name }}
  </option>
</select>

<input 
  v-model.number="item.unit_conversion" 
  type="number"
  step="0.0001"
  placeholder="Contoh: 12"
/>

<!-- Preview -->
<div class="text-sm text-gray-600">
  1 {{ item.unit_name }} = {{ item.unit_conversion }} {{ product.unit.name }}
  <br>
  Total: {{ item.quantity * item.unit_conversion }} {{ product.unit.name }}
</div>
```


### 5. Update Purchase Show/Index Pages

**Files:**

- `resources/js/Pages/Purchases/Show.vue`
- `resources/js/Pages/Purchases/Index.vue`

Display unit information for each item:

```
5 Box @ 12 pcs/box (60 pcs total)
Unit Cost: Rp 100,000/box (Rp 8,333/pcs)
```

### 6. Update Units Master Page (Optional)

**File:** `resources/js/Pages/Units/Index.vue`

Add note that units defined here are just names/labels. The actual conversion is specified during purchase.

## Result

After implementation:

- ✅ Units page defines unit NAMES only (Box, Carton, Pack, etc.)
- ✅ Products still use BASE units for inventory
- ✅ Purchases can use ANY unit with CUSTOM conversion per purchase
- ✅ Inventory always stored in base units
- ✅ Purchase history shows unit used and conversion factor
- ✅ FIFO batches store cost in base unit
- ✅ Maximum flexibility for different supplier packaging

### To-dos

- [ ] Create database migrations for purchases, inventory_batches, and FIFO fields
- [ ] Create Purchase, PurchaseItem, and InventoryBatch models with relationships
- [ ] Update Product, SalesItem, and SalesTransaction models with FIFO fields
- [ ] Create PurchaseController with CRUD operations and FIFO batch creation
- [ ] Add stock opname and movement history methods to ProductController
- [ ] Update TransactionController to consume FIFO batches and calculate COGS
- [ ] Create Purchase Index, Create, and Show Vue pages
- [ ] Add stock opname modal and movement history to Products/Index.vue
- [ ] Update Inventory/Movements.vue to show purchase transactions
- [ ] Add purchase routes, stock opname route, and remove old stock routes
- [ ] Add purchase permissions to RolePermissionSeeder and run seeder
- [ ] Remove StockController and old Inventory/Stock*.vue pages
- [ ] Run migrations to update database schema
- [ ] Test complete flow: purchase -> stock increase -> sale with FIFO -> stock decrease