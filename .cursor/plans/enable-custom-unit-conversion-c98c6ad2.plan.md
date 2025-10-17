<!-- c98c6ad2-c807-4171-beb6-0f8634bbd385 f203ef37-9228-418c-abd9-54280392ad2c -->
# Enable Custom Unit Conversion in Purchases with Unit Type Filtering

## Current Problem

Currently, when making a purchase, the system only allows buying in the product's assigned base unit. Cannot use derived units (like Box, Carton) with flexible conversion factors. Additionally, there's no unit type filtering, which could allow inappropriate unit mixing (e.g., selecting kg for piece-based products).

## Database Reset (Clean Start)

Since we're making fundamental changes to the units and purchase system, we'll do a clean start by wiping all data except user-related tables.

**Option 1: Fresh Migration (Recommended)**

```bash
php artisan migrate:fresh
php artisan db:seed --class=RolePermissionSeeder
```

This will wipe everything and recreate all tables fresh, then reseed permissions.

**Option 2: Selective Truncate**

Manually truncate business tables while keeping:

- `users`, `password_reset_tokens`, `sessions`, `personal_access_tokens`
- `permissions`, `roles`, `role_has_permissions`, `model_has_permissions`, `model_has_roles`

For this implementation, we'll use **Option 1** since the type field needs to be in the units table from the start.

## Desired Behavior

**Example Flow:**

1. Admin defines units with types:

   - "pcs" (type: piece, base unit)
   - "Box" (type: piece, derived)
   - "kg" (type: weight, base unit)
   - "gram" (type: weight, derived)

2. Product "Coca Cola" is defined with base unit "pcs" (type: piece)

3. In Purchase page:

   - Select product: Coca Cola (pcs)
   - Unit dropdown shows ONLY piece-type units: pcs, Box, Carton (NOT kg, gram)
   - Select unit: Box
   - Enter custom conversion: 12 (meaning 1 Box = 12 pcs for THIS purchase)
   - Enter quantity: 5 boxes
   - Enter unit cost: Rp 100,000 per box
   - Result: Adds 60 pcs to inventory (5 × 12), with unit cost Rp 8,333.33 per pcs

## Changes Required

### 0. Update Units Migration and Create UserSeeder

**File:** `database/migrations/2025_10_01_091331_create_units_table.php`

Update the existing units migration to include the `type` field from the start:

```php
Schema::create('units', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('symbol')->nullable();
    $table->enum('type', ['piece', 'weight', 'volume', 'length'])->default('piece'); // ADD THIS
    $table->unsignedBigInteger('parent_unit_id')->nullable();
    $table->decimal('conversion_factor', 10, 4)->default(1);
    $table->boolean('is_base_unit')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->foreign('parent_unit_id')->references('id')->on('units')->onDelete('set null');
    $table->index(['is_active', 'is_base_unit']);
});
```

**File:** Create `database/seeders/UserSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }
    }
}
```

**File:** Update `database/seeders/DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call([
        RolePermissionSeeder::class,
        UserSeeder::class,  // ADD THIS
    ]);
}
```

Then run:

```bash
php artisan migrate:fresh --seed
```

### 1. Add Unit Type to Units Table (SKIP - already done in step 0)

**File:** Create new migration `2025_10_17_add_type_to_units_table.php`

```php
Schema::table('units', function (Blueprint $table) {
    $table->enum('type', ['piece', 'weight', 'volume', 'length'])->default('piece')->after('symbol');
});
```

Unit types:

- `piece`: pcs, box, carton, pack, dozen, etc.
- `weight`: kg, gram, ton, pound, etc.
- `volume`: liter, ml, gallon, etc.
- `length`: meter, cm, yard, etc.

### 2. Update Unit Model

**File:** `app/Models/Unit.php`

Add type to fillable and add scope for filtering:

```php
protected $fillable = [
    'name',
    'symbol',
    'type',  // NEW
    'is_base_unit'
];

// Scope for filtering by type
public function scopeOfType($query, $type)
{
    return $query->where('type', $type);
}
```

### 3. Update purchase_items Table Schema

**File:** Create new migration `2025_10_17_add_unit_fields_to_purchase_items_table.php`

