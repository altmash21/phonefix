<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class AccessoriesAndRepairsCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        // 1. Ensure Categories in ms_part_categories
        $categories = [
            ['name' => 'Back Covers & Cases', 'slug' => 'back_cover', 'description' => 'Silicone, MagSafe, leather, and heavy-duty armor cases'],
            ['name' => 'Tempered Glass & Screen Guards', 'slug' => 'tempered_glass', 'description' => '11D curved, matte gaming, and privacy tempered protectors'],
            ['name' => 'Fast Chargers & Adapters', 'slug' => 'charger', 'description' => '25W to 120W GaN fast chargers, car adapters, and charging docks'],
            ['name' => 'Cables & Connectors', 'slug' => 'cable', 'description' => 'Type-C, Lightning, braided high-wattage fast charging cables'],
            ['name' => 'TWS Earbuds & Audio', 'slug' => 'audio', 'description' => 'Wireless Bluetooth earbuds, neckbands, and speakers'],
            ['name' => 'OEM Replacement Batteries', 'slug' => 'battery', 'description' => 'High-capacity zero-cycle replacement batteries with warranty'],
            ['name' => 'Original Screen Combos', 'slug' => 'folder_display', 'description' => 'OLED, Super AMOLED, and In-Cell display assemblies'],
            ['name' => 'Power Banks & Travel Accessories', 'slug' => 'power_bank', 'description' => '10000mAh - 30000mAh fast charging portable power banks'],
            ['name' => 'Camera Protectors & Holders', 'slug' => 'holder', 'description' => 'Sapphire camera lens rings, bike mounts, and car holders'],
            ['name' => 'Charging Ports & Sub-Boards', 'slug' => 'charging_port', 'description' => 'Original charging flex, sub-boards, and mic connectors'],
        ];

        foreach ($categories as $cat) {
            DB::table('ms_part_categories')->updateOrInsert(
                ['company_id' => $companyId, 'slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Curated Accessories & Spare Parts Catalog
        $items = [
            // Fast Chargers & Power
            [
                'name' => '65W GaN Dual-Port Fast Wall Charger (Type-C + USB-A)',
                'category' => 'charger',
                'brand' => 'Anker',
                'compatible_model' => 'Universal (iPhone, Samsung, MacBook, OnePlus)',
                'display_type' => 'na',
                'hsn_code' => '85044030',
                'description' => 'Ultra-compact Gallium Nitride (GaN) fast charger with PowerIQ 3.0 technology. Charges iPhone 15 to 50% in 25 minutes. Multi-protect thermal defense.',
                'unit_cost' => 1100.00,
                'selling_price' => 1899.00,
                'stock_qty' => 28,
                'min_stock_alert' => 5,
            ],
            [
                'name' => '25W Type-C Super Fast Power Adapter (PD 3.0)',
                'category' => 'charger',
                'brand' => 'Samsung',
                'compatible_model' => 'Galaxy S24 / S23 / A55 / A35 / Z Flip',
                'display_type' => 'na',
                'hsn_code' => '85044030',
                'description' => 'Official PPS fast charging brick providing optimized 25W charging speed for modern Galaxy smartphones with surge protection.',
                'unit_cost' => 650.00,
                'selling_price' => 1299.00,
                'stock_qty' => 35,
                'min_stock_alert' => 5,
            ],
            [
                'name' => '20W USB-C Power Adapter (Fast Charging)',
                'category' => 'charger',
                'brand' => 'Apple',
                'compatible_model' => 'iPhone 15 / 14 / 13 / 12 / iPad',
                'display_type' => 'na',
                'hsn_code' => '85044030',
                'description' => 'Genuine Apple 20W Power Delivery adapter. Fast charges iPhone from 0 to 50% in 30 minutes with intelligent current regulation.',
                'unit_cost' => 950.00,
                'selling_price' => 1690.00,
                'stock_qty' => 22,
                'min_stock_alert' => 4,
            ],

            // Cables
            [
                'name' => '100W Braided USB-C to USB-C Heavy Duty Cable (1.5m)',
                'category' => 'cable',
                'brand' => 'Baseus',
                'compatible_model' => 'Universal Type-C Devices',
                'display_type' => 'na',
                'hsn_code' => '85444299',
                'description' => 'Zinc-alloy reinforced braided cable with built-in E-Marker chip supporting 100W fast charging and 480Mbps data transfer speed.',
                'unit_cost' => 220.00,
                'selling_price' => 499.00,
                'stock_qty' => 50,
                'min_stock_alert' => 10,
            ],
            [
                'name' => 'MFi Certified Braided Lightning to Type-C Cable (1.2m)',
                'category' => 'cable',
                'brand' => 'Belkin',
                'compatible_model' => 'iPhone 14 / 13 / 12 / 11 / SE',
                'display_type' => 'na',
                'hsn_code' => '85444299',
                'description' => 'Apple MFi Certified silicone-jacketed durable cable rated for 25,000+ bends. Supports Power Delivery fast charging.',
                'unit_cost' => 380.00,
                'selling_price' => 799.00,
                'stock_qty' => 30,
                'min_stock_alert' => 6,
            ],

            // Back Covers & Cases
            [
                'name' => 'MagSafe Crystal Clear Shockproof Protective Case',
                'category' => 'back_cover',
                'brand' => 'Spigen',
                'compatible_model' => 'iPhone 15 Pro / 15 Pro Max',
                'display_type' => 'na',
                'hsn_code' => '39269099',
                'description' => 'Military-grade air cushion corner technology with 38 embedded neodymium N52 magnets for rock-solid MagSafe wireless charging alignment.',
                'unit_cost' => 450.00,
                'selling_price' => 999.00,
                'stock_qty' => 40,
                'min_stock_alert' => 8,
            ],
            [
                'name' => 'Liquid Silicone Soft Touch Case with Microfiber Lining',
                'category' => 'back_cover',
                'brand' => 'PhoneFix Premium',
                'compatible_model' => 'iPhone 13 / 14 / 15 Series',
                'display_type' => 'na',
                'hsn_code' => '39269099',
                'description' => 'Silky smooth anti-fingerprint silicone with interior soft microfiber cushion preventing scratches on back glass. Raised 1.5mm camera lips.',
                'unit_cost' => 160.00,
                'selling_price' => 399.00,
                'stock_qty' => 65,
                'min_stock_alert' => 12,
            ],
            [
                'name' => 'Carbon Fiber Rugged Armor Case with Kickstand',
                'category' => 'back_cover',
                'brand' => 'Ringke',
                'compatible_model' => 'Samsung Galaxy S24 Ultra / S23 Ultra',
                'display_type' => 'na',
                'hsn_code' => '39269099',
                'description' => 'Dual-layer TPU and polycarbonate hybrid shell with drop absorption matrix and built-in handsfree landscape metal kickstand.',
                'unit_cost' => 380.00,
                'selling_price' => 799.00,
                'stock_qty' => 25,
                'min_stock_alert' => 5,
            ],
            [
                'name' => 'Matte Frosted Translucent Bumper Cover (Smoke Black)',
                'category' => 'back_cover',
                'brand' => 'Nillkin',
                'compatible_model' => 'OnePlus 12 / 12R / Nord CE 4',
                'display_type' => 'na',
                'hsn_code' => '39269099',
                'description' => 'Tactile non-slip grip textured bumper with anti-yellowing matte backplate. Precise cutouts and responsive tactile buttons.',
                'unit_cost' => 240.00,
                'selling_price' => 549.00,
                'stock_qty' => 32,
                'min_stock_alert' => 6,
            ],

            // Tempered Glass & Protectors
            [
                'name' => '11D Edge-to-Edge Curved Full Glue Tempered Glass',
                'category' => 'tempered_glass',
                'brand' => 'Gorilla Shield',
                'compatible_model' => 'iPhone 15 / 15 Plus / 15 Pro',
                'display_type' => 'na',
                'hsn_code' => '70071900',
                'description' => 'Japanese Asahi 9H dual-tempered glass with oleophobic electroplated coating for smudge-free swipe gestures and shatter resistance.',
                'unit_cost' => 60.00,
                'selling_price' => 299.00,
                'stock_qty' => 110,
                'min_stock_alert' => 20,
            ],
            [
                'name' => '28° Anti-Peep Privacy Tempered Glass Shield',
                'category' => 'tempered_glass',
                'brand' => 'Privacy Guard',
                'compatible_model' => 'iPhone 13 / 14 / 15 Series',
                'display_type' => 'na',
                'hsn_code' => '70071900',
                'description' => 'Micro-louver technology blocks visibility from angles greater than 28 degrees. Perfect for privacy in metros, public places, and offices.',
                'unit_cost' => 85.00,
                'selling_price' => 399.00,
                'stock_qty' => 75,
                'min_stock_alert' => 15,
            ],
            [
                'name' => 'UV Liquid Curved Glass Protector for Curved Screens',
                'category' => 'tempered_glass',
                'brand' => 'UV Optics',
                'compatible_model' => 'Samsung S24 Ultra / S23 / OnePlus 12',
                'display_type' => 'na',
                'hsn_code' => '70071900',
                'description' => 'Loca liquid glue cured via UV light for bubble-free adhesion on edge-curved AMOLED panels. Ultrasonic fingerprint sensor compatible.',
                'unit_cost' => 120.00,
                'selling_price' => 599.00,
                'stock_qty' => 40,
                'min_stock_alert' => 8,
            ],
            [
                'name' => 'Sapphire Metal Ring Camera Lens Protector (Set of 3)',
                'category' => 'tempered_glass',
                'brand' => 'LensGuard',
                'compatible_model' => 'iPhone 15 Pro / 15 Pro Max',
                'display_type' => 'na',
                'hsn_code' => '70071900',
                'description' => 'Individually fitted aviation aluminum rings with AR anti-reflective coated optical glass. Zero ghosting on flash photography.',
                'unit_cost' => 80.00,
                'selling_price' => 349.00,
                'stock_qty' => 60,
                'min_stock_alert' => 10,
            ],

            // Audio & Earbuds
            [
                'name' => 'Airdopes Active ANC Wireless TWS Earbuds (50h Playtime)',
                'category' => 'audio',
                'brand' => 'boAt',
                'compatible_model' => 'All Bluetooth Smartphones',
                'display_type' => 'na',
                'hsn_code' => '85183000',
                'description' => 'Active Noise Cancellation with Quad Mics and ENx tech. 13mm drivers delivering punchy bass. ASAP charge: 10 mins = 100 mins playtime.',
                'unit_cost' => 1150.00,
                'selling_price' => 1799.00,
                'stock_qty' => 18,
                'min_stock_alert' => 4,
            ],
            [
                'name' => 'Buds Pro Wireless Bluetooth Earbuds (Spatial Audio)',
                'category' => 'audio',
                'brand' => 'OnePlus',
                'compatible_model' => 'All Bluetooth Smartphones',
                'display_type' => 'na',
                'hsn_code' => '85183000',
                'description' => 'Smart adaptive noise cancellation up to 48dB with dual drivers tuned by Dynaudio and LHDC 5.0 Hi-Res audio codec.',
                'unit_cost' => 3200.00,
                'selling_price' => 4999.00,
                'stock_qty' => 10,
                'min_stock_alert' => 2,
            ],
            [
                'name' => 'Type-C Hi-Fi Digital Audio DAC Earphones with Mic',
                'category' => 'audio',
                'brand' => 'Realme',
                'compatible_model' => 'Type-C Audio Smartphones',
                'display_type' => 'na',
                'hsn_code' => '85183000',
                'description' => 'Built-in real DAC chip delivering loss-free 24-bit/96kHz digital sound with tangle-free braided cord and inline volume controls.',
                'unit_cost' => 220.00,
                'selling_price' => 499.00,
                'stock_qty' => 45,
                'min_stock_alert' => 8,
            ],

            // Power Banks & Mounts
            [
                'name' => '20000mAh 22.5W Two-Way Fast Charge Power Bank',
                'category' => 'power_bank',
                'brand' => 'Mi / Xiaomi',
                'compatible_model' => 'Universal',
                'display_type' => 'na',
                'hsn_code' => '85076000',
                'description' => 'High density lithium polymer cell with triple output ports (2x USB-A, 1x Type-C) supporting QC 3.0 and Power Delivery protocols.',
                'unit_cost' => 1250.00,
                'selling_price' => 1999.00,
                'stock_qty' => 16,
                'min_stock_alert' => 3,
            ],
            [
                'name' => '15W MagSafe Wireless Power Bank with Ring Stand (10000mAh)',
                'category' => 'power_bank',
                'brand' => 'Ambrane',
                'compatible_model' => 'iPhone 12-15 Series / Qi Phones',
                'display_type' => 'na',
                'hsn_code' => '85076000',
                'description' => 'Magnetic snap-and-charge wireless battery pack with foldable zinc kickstand for tabletop movie watching while charging.',
                'unit_cost' => 980.00,
                'selling_price' => 1699.00,
                'stock_qty' => 14,
                'min_stock_alert' => 3,
            ],
            [
                'name' => '360° Rotating Heavy-Duty Car Dashboard Phone Mount',
                'category' => 'holder',
                'brand' => 'Portronics',
                'compatible_model' => 'Universal 4.7 to 7.0 Inch Phones',
                'display_type' => 'na',
                'hsn_code' => '39269099',
                'description' => 'Industrial silicone suction base with expandable telescopic arm and quick one-tap auto clamp locking mechanism.',
                'unit_cost' => 210.00,
                'selling_price' => 499.00,
                'stock_qty' => 35,
                'min_stock_alert' => 5,
            ],

            // OEM Repair Spare Parts
            [
                'name' => 'iPhone 13 OLED Display Screen Assembly (Super Retina XDR)',
                'category' => 'folder_display',
                'brand' => 'Apple',
                'compatible_model' => 'iPhone 13 / 13 Pro',
                'display_type' => 'oled',
                'hsn_code' => '85177090',
                'description' => 'Grade A+ OLED assembly with true tone reprogrammable EEPROM IC, high brightness HDR, and 120Hz touch digitizer grid.',
                'unit_cost' => 6800.00,
                'selling_price' => 10500.00,
                'stock_qty' => 8,
                'min_stock_alert' => 2,
            ],
            [
                'name' => 'Samsung S24 Dynamic AMOLED 2X Display Combo',
                'category' => 'folder_display',
                'brand' => 'Samsung',
                'compatible_model' => 'Galaxy S24 5G (SM-S921B)',
                'display_type' => 'original_oem',
                'hsn_code' => '85177090',
                'description' => 'Original factory service pack display with frame pre-installed. Zero dead pixels, genuine LTPO variable 1-120Hz refresh rate.',
                'unit_cost' => 8500.00,
                'selling_price' => 12999.00,
                'stock_qty' => 5,
                'min_stock_alert' => 2,
            ],
            [
                'name' => 'Redmi Note 13 Pro In-Cell Display Screen Combo',
                'category' => 'folder_display',
                'brand' => 'Xiaomi',
                'compatible_model' => 'Redmi Note 13 Pro 5G',
                'display_type' => 'in_cell',
                'hsn_code' => '85177090',
                'description' => 'High-luminance In-Cell FHD+ panel replacement combo. Accurate color calibration and smooth multitouch gesture recognition.',
                'unit_cost' => 1650.00,
                'selling_price' => 2999.00,
                'stock_qty' => 12,
                'min_stock_alert' => 3,
            ],
            [
                'name' => 'iPhone 12 High Capacity OEM Battery 2815mAh (Zero Cycle)',
                'category' => 'battery',
                'brand' => 'Apple',
                'compatible_model' => 'iPhone 12 / 12 Pro',
                'display_type' => 'na',
                'hsn_code' => '85076000',
                'description' => 'Original Texas Instruments battery management chip. Retains 100% Battery Health reading on iOS diagnostics with 6-month warranty.',
                'unit_cost' => 1100.00,
                'selling_price' => 2200.00,
                'stock_qty' => 15,
                'min_stock_alert' => 4,
            ],
            [
                'name' => 'Samsung 5000mAh Replacement Battery (Galaxy M/F Series)',
                'category' => 'battery',
                'brand' => 'Samsung',
                'compatible_model' => 'Galaxy M33 / M34 / M53 / F34',
                'display_type' => 'na',
                'hsn_code' => '85076000',
                'description' => 'High endurance lithium-ion battery cell with integrated thermal fuse. Factory matched voltage with 6-month replacement warranty.',
                'unit_cost' => 850.00,
                'selling_price' => 1650.00,
                'stock_qty' => 14,
                'min_stock_alert' => 3,
            ],
            [
                'name' => 'Type-C Charging Sub-Board with Mic IC (Galaxy M33)',
                'category' => 'charging_port',
                'brand' => 'Samsung',
                'compatible_model' => 'Galaxy M33 5G',
                'display_type' => 'na',
                'hsn_code' => '85177090',
                'description' => 'Original bottom charging PCB board with noise cancellation microphone and fast charging power flex connector.',
                'unit_cost' => 220.00,
                'selling_price' => 650.00,
                'stock_qty' => 10,
                'min_stock_alert' => 2,
            ],
        ];

        foreach ($items as $item) {
            DB::table('ms_parts_inventory')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'name' => $item['name'],
                ],
                array_merge($item, [
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 3. Demo Customer & Repair Tickets for Live Tracking Demonstration
        $customer = DB::table('ms_customers')->where('phone', '9876543210')->first();
        if (!$customer) {
            $customerId = DB::table('ms_customers')->insertGetId([
                'company_id' => $companyId,
                'name' => 'Rahul Sharma',
                'phone' => '9876543210',
                'email' => 'rahul.sharma@example.com',
                'state_code' => '09',
                'address' => 'Shop 4, Linking Road, Bandra West, Mumbai',
                'udhari_balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $customerId = $customer->id;
        }

        $tickets = [
            [
                'ticket_number' => 'REP-2026-0042',
                'brand' => 'Apple',
                'model' => 'iPhone 13',
                'imei_serial' => '359482019482011',
                'passcode_encrypted' => Crypt::encryptString('258014'),
                'reported_faults' => 'Display cracked after drop on concrete. Green vertical line on right side. Touch working.',
                'physical_condition' => 'Minor scuff on top right bezel. Camera lenses clear.',
                'status' => 'in_repair',
                'estimated_cost' => 10500.00,
                'labor_charge' => 1000.00,
                'parts_cost' => 9500.00,
                'total_amount' => 10500.00,
                'advance_paid' => 2000.00,
                'balance_due' => 8500.00,
                'received_at' => now()->subHours(4),
            ],
            [
                'ticket_number' => 'REP-2026-0038',
                'brand' => 'Samsung',
                'model' => 'Galaxy S23 Ultra',
                'imei_serial' => '352849102948201',
                'passcode_encrypted' => Crypt::encryptString('123456'),
                'reported_faults' => 'Battery draining rapidly (under 3 hours). Overheating while charging on 45W.',
                'physical_condition' => 'Excellent cosmetic condition. No dents.',
                'status' => 'ready',
                'estimated_cost' => 2800.00,
                'labor_charge' => 600.00,
                'parts_cost' => 2200.00,
                'total_amount' => 2800.00,
                'advance_paid' => 2800.00,
                'balance_due' => 0.00,
                'received_at' => now()->subHours(24),
                'completed_at' => now()->subHours(2),
            ],
            [
                'ticket_number' => 'REP-2026-0045',
                'brand' => 'OnePlus',
                'model' => 'OnePlus 11 5G',
                'imei_serial' => '864810294810293',
                'passcode_encrypted' => null,
                'reported_faults' => 'Charging port loose. Type-C cable disconnects when moved. Slow charging only.',
                'physical_condition' => 'Clean device.',
                'status' => 'received',
                'estimated_cost' => 1200.00,
                'labor_charge' => 500.00,
                'parts_cost' => 700.00,
                'total_amount' => 1200.00,
                'advance_paid' => 500.00,
                'balance_due' => 700.00,
                'received_at' => now()->subMinutes(45),
            ],
        ];

        foreach ($tickets as $t) {
            DB::table('ms_repair_tickets')->updateOrInsert(
                ['ticket_number' => $t['ticket_number']],
                array_merge($t, [
                    'company_id' => $companyId,
                    'customer_id' => $customerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
