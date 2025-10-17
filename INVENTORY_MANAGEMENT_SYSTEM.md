# Inventory Management System Documentation

## Summary of New Inventory System Plan

This plan proposes a shift to a base product master data approach, where all inventory is tracked in the smallest (base) unit. Purchases and sales are always recorded in base units, and derived units (such as boxes or packs) are handled through conversion factors. Special pricing for derived units is supported by storing separate prices for each unit type.

### Key Points
- **Base Product Master Data**: All products are defined by their base unit (e.g., pcs, kg).
- **Stock Movement**: Purchases add to, and sales subtract from, the base unit stock.
- **Derived Units**: Selling in derived units (e.g., boxes) deducts the equivalent base units from stock.
- **Special Pricing**: Both base and derived unit prices are stored, allowing for bulk or promotional pricing.
- **Consistency**: All inventory, purchasing, and sales reference the same base product, ensuring traceability and accuracy.

---

## Rollout Strategy for the New Inventory System

### 1. **Design & Planning**
   - Define the new product data model (base unit, derived units, conversion, separate prices).
   - Identify all system areas impacted (purchasing, sales, reporting, UI).
   - Plan data migration for existing products and transactions.

### 2. **Development**
   - Update product model and database schema to support base and derived unit prices.
   - Refactor transaction logic to always use base units for stock movement.
   - Implement UI changes for product selection and price display.
   - Add validation to prevent overselling and ensure correct stock deduction.

### 3. **Testing**
   - Unit test all new logic (stock movement, pricing, conversions).
   - User acceptance testing with real-world scenarios (bulk sales, promotions).
   - Validate data migration and reporting accuracy.

### 4. **Training & Documentation**
   - Update user manuals and training materials.
   - Train staff on new workflows and UI changes.

### 5. **Deployment**
   - Roll out in a staging environment for final validation.
   - Deploy to production during low-traffic hours.
   - Monitor for issues and provide support.

### 6. **Post-Deployment**
   - Gather feedback from users.
   - Address any bugs or workflow issues.
   - Plan for future enhancements (e.g., multi-location, batch tracking).

---

**This summary and rollout strategy should be reviewed and updated as the project progresses.**

## Overview

This document outlines the inventory management capabilities of the POS application, focusing on the transaction creation system and its approach to handling product units, pricing, and sales tracking.

---

## Table of Contents

1. [Core Features](#core-features)
2. [Unit of Measure (UOM) System](#unit-of-measure-uom-system)
3. [Transaction Item Tracking](#transaction-item-tracking)
4. [Smart Cart Management](#smart-cart-management)
5. [Price Management](#price-management)
6. [Data Structure](#data-structure)
7. [User Experience Features](#user-experience-features)
8. [System Limitations](#system-limitations)
9. [Technical Implementation](#technical-implementation)

---

## Core Features

### 1. **Dual-Unit Inventory Management**
The system supports flexible unit-of-measure configuration allowing products to be sold in two different units:
- **Base Unit**: The smallest sellable unit (e.g., pcs, gram, ml)
- **Derived Unit**: A larger packaging unit that contains multiple base units (e.g., box, pack, carton)

### 2. **Dynamic Unit Selection**
Cashiers can choose to sell products in either:
- The derived unit (default packaging)
- The base unit (individual items)

### 3. **Automatic Price Calculation**
The system automatically calculates prices based on the selected unit type using the conversion ratio (`unit_quantity`).

---

## Unit of Measure (UOM) System

### Configuration

Each product can have:
- **Primary Unit** (`unit_id`): The main selling unit
- **Base Unit** (`base_unit_id`): The smallest unit component
- **Unit Quantity** (`unit_quantity`): Conversion ratio between units

### Example Scenarios

#### Scenario 1: Boxed Product
```
Product: Mineral Water
- Derived Unit: Box (unit_id)
- Base Unit: Bottle (base_unit_id)
- Unit Quantity: 24 (bottles per box)
- Price: Rp 72,000 per box

Calculated Prices:
- Per Box: Rp 72,000
- Per Bottle: Rp 3,000 (72,000 ÷ 24)
```

#### Scenario 2: Weight-Based Product
```
Product: Rice Premium
- Derived Unit: Sack (unit_id)
- Base Unit: Kg (base_unit_id)
- Unit Quantity: 25 (kg per sack)
- Price: Rp 500,000 per sack

Calculated Prices:
- Per Sack: Rp 500,000
- Per Kg: Rp 20,000 (500,000 ÷ 25)
```

#### Scenario 3: Single Unit Product
```
Product: T-Shirt
- Unit: Pcs (unit_id)
- Base Unit: Not configured
- Unit Quantity: 1
- Price: Rp 150,000 per pcs

Behavior:
- No unit selection available
- Sold only in pcs
```

---

## Transaction Item Tracking

### Data Captured Per Item

Each transaction line item records:

| Field | Type | Description |
|-------|------|-------------|
| `product_id` | Integer | Reference to the product |
| `quantity` | Decimal | Amount sold (supports decimals for precision) |
| `price_each` | Decimal | Unit price at time of sale |
| `sell_in_base_unit` | Boolean | `true` if sold in base unit, `false` if sold in derived unit |

### Benefits of This Approach

1. **Historical Accuracy**: Prices are locked at transaction time
2. **Audit Trail**: Clear record of which unit type was used
3. **Flexibility**: Supports decimal quantities for weight-based products
4. **Reporting**: Can analyze sales by unit type

---

## Smart Cart Management

### Duplicate Detection & Auto-Merge

The system prevents duplicate entries of the same product with the same unit type:

#### When Adding Product
```javascript
// If product already exists in cart with same unit type
→ Increment quantity of existing item
→ Remove the newly added row
```

#### When Switching Unit Type
```javascript
// If switching to a unit type that already exists in cart
→ Add current quantity to existing item
→ Remove current row
```

### Example Flow

**Step 1**: Add "Mineral Water" (selling in bottles)
```
Cart:
[1] Mineral Water - 1 Bottle @ Rp 3,000 = Rp 3,000
```

**Step 2**: Add "Mineral Water" again (also bottles)
```
Cart:
[1] Mineral Water - 2 Bottles @ Rp 3,000 = Rp 6,000
(Auto-merged)
```

**Step 3**: Add "Mineral Water" but select "Box"
```
Cart:
[1] Mineral Water - 2 Bottles @ Rp 3,000 = Rp 6,000
[2] Mineral Water - 1 Box @ Rp 72,000 = Rp 72,000
(Separate line item because unit type differs)
```

---

## Price Management

### Default Pricing Logic

When a product is selected:

1. **Check for Derived Unit Configuration**
   ```javascript
   if (product.base_unit_id && product.unit_quantity > 1) {
     // Has derived unit - default to base unit selling
     sell_in_base_unit = true
     price_each = product.price / product.unit_quantity
   } else {
     // Single unit product
     sell_in_base_unit = false
     price_each = product.price
   }
   ```

2. **When Unit Type is Changed**
   ```javascript
   if (sell_in_base_unit) {
     // Selling in base unit (e.g., per bottle)
     price_each = product.price / product.unit_quantity
   } else {
     // Selling in derived unit (e.g., per box)
     price_each = product.price
   }
   ```

### Manual Price Override

Cashiers can manually adjust the `price_each` field:
- Useful for promotions
- Special customer pricing
- Damaged goods discounts
- Manager overrides

---

## Data Structure

### Product Model (Relevant Fields)

```javascript
{
  id: Integer,
  name: String,
  price: Decimal,           // Base price (usually for derived unit)
  unit_id: Integer,         // Primary/Derived unit
  base_unit_id: Integer,    // Smallest unit (optional)
  unit_quantity: Decimal,   // Conversion factor
  unit: {                   // Relationship
    id: Integer,
    name: String
  },
  base_unit: {              // Relationship
    id: Integer,
    name: String
  }
}
```

### Transaction Form Structure

```javascript
{
  member_id: Integer|Null,
  payment_type: String,     // 'Cash', 'QR', 'Transfer'
  items: [
    {
      product_id: Integer,
      quantity: Decimal,
      price_each: Decimal,
      sell_in_base_unit: Boolean
    }
  ],
  discount_type: String|Null,  // 'fixed', 'percent', null
  discount_value: Decimal,
  paid_amount: Decimal,
  notes: String
}
```

---

## User Experience Features

### 1. **Quantity Controls**

- **Manual Input**: Direct numeric entry with 3 decimal precision
- **Increment/Decrement Buttons**: 
  - Plus (+): Increases by 1
  - Minus (−): Decreases by 1
  - Minimum: 0.001

### 2. **Unit Selection Interface**

For products with derived units:
```
[Dropdown: Jual Dalam]
Options:
  - Box (Derived Unit)
  - Bottle (Base Unit)

Price Info Display:
  "Rp 3,000 / Bottle" or "Rp 72,000 / Box"
```

For single-unit products:
```
[Display Only: Unit]
  Pcs (non-editable)
```

### 3. **Real-time Calculations**

All calculations update automatically:
- **Subtotal per item**: `quantity × price_each`
- **Cart subtotal**: Sum of all item subtotals
- **Discount amount**: Based on type and value
- **Total payable**: `subtotal - discount`
- **Change amount**: `paid_amount - total`

### 4. **Discount System**

Two discount types supported:

| Type | Calculation | Example |
|------|-------------|---------|
| Fixed | Direct deduction (capped at subtotal) | Rp 10,000 off |
| Percent | Percentage of subtotal (0-100%) | 15% discount |

### 5. **Payment Information**

- **Payment Type**: Cash, QR, Transfer
- **Paid Amount**: Cash tendered by customer
- **Change Calculation**: Automatic change calculation

---

## System Limitations

### What's Currently Implemented ✅

- Dual-unit product selling
- Flexible quantity entry (decimal support)
- Price locking per transaction
- Smart cart duplicate prevention
- Discount management
- Multiple payment types
- Customer/member assignment

### What's NOT Visible in Current View ⚠️

The transaction creation interface doesn't show:

1. **Stock Deduction Logic**
   - No indication inventory is decremented after sale
   - Need to check backend controller

2. **Stock Availability Validation**
   - No "out of stock" warnings
   - No quantity available display
   - No prevention of overselling

3. **Stock Level Display**
   - Cashier can't see current stock
   - No real-time inventory visibility

4. **FIFO/LIFO/Batch Tracking**
   - No batch number selection
   - No expiry date management
   - No serial number tracking

5. **Multi-Location Inventory**
   - No warehouse selection
   - No location/bin tracking
   - Single-location assumed

6. **Inventory Movements**
   - No inbound receiving visible
   - No stock adjustments
   - No transfer between locations
   - No damage/loss recording

7. **Stock Alerts**
   - No low stock warnings
   - No reorder point notifications
   - No stock forecasting

---

## Technical Implementation

### Key Functions

#### `onProductChange(itemIndex)`
Handles product selection:
- Sets default unit type (base unit if derived exists)
- Calculates appropriate price
- Checks for duplicates and merges if found
- Resets quantity to 1

#### `onUnitTypeChange(itemIndex)`
Handles unit type switching:
- Recalculates price based on new unit type
- Checks for duplicates with same unit type
- Merges if duplicate found

#### `canSelectUnit(productId)`
Determines if unit selection dropdown should appear:
```javascript
return product.base_unit_id && product.unit_quantity > 1
```

#### Price Calculation Functions
- `getPriceInfo(item)`: Returns formatted price with unit name
- `formatRupiah(value)`: Indonesian currency formatting
- `formatNumber(value)`: Smart number formatting (removes trailing zeros)

### Computed Properties

```javascript
// Cart subtotal
subtotal = Σ(item.quantity × item.price_each)

// Discount calculation
discountAmount = {
  fixed: min(discount_value, subtotal)
  percent: subtotal × (discount_value / 100)
}

// Final total
total = max(0, subtotal - discountAmount)

// Change calculation
changeAmount = max(0, paid_amount - total)
```

---

## Best Practices & Recommendations

### For Complete Inventory Management

To build a complete system, consider implementing:

1. **Stock Tracking**
   - Real-time stock deduction on transaction save
   - Stock balance display on product selection
   - Prevent negative stock (configurable)

2. **Stock Validation**
   ```javascript
   // Before allowing product selection
   if (product.stock < requested_quantity) {
     alert('Insufficient stock')
   }
   ```

3. **Batch/Lot Management**
   - For products with expiry dates
   - FIFO/FEFO logic for automatic batch selection
   - Batch number display and manual selection

4. **Multi-Location Support**
   - Warehouse/location selection per transaction
   - Stock transfer between locations
   - Location-specific stock balances

5. **Inventory Adjustments**
   - Stock take/cycle count functionality
   - Damage/loss recording
   - Manual stock corrections with approval workflow

6. **Purchase/Receiving Module**
   - Supplier management
   - Purchase orders
   - Goods receiving
   - Stock level replenishment

7. **Reporting & Analytics**
   - Stock movement report
   - Sales by product/unit type
   - Inventory aging
   - Stock valuation
   - Turnover ratio

---

## Files to Review for Complete Picture

To understand the full inventory flow:

1. **Backend Controller**: `/app/Http/Controllers/TransactionController.php`
   - Check `store()` method for stock deduction logic

2. **Product Model**: `/app/Models/Product.php`
   - Check for stock-related fields and methods
   - Look for `decrementStock()` or similar methods

3. **Database Migrations**: `/database/migrations/*_create_products_table.php`
   - Check schema for stock fields
   - Look for `stock`, `min_stock`, `max_stock` columns

4. **Inventory Pages**: `/resources/js/Pages/Products/` or `/resources/js/Pages/Inventory/`
   - Check for stock management interfaces
   - Look for adjustment/transfer pages

5. **API Routes**: `/routes/api.php` or `/routes/web.php`
   - Check for inventory-related endpoints

---

## Conclusion

The current transaction creation system demonstrates a **flexible and user-friendly approach** to handling multi-unit product sales with:

- ✅ Intelligent unit conversion
- ✅ Automatic price calculation
- ✅ Smart duplicate prevention
- ✅ Real-time subtotal calculations
- ✅ Flexible discount options

However, to have a **complete inventory management system**, integration with stock tracking, validation, and reporting features would be essential.

---

**Document Version**: 1.0  
**Last Updated**: October 17, 2025  
**Author**: System Documentation  
**Related Files**: 
- `/resources/js/Pages/Transactions/Create.vue`
