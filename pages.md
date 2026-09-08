# MobiTrack Public Marketplace — Page Architecture Specification

> **Design Anchor**: Airbnb Consumer Marketplace Design System  
> **Brand Voltage**: Airbnb Rausch (`#ff385c`), Near-black Ink (`#222222`), Pure White Canvas (`#ffffff`), and Single Shadow Tier.

---

## 🗺️ Sitemap Overview

```
MobiTrack Public Web (B2C)
├── / (Home)                     → Featured marketplace, pill search, category strip, 64px laurel rating
├── /shop                        → Live inventory catalog with filter pills (All Smartphones, Brand New, Pre-Owned)
├── /shop/{id}   [Planned]       → Device detail page with photo gallery, 50-point diagnostics & sticky reserve rail
├── /sell        [Planned]       → Instant buyback valuation calculator & trade-in estimator
├── /track-repair                → Live repair job sheet status tracker & express service pricing
├── /about                       → Showroom story, 50-point diagnostic checklist & genuine warranty pledge
└── /contact                     → Showroom address, timings (7 days), direct WhatsApp desk & callback form
```

---

## 📑 Detailed Page Specifications

### 1. Marketplace Home (`/`)
* **Blade View**: `resources/views/mobileshop/public/home.blade.php`
* **Route**: `Route::get('/', 'MobileShopController@publicLanding')->name('public.landing')`
* **Primary Objective**: Establish brand trust, showcase live inventory, and enable immediate keyword/condition search.

#### Section Breakdown:
1. **Pill Search Bar (`search-bar-pill`)**:
   - 64px height, `rounded-full`, 1px hairline border, single shadow tier.
   - Divided into 3 segments:
     - **Where / Search**: Input field for Brand or Model (*e.g. iPhone 15, Galaxy S24*).
     - **Condition**: Dropdown selector (*All Smartphones, Brand New (Sealed), Certified Pre-Owned*).
     - **Service Desk**: Micro-label (*Express 45-min repair lab*).
   - Terminating **Search Orb (`search-orb`)**: 48px circular Rausch `#ff385c` button with white magnifying glass icon.
2. **Category Navigation Strip (`category-strip`)**:
   - Horizontal scrolling strip with 32px icons and clean active ink underlines:
     - *All Smartphones*
     - *Brand New*
     - *Certified Pre-Owned (NEW tag)*
     - *Track Repair*
     - *0% EMI Financing*
3. **Brand New Sealed Smartphones Demo Showcase**:
   - Section header with *"Explore All New Phones →"* button linking to `/shop?tab=new`.
   - 4-column responsive photo-first cards (`property-card`) for top 4 demo units.
   - 1:1 photo plate (`rounded-[14px]` corner clipping).
   - Floating white pill badge top-left: `100% Sealed`.
   - Floating 32px circular wishlist heart button top-right.
   - 4-line metadata: Title, specs (*Storage / RAM / Color*), official warranty note, bold price (`₹XX,XXX incl. GST`), and WhatsApp inquiry CTA.
4. **Certified Pre-Owned Highlights**:
   - Section header with *"Explore All Pre-Owned →"* button linking to `/shop?tab=second_hand`.
   - 4-column grid for top 4 inspected second-hand devices.
   - Floating badges: `Grade A+` and battery health (`🔋 92% Battery Health`).
   - Multi-point diagnostic badge: `50-Point Checked`.
   - Deal price + "Reserve Device" CTA.
5. **Express Repair Service Banner**:
   - Soft surface card introducing Level 4 micro-soldering diagnostics and direct *"Track Live Repair Status →"* button.
6. **Signature Rating Display Card (`rating-display-card`)**:
   - Airbnb's loudest typographic moment: **64px / 700 rating display** (`4.92`) flanked by laurel wreath ornaments.
   - Tagline: *"Guest favorite — Loved by over 1,850+ customers across Mumbai"*.
   - 2-column verified customer review excerpts with customer avatars.
7. **Showroom & Map Split CTA**:
   - Split 2-column layout:
     - **Left (6 cols)**: Showroom address, phone, WhatsApp counter desk (+91 98765 43210), hours (Open 7 days), inquiry CTA.
     - **Right (6 cols)**: Google Maps store location plate with *"Open Navigation in Google Maps"* button.

---

### 2. Explore Store (`/shop`)
* **Blade View**: `resources/views/mobileshop/public/shop.blade.php`
* **Route**: `Route::get('shop', 'MobileShopController@publicStore')->name('public.store')`
* **Primary Objective**: Granular filtering and real-time inventory discovery across all smartphone models.

#### Section Breakdown:
1. **Catalog Header**:
   - Headline (`display-xl` 28px/700): *"Explore In-Stock Catalog"*.
   - Real-time showroom sync subtext.
2. **Search & Category Pills (`category-tab-active` / `inactive`)**:
   - Category switcher pills:
     - `All Smartphones (Count)`
     - `📱 Brand New (Count)`
     - `🔄 Certified Pre-Owned (Count)`
   - Real-time search bar with 2px ink focus state.
3. **Inventory Sections**:
   - Grouped sections for Brand New Sealed and Certified Pre-Owned Devices.
   - 4-column grid of property cards with stock status.
   - One-tap WhatsApp reserve/purchase button pre-filling the exact device name and price.

---

### 3. *[Recommended Addition]* Device Detail Page (`/shop/{id}`)
* **Airbnb Equivalent**: Listing Detail Page
* **Primary Objective**: Eliminate final customer hesitation before buying or reserving a specific phone.

#### Section Breakdown:
1. **Device Photo Gallery**:
   - 2-column or 5-grid photo layout showing sealed box verification or exact pre-owned physical cosmetic condition.
2. **Device Headline & Badges**:
   - Model name, brand, storage, color, and condition grade.
   - Verified serial / IMEI status.
3. **Sticky Right-Rail Reservation Card (`reservation-card`)**:
   - Nightly price equivalent: Selling price in `display-md` (21px/700).
   - Free bundled perks: *Free 9D Tempered Glass + Protective Case*.
   - EMI calculation box: *From ₹3,499/mo on 0% EMI*.
   - Primary Action: Full-width Rausch CTA (`button-primary` 48px height) **"Reserve For Store Pickup"**.
   - Direct WhatsApp sales desk chat button.
4. **Diagnostic Inspection Sheet** (for Pre-Owned):
   - Battery health health reading (`🔋 94% OEM Health`).
   - Screen test (touch grid, TrueTone, 0 dead pixels).
   - Biometric sensor test (FaceID / TouchID passed).
   - Camera focus and optical image stabilization report.
5. **Showroom Pickup Assurance**:
   - *"Ready for pickup in 15 minutes at Linking Road showroom"*.
   - 30-day testing warranty & receipt details.

---

### 4. *[Recommended Addition]* Instant Buyback & Trade-In (`/sell`)
* **Airbnb Equivalent**: "Airbnb Your Home" Earnings Estimator
* **Primary Objective**: Acquire high-margin pre-owned inventory by offering customers instant cash valuations.

#### Section Breakdown:
1. **Interactive Valuation Calculator**:
   - **Step 1**: Select Brand (Apple, Samsung, OnePlus, Xiaomi, Vivo, Oppo, Google).
   - **Step 2**: Select Model & Variant (*e.g., iPhone 13 128GB*).
   - **Step 3**: Select Condition (*Flawless, Light Use, Screen Scratched, Cracked Glass*).
   - **Instant Result**: Dynamic valuation range (*e.g., ₹28,500 – ₹31,000*).
2. **Exchange Upgrade Bonus**:
   - Highlight banner: *"Get +₹2,000 extra trade-in bonus when upgrading to any Brand New smartphone"*.
3. **Transparent 3-Step Process**:
   - 1. Online valuation quote.
   - 2. 10-minute counter inspection at Bandra West showroom.
   - 3. Instant cash / UPI payment or trade-in credit.
4. **CTA**: *"Lock Estimate & Book Counter Drop-Off"*.

---

### 5. Live Repair Lab (`/track-repair`)
* **Blade View**: `resources/views/mobileshop/public/track_repair.blade.php`
* **Route**: `Route::get('track-repair', 'MobileShopController@publicTrackRepair')->name('public.track_repair')`
* **Primary Objective**: Real-time service transparency for ongoing repairs and express service discovery.

#### Section Breakdown:
1. **Job Sheet Ticket Lookup Card**:
   - Clean 56px white text input with 2px ink focus border.
   - Placeholder: *e.g. REP-2026-0042 or 0042*.
   - Primary Rausch search button.
2. **Verified Job Sheet Result**:
   - Ticket number, brand, model, customer name, and intake timestamp.
   - Status badge (*Received, In Diagnosis, Waiting for Parts, Tested & Ready, Delivered*).
3. **4-Stage Visual Laboratory Stepper**:
   - *Stage 1: Intake Received*
   - *Stage 2: In Repair / Diagnostic*
   - *Stage 3: Tested & Ready for Pickup (Rausch highlight)*
   - *Stage 4: Delivered*
4. **Financial Summary Box**:
   - Advance paid vs. balance due upon pickup.
   - WhatsApp pickup confirmation button when status is `ready`.
5. **Express Repair Service Menu**:
   - Original Display Glass replacement (~35 mins).
   - High-capacity Battery replacement (~25 mins).
   - Chip-level Motherboard IC repair (Same day).
   - Water damage ultrasonic cleansing (24–48 hrs).

---

### 6. About & Quality Lab (`/about`)
* **Blade View**: `resources/views/mobileshop/public/about.blade.php`
* **Route**: `Route::get('about', 'MobileShopController@publicAbout')->name('public.about')`
* **Primary Objective**: Build deep consumer trust and differentiate from grey-market street shops.

#### Section Breakdown:
1. **Brand Story & Philosophy**:
   - Established in 2018 on Linking Road, Bandra West.
   - Eliminating fraud in mobile retail through certified diagnostics and transparent warranties.
2. **Key Metric Columns**:
   - *15,000+ Happy Customers*
   - *99.2% Repair Success Rate*
   - *4.92 ★ Google Customer Rating*
3. **The 50-Point Diagnostic Checklist**:
   - 4 category cards detailing technical benchmarks:
     - **1. Display & Touch**: Touch grid, TrueTone, dead pixel test, scratch grading.
     - **2. Battery & Thermal**: OEM battery verification, 80%+ health check, fast charge testing.
     - **3. Cameras & Sensors**: OIS, 4K recording, FaceID/TouchID, proximity sensors.
     - **4. Legality & Network**: National police IMEI blacklist check, 5G/4G dual-SIM, Wi-Fi 6.
4. **The MobiTrack Standard Card**:
   - 100% genuine tax invoices, Level 4 micro-soldering lab, and transparent buyback valuations.
5. **Showroom Experience CTA**:
   - Directions and invitation to visit in person.

---

### 7. Showroom & Contact (`/contact`)
* **Blade View**: `resources/views/mobileshop/public/contact.blade.php`
* **Route**: `Route::get('contact', 'MobileShopController@publicContact')->name('public.contact')`
* **Primary Objective**: Frictionless store visits, phone inquiries, and lead generation.

#### Section Breakdown:
1. **Showroom Location & Direct Cards**:
   - **Physical Address**: Shop #14, Linking Road, Near Bandra Station West, Mumbai 400050.
   - **Phone & WhatsApp**: Counter desk (+91 98765 43210), Service lab (+91 98765 43211).
   - **Store Timings**: 10:00 AM – 9:30 PM (Open all 7 days).
   - **Directions**: 5-minute walk from Bandra Station, street and valet parking info.
2. **Interactive Callback & Inquiry Form**:
   - Full Name, Phone Number, Email.
   - Department selector:
     - *Brand New Smartphone Purchase*
     - *Certified Pre-Owned Inquiry*
     - *Repair Diagnosis / Price Quote*
     - *Sell My Phone / Buyback Valuation*
     - *0% EMI Financing Scheme*
   - Message textarea for device details.
   - Primary Rausch submission CTA with 30-minute callback SLA guarantee.

---

## 🎨 Global Components & Shared Layout (`layout.blade.php`)

### 1. Top Navigation (`top-nav`)
- **Height**: 80px
- **Surface**: Pure white (`#ffffff`) with 1px bottom hairline (`#ebebeb`).
- **Brand Mark**: `mobitrack` wordmark with Rausch `#ff385c` accent.
- **Product Tabs (Centered)**:
  - `Smartphones` (with phone icon)
  - `Pre-Owned` (with recycle icon + "NEW" badge)
  - `Repair Lab` (with wrench icon + "NEW" badge)
  - Active tab indicated by a **2px ink underline rule**.
- **Right Utilities**:
  - *"Sell Your Phone"* text link
  - Globe language/region button
  - User / Staff login pill button with avatar

### 2. Footer (`footer-light`)
- **Surface**: Pure white canvas with 1px top hairline.
- **4 Editorial Columns**:
  1. *Support & Desk* (Repair tracker, WhatsApp support, phone)
  2. *Marketplace* (Brand New phones, Certified Pre-Owned, Express Repair, 0% EMI)
  3. *Trust & Quality* (50-point inspection, warranty, buyback)
  4. *MobiTrack* (About showroom, showroom visit, staff portal)
- **Bottom Legal Sub-band**:
  - Copyright line, Privacy, Terms, Sitemap.
  - Region selector (*English (IN)*) and currency picker (*₹ INR*).

---

## 🚀 Recommended Implementation Phase

| Phase | Pages | Status | Focus |
|---|---|---|---|
| **Phase 1** | Home (`/`), Shop (`/shop`), Track Repair (`/track-repair`), About (`/about`), Contact (`/contact`), Layout | **Completed** | Full Airbnb design token integration, 80px nav, search pill, 64px rating card, footer-light. |
| **Phase 2** | Device Detail (`/shop/{id}`) | **Planned** | Photo carousel, 50-point diagnostics specs, sticky reservation rail. |
| **Phase 3** | Instant Buyback Calculator (`/sell`) | **Planned** | 3-step value calculator to acquire second-hand devices from consumers. |
