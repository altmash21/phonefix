# MobiTrack — Mobile Shop ERP & POS

[![Built on Akaunting](https://img.shields.io/badge/Built%20on-Akaunting-6366F1)](https://akaunting.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-Framework-FF2D20)](https://laravel.com)
[![Google Gemini](https://img.shields.io/badge/AI%20OCR-Gemini%201.5%20Flash-4285F4)](https://deepmind.google/technologies/gemini/)
[![License](https://img.shields.io/badge/License-Proprietary-blue)](LICENSE.txt)

A complete, specialized **Mobile Retail & Service ERP System** built on top of Laravel/Akaunting. Designed specifically for Indian mobile phone retail outlets, wholesale distributors, and multi-counter repair centers.

---

## 🌟 Core Highlights & Modern Features

### ⚡ 1. AI-Powered OCR Auto-Fill (Google Gemini 1.5 Flash)
- **Instant Document Recognition**: Snap a photo or upload an EMI Delivery Challan, Distributor Invoice, or Finance Slip (Bajaj Finserv, TVS Credit, HDB, Home Credit, IDFC First, etc.).
- **Automatic Form Population**: Automatically extracts and populates:
  - Customer Name, 10-Digit Mobile, Address, GSTIN
  - Phone Brand, Model, and 15-Digit IMEI Number
  - Financier Selection, Loan Reference #, Down Payment, and Sale Price
- **Active Stock Matching**: Automatically matches the extracted IMEI against active in-stock devices and locks the inventory row.

### 📜 2. Mixed Billing (Formal GST vs. Retail Estimate)
- **Flexible Bill Type Toggle**: Toggle between **"Formal GST Tax Invoice (18% incl.)"** and **"Estimate / Retail Bill (0% Tax)"** on all checkout counters (New Phones, Pre-Owned, Accessories, and Inward Procurement).
- **Dynamic Invoicing**: Invoices automatically switch headings between **TAX INVOICE** and **ESTIMATE**, hiding tax regime subtext and GST breakdown for retail walk-in bills.

### 📊 3. Tally-Style Customer Ledger Statement (Hisab-Kitab)
- **Chronological Ledger on Invoices**: Every customer bill displays a running statement of previous invoices, repayments, and live balance due.
- **Standalone A4 Statement & PDF**: Generate, print, or download dedicated A4 Customer Account Statements (`/mobileshop/khata/customer/{id}/statement`).
- **Pre-Formatted WhatsApp Statements**: 1-click WhatsApp button generates a clean, text-based ledger breakdown with totals directly in the chat.

### 📸 4. Device Condition Photography & Lightbox
- **Dual Photo Intake**: Upload/capture both **Device Condition Photo** and **Sealed Box Photo** during stock intake and customer buybacks.
- **Fullscreen Lightbox**: Single-tap image zoom modal with frosted-glass backdrop across desktop and mobile screens.

### 📱 5. Mobile-First Zero-Depth UI
- **Mobile Stat Strips**: Compact, sticky metric bars for quick revenue and inventory insights on mobile screens.
- **Zero-Depth Flat Cards**: High-density flat card rows replace bulky horizontal-scrolling tables on mobile devices.
- **Floating Action Buttons (FAB)**: Quick-access floating action buttons for adding stock and intake from anywhere.

---

## 🏪 The 5 Operational Hubs

```
┌────────────────────────────────────────────────────────────────────────┐
│                        MobiTrack Operational Hubs                       │
├───────────────┬────────────────┬───────────────┬───────────────────────┤
│ 📱 New Phones │ 🔄 Second-Hand │ 📦 Accessory  │ 🔧 Service Desk       │
│  - Boxed POS  │  - Buybacks    │  - Multi-Cart │  - Job Sheets         │
│  - IMEI Match │  - Diagnostics │  - Stock Flow │  - Part Consumption   │
│  - EMI Finance│  - Refurb POS  │  - Dual Print │  - Diagnostic Stages  │
└───────────────┴────────────────┴───────────────┴───────────────────────┘
```

1. **Brand New Mobiles POS & Inventory Hub**
   - Boxed IMEI serial tracking, promotional gift bundles, and EMI financier integration.
   - Atomic sequential GST invoicing: `INV-YYYYMM-0001`.
2. **Second-Hand & Buyback Hub**
   - Customer KYC, dual IMEI capture, battery health %, condition grading (Grade A+/A/B), and trade-in resale pricing.
3. **Accessories & Spare Parts Counter**
   - Multi-item shopping cart checkout for covers, tempered glass, folders, chargers, and batteries.
   - Dual print formats: **Standard A4 Tax Invoice** & **80mm POS Thermal Receipt**.
4. **Repair Service Desk**
   - Customer job sheet generation with device lock patterns/passcodes, diagnostic stages, technician assignment, spare part consumption, and labor charge tracking.
5. **Customer Khata (Udhari) & Procurement Ledger**
   - Outstanding customer credit management, WhatsApp payment reminders, settlement collections, and supplier purchase orders.

---

## 👥 Staff Roles & Default Credentials

| Role | Email | Password | Accessible Panels |
| :--- | :--- | :--- | :--- |
| **Store Owner / Admin** | `admin@mobitrack.local` | `password` | Complete store access + void authorizations |
| **New Phones Staff** | `sales@mobitrack.local` | `password` | New Mobiles POS, Stock & Khata Lookup |
| **Second-Hand Specialist** | `buyback@mobitrack.local` | `password` | Second-Hand Intake & Pre-Owned POS |
| **Accessories Staff** | `accessories@mobitrack.local` | `password` | Accessories Multi-Item Cart & Catalog |
| **Repair Technician** | `tech@mobitrack.local` | `password` | Service Desk & Repair Job Sheets |

> ⚠️ **Important:** Change all default passwords immediately before production launch.

---

## 🔐 Security & Concurrency Architecture

- **Fail-Closed Multi-Tenancy**: All database queries are strictly scoped by `company_id` and verified against authenticated user company memberships (`abort(403)` on mismatch).
- **Concurrency & Race Condition Safety**: All inventory decrements and credit wallet deductions use `DB::transaction()` with pessimistic row locks (`lockForUpdate()`).
- **Idempotency Safeguards**: Client-generated UUID `idempotency_key` guarantees no double-billing occurs from network timeouts or repeated button clicks.
- **Race-Free Invoice Numbers**: High-performance atomic sequential invoice generator backed by the `ms_invoice_sequences` table.
- **Fraud-Proof Audit Logs**: Sales voids and cancellations require staff reasons, restock inventory safely, reverse customer balances, and create permanent audit log records.

---

## 🚀 Installation & Setup

### 1. Requirements
- PHP 8.1 or PHP 8.2
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.x
- Node.js 18+ & npm

### 2. Setup Codebase
```bash
# Clone the repository
git clone <your-repository-url> mobitrack
cd mobitrack

# Install PHP dependencies
composer install

# Install frontend assets
npm install && npm run build
```

### 3. Environment & Gemini AI Configuration
Copy `.env.example` to `.env` and configure your database and Google Gemini API credentials:

```env
APP_NAME="MobiTrack ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mobitrack
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Google Gemini API Key for OCR Auto-Fill (1,500 free scans/day)
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-1.5-flash
```

### 4. Run Migrations & Seeders
```bash
# Run database schema migrations
php artisan migrate

# Seed staff roles, sample inventory, and demo data
php artisan db:seed --class="Database\Seeds\MobileShopRbacSeeder"
```

### 5. Storage Symlink & Optimizations
```bash
# Link public storage for device photos
php artisan storage:link

# Cache routes, views, and configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🗄️ Database Architecture

| Table Name | Description |
| :--- | :--- |
| `ms_mobile_devices` | In-stock brand new and pre-owned smartphones with IMEI 1/2, specs, and photos |
| `ms_mobile_sales` | Phone sale transactions, bill type (gst/non_gst), payment modes, and EMI metadata |
| `ms_parts_inventory` | Accessories & spare parts catalog with live stock counts and min-stock alert thresholds |
| `ms_parts_inventory_history` | Complete chronological stock in/out ledger per item with user attribution |
| `ms_accessory_sales` | Multi-item counter sales headers with tax breakdown |
| `ms_accessory_sale_items` | Line items for multi-item accessory sales |
| `ms_repair_tickets` | Repair service desk job sheets, issue diagnostics, pattern locks, and stages |
| `ms_repair_ticket_parts` | Spare parts consumed during repair jobs |
| `ms_customers` | Customer directory, GSTIN, phone, address, and live Khata balance |
| `ms_customer_khata_transactions` | Customer credit/debit transaction ledger with running balance |
| `ms_purchase_orders` | Supplier inward shipments, PO numbers, and vendor balance dues |
| `ms_suppliers` | Vendor & distributor master records |
| `ms_supplier_credit_wallets` | Supplier advance and credit balance tracking |
| `ms_emi_providers` | Finance companies (Bajaj, TVS, HDB, IDFC, etc.) and advance pool balances |
| `ms_invoice_sequences` | Atomic sequence counters for race-free invoice numbering |

---

## 🌐 Hosting & Deployment Recommendations

### YouStable / cPanel Shared Hosting
- **PHP Version**: Set to `PHP 8.1` or `PHP 8.2`.
- **PHP Limits**: `memory_limit = 512M`, `max_execution_time = 120`.
- **Document Root**: Ensure cPanel points your domain's Document Root to the `/public` subdirectory.

### Cloud VPS (Recommended for Multi-Store Retail)
- **Stack**: Ubuntu 22.04 LTS + Nginx + PHP 8.2-FPM + MySQL 8.0.
- **OPcache**: Enable `opcache.enable=1` and `opcache.memory_consumption=256` for sub-100ms response times.

---

## 📄 License
- Built on top of Akaunting (BSL License).
- MobiTrack ERP Custom Module & Extensions: Proprietary — All Rights Reserved.