Add columns to store unit information:

```php
Schema::table('purchase_items', function (Blueprint $table) {
    $table->string('unit_name')->nullable()->after('product_id'); // e.g., "Box"
    $table->decimal('unit_conversion', 10, 4)->default(1)->after('unit_name'); // e.g., 12.0000
    $table->decimal('quantity_in_base_unit', 10, 2)->after('quantity'); // converted quantity
});
```

### 4. Update PurchaseItem Model

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

### 5. Update PurchaseController

**File:** `app/Http/Controllers/PurchaseController.php`

In `create()` method:

```php
public function create()
{
    $products = Product::with(['unit', 'baseUnit'])
        ->orderBy('name')
        ->get(['id', 'name', 'stock', 'unit_id', 'base_unit_id', 'cost_price']);

    // Get all units grouped by type
    $units = Unit::orderBy('type')->orderBy('name')->get(['id', 'name', 'symbol', 'type']);

    return Inertia::render('Purchases/Create', [
        'products' => $products,
        'units' => $units,  // Now includes 'type' field
        'purchaseNumber' => Purchase::generatePurchaseNumber(),
    ]);
}
```

In `store()` method - logic remains the same but validates unit_name and unit_conversion.

### 6. Update Purchase Create Form

**File:** `resources/js/Pages/Purchases/Create.vue`

For each item in the purchase:

```vue
<template>
  <!-- Product selection -->
  <select v-model="item.product_id" @change="onProductChange(item)">
    <option v-for="product in products" :value="product.id">
      {{ product.name }}
    </option>
  </select>

  <!-- Unit selection - filtered by product's base unit type -->
  <select v-model="item.unit_name">
    <option v-for="unit in getCompatibleUnits(item.product_id)" :value="unit.name">
      {{ unit.name }}
    </option>
  </select>

  <!-- Custom conversion factor -->
  <input 
    v-model.number="item.unit_conversion" 
    type="number"
    step="0.0001"
    min="0.0001"
    placeholder="Contoh: 12"
  />

  <!-- Preview -->
  <div class="text-sm text-gray-600">
    1 {{ item.unit_name }} = {{ item.unit_conversion }} {{ getProductBaseUnit(item.product_id) }}
    <br>
    Total: {{ item.quantity * item.unit_conversion }} {{ getProductBaseUnit(item.product_id) }}
  </div>
</template>

<script setup>
// Filter units by product's base unit type
const getCompatibleUnits = (productId) => {
  const product = props.products.find(p => p.id === productId);
  if (!product || !product.base_unit) return [];
  
  const baseUnitType = product.base_unit.type;
  return props.units.filter(unit => unit.type === baseUnitType);
};

const getProductBaseUnit = (productId) => {
  const product = props.products.find(p => p.id === productId);
  return product?.base_unit?.name || '';
};

const onProductChange = (item) => {
  // Reset unit fields when product changes
  const compatibleUnits = getCompatibleUnits(item.product_id);
  if (compatibleUnits.length > 0) {
    item.unit_name = compatibleUnits[0].name;
    item.unit_conversion = 1;
  }
};
</script>
```

### 7. Update Purchase Show/Index Pages

**Files:**

- `resources/js/Pages/Purchases/Show.vue`
- `resources/js/Pages/Purchases/Index.vue`

Display unit information for each item:

```
5 Box @ 12 pcs/box (60 pcs total)
Unit Cost: Rp 100,000/box (Rp 8,333/pcs)
```

### 8. Update UnitController

**File:** `app/Http/Controllers/UnitController.php`

Update validation in `store()` and `update()` methods to include type field:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:units,name',
        'symbol' => 'nullable|string|max:10',
        'type' => 'required|in:piece,weight,volume,length',  // NEW
        'is_base_unit' => 'required|boolean'
    ]);

    Unit::create($validated);

    return redirect()->route('units.index')
        ->with('success', 'Unit berhasil ditambahkan!');
}

public function update(Request $request, Unit $unit)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
        'symbol' => 'nullable|string|max:10',
        'type' => 'required|in:piece,weight,volume,length',  // NEW
        'is_base_unit' => 'required|boolean'
    ]);

    $unit->update($validated);

    return redirect()->route('units.index')
        ->with('success', 'Unit berhasil diperbarui!');
}
```

### 9. Update Units/Create.vue

**File:** `resources/js/Pages/Units/Create.vue`

Add unit type selection dropdown after the symbol field:

```vue
<!-- Add after Symbol field, before Tipe Unit (is_base_unit) -->
<div>
  <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
    Kategori Unit
  </label>
  <select
    id="type"
    v-model="form.type"
    required
    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
  >
    <option value="piece">Piece (pcs, box, carton, dozen, pack)</option>
    <option value="weight">Weight (kg, gram, ton, pound)</option>
    <option value="volume">Volume (liter, ml, gallon)</option>
    <option value="length">Length (meter, cm, yard, inch)</option>
  </select>
  <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">
    {{ form.errors.type }}
  </div>
  <div class="text-sm text-gray-600 mt-2">
    Unit dengan kategori yang sama dapat digunakan bersama untuk satu produk
  </div>
</div>

<!-- Update form initialization -->
<script setup>
const form = useForm({
  name: '',
  symbol: '',
  type: 'piece',  // NEW - default to piece
  is_base_unit: true
})
</script>
```

### 10. Update Units/Edit.vue

**File:** `resources/js/Pages/Units/Edit.vue`

Add the same unit type selection dropdown (note: Edit.vue has duplicate "Tipe Unit" section, remove one):

```vue
<!-- Add after Symbol field -->
<div>
  <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
    Kategori Unit
  </label>
  <select
    id="type"
    v-model="form.type"
    required
    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
  >
    <option value="piece">Piece (pcs, box, carton, dozen, pack)</option>
    <option value="weight">Weight (kg, gram, ton, pound)</option>
    <option value="volume">Volume (liter, ml, gallon)</option>
    <option value="length">Length (meter, cm, yard, inch)</option>
  </select>
  <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">
    {{ form.errors.type }}
  </div>
</div>

<!-- Remove duplicate "Tipe Unit" section (lines 72-99) -->
<!-- Keep only one "Tipe Unit" section for is_base_unit -->

<!-- Update form initialization -->
<script setup>
const form = useForm({
  name: props.unit.name,
  symbol: props.unit.symbol || '',
  type: props.unit.type || 'piece',  // NEW
  is_base_unit: props.unit.is_base_unit
})
</script>
```

### 11. Update Units/Index.vue

**File:** `resources/js/Pages/Units/Index.vue`

Display unit type in the units table. Add a "Type" column showing the unit category with a badge:

```vue
<!-- Add Type column in table header -->
<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
  Kategori
</th>

<!-- Add Type cell in table body -->
<td class="px-6 py-4 whitespace-nowrap">
  <span :class="getTypeBadgeClass(unit.type)">
    {{ getTypeLabel(unit.type) }}
  </span>
</td>

<script setup>
// Helper functions for type display
const getTypeLabel = (type) => {
  const labels = {
    piece: 'Piece',
    weight: 'Weight',
    volume: 'Volume',
    length: 'Length'
  }
  return labels[type] || type
}

const getTypeBadgeClass = (type) => {
  const classes = {
    piece: 'bg-blue-100 text-blue-800',
    weight: 'bg-green-100 text-green-800',
    volume: 'bg-purple-100 text-purple-800',
    length: 'bg-orange-100 text-orange-800'
  }
  return `px-2 py-1 text-xs font-medium rounded ${classes[type] || 'bg-gray-100 text-gray-800'}`
}
</script>
```

## Result

After implementation:

- ✅ Units have types (piece, weight, volume, length)
- ✅ Purchase page filters units by product's base unit type
- ✅ Prevents mixing incompatible units (e.g., kg for piece-based products)
- ✅ Purchases can use ANY compatible unit with CUSTOM conversion per purchase
- ✅ Inventory always stored in base units
- ✅ Purchase history shows unit used and conversion factor
- ✅ FIFO batches store cost in base unit
- ✅ Maximum flexibility for different supplier packaging within compatible unit types

### To-dos

- [ ] Create migration to add type field to units table