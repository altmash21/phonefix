<style>
    /* ── Hide browser number spinners ── */
    .bulk-num-input::-webkit-outer-spin-button,
    .bulk-num-input::-webkit-inner-spin-button { -webkit-appearance: none !important; margin: 0 !important; }
    .bulk-num-input { -moz-appearance: textfield !important; }

    /* ── Container Layout ── */
    .restock-container {
        max-width: 1420px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* ── High-Contrast Form Inputs ── */
    .restock-input {
        width: 100%;
        color: #111827 !important;
        font-weight: 600 !important;
        background: #ffffff !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 6px !important;
        padding: 5px 8px !important;
        font-size: 12.5px !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }
    .restock-input:focus {
        outline: none !important;
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2.5px rgba(99, 102, 241, 0.15) !important;
        background: #ffffff !important;
    }
    .restock-input.cat-select {
        background-color: #F8FAFC !important;
        color: #3730A3 !important;
        font-weight: 700 !important;
        cursor: pointer;
    }
    .restock-input.num-field {
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 700 !important;
        color: #111827 !important;
    }
    .restock-input.cost-field {
        text-align: right;
        color: #0F172A !important;
    }
    .restock-input.sell-field {
        text-align: right;
        color: #047857 !important;
    }

    /* ── Ghost Add Plus Button (Matches theme, highlights on hover) ── */
    .btn-ghost-add {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #EEF2FF;
        border: 1px solid #C7D2FE;
        color: #4F46E5;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-ghost-add:hover {
        background: #4F46E5 !important;
        border-color: #4F46E5 !important;
        color: #FFFFFF !important;
        transform: scale(1.05);
    }

    /* ── Ghost Delete Cross Button (Clearly visible, turns red on hover) ── */
    .btn-ghost-delete {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #475569;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-ghost-delete:hover {
        background: #FEF2F2 !important;
        border-color: #FECACA !important;
        color: #DC2626 !important;
    }

    /* ── Stepper Component (Responsive) ── */
    .stepper-wrap {
        display: inline-flex;
        align-items: center;
        width: 100%;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        background: #ffffff;
        overflow: hidden;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .stepper-wrap:focus-within {
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
    }
    .stepper-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F8FAFC;
        color: #475569;
        font-size: 15px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        user-select: none;
        transition: background 0.1s, color 0.1s;
        padding: 0;
        flex-shrink: 0;
    }
    .stepper-btn:hover {
        background: #EDE9FE;
        color: #4F46E5;
    }
    .stepper-btn-minus { border-right: 1px solid #E2E8F0; }
    .stepper-btn-plus  { border-left:  1px solid #E2E8F0; }
    .stepper-input {
        flex: 1;
        border: none !important;
        border-radius: 0 !important;
        text-align: center !important;
        font-family: 'JetBrains Mono', monospace !important;
        font-weight: 800 !important;
        color: #111827 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        min-width: 0;
    }

    /* ── Quality OG / HD+ / Normal Inline Pill ── */
    .quality-toggle-pill {
        display: none;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 800;
        cursor: pointer;
        margin-top: 3px;
        transition: all 0.15s ease;
        user-select: none;
    }
    .quality-toggle-pill.visible {
        display: inline-flex;
    }
    .quality-toggle-pill.quality-pill-normal {
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #475569;
    }
    .quality-toggle-pill.quality-pill-hd {
        background: #FEF3C7;
        border: 1px solid #FCD34D;
        color: #92400E;
    }
    .quality-toggle-pill.quality-pill-og {
        background: #EDE9FE;
        border: 1px solid #C4B5FD;
        color: #6D28D9;
    }

    /* ── Live Row Health & Instant Warning States ── */
    .batch-item-row {
        border-left: 3.5px solid #CBD5E1;
        transition: border-color 0.18s ease, background-color 0.18s ease;
    }
    .batch-item-row.row-state-valid {
        border-left: 3.5px solid #10B981 !important;
    }
    .batch-item-row.row-state-warning {
        border-left: 3.5px solid #F59E0B !important;
        background: #FFFDF5 !important;
    }
    .batch-item-row.row-state-error {
        border-left: 3.5px solid #EF4444 !important;
        background: #FFF5F5 !important;
    }
    .batch-item-row.row-state-pristine {
        border-left: 3.5px solid #CBD5E1 !important;
    }

    .instant-row-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.1px;
    }
    .instant-row-badge.badge-err {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FCA5A5;
        animation: pulseWarning 1.6s ease-in-out infinite;
    }
    .instant-row-badge.badge-warn {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FCD34D;
    }
    .instant-row-badge.badge-ok {
        background: #DCFCE7;
        color: #15803D;
        border: 1px solid #86EFAC;
    }
    @keyframes pulseWarning {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.85; transform: scale(0.99); }
    }

    .category-combobox-menu {
        min-width: 240px !important;
        max-width: 340px !important;
        max-height: 280px !important;
    }

    /* Mobile-only elements default hidden on desktop */
    .mobile-card-top,
    .mobile-card-label,
    .mobile-card-footer,
    .mobile-sticky-footer {
        display: none !important;
    }

    /* ==========================================================================
       DESKTOP VIEWPORT (>= 880px): True Aligned Tabular Grid
       ========================================================================== */
    @media (min-width: 880px) {
        .batch-grid-row {
            display: grid;
            grid-template-columns: 160px minmax(190px, 1.8fr) 115px minmax(130px, 1.2fr) 85px 90px 90px 60px 90px 65px;
            gap: 8px;
            align-items: center !important;
        }

        .batch-table-header {
            background: #F8FAFC;
            border-bottom: 2px solid #E2E8F0;
            padding: 9px 14px;
            font-size: 10.5px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            align-items: center !important;
        }

        .batch-item-row {
            padding: 8px 14px;
            background: #FFFFFF;
            border-bottom: 1px solid #F1F5F9;
            transition: background 0.12s ease;
            align-items: center !important;
        }
        .batch-item-row:hover {
            background: #FBFBFE;
        }
        .batch-item-row:last-child {
            border-bottom: none;
        }

        .restock-input {
            height: 34px !important;
            font-size: 12.5px !important;
        }
        .stepper-wrap {
            height: 34px;
        }
        .stepper-btn {
            width: 26px;
            height: 100%;
        }
        .stepper-input {
            font-size: 12.5px !important;
        }

        /* Hide mobile card chrome on desktop */
        .mobile-card-top,
        .mobile-card-label,
        .mobile-card-footer,
        .mobile-sticky-footer,
        .mobile-add-row-bar {
            display: none !important;
        }

        .desktop-total-cell {
            text-align: right;
            padding-top: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
        }
        .desktop-total-amt {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13.5px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.2;
        }
        .desktop-action-cell {
            padding-top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 100%;
        }
    }

    .stock-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.3;
    }
    .stock-badge-pill.in-stock {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }
    .stock-badge-pill.low-stock {
        background: #FFFBEB;
        color: #92400E;
        border: 1px solid #FCD34D;
    }
    .stock-badge-pill.out-of-stock {
        background: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }
    .stock-badge-pill.new-item {
        background: #EEF2FF;
        color: #4338CA;
        border: 1px solid #C7D2FE;
    }

    /* ── Searchable Dropdown (Combobox) ── */
    .combobox-wrapper {
        position: relative;
        width: 100%;
    }
    .search-combobox-input {
        width: 100%;
        padding-right: 28px !important;
        background: #FFFFFF;
        cursor: text;
        font-size: 12px !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 6px !important;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .search-combobox-input:focus {
        border-color: #4F46E5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
        outline: none;
    }
    .combobox-clear-btn {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: #E2E8F0;
        color: #475569;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        padding: 0;
        line-height: 1;
        z-index: 2;
    }
    .combobox-clear-btn:hover {
        background: #CBD5E1;
        color: #0F172A;
    }
    .combobox-menu {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        min-width: 320px;
        max-width: 480px;
        max-height: 310px;
        overflow-y: auto;
        background: #FFFFFF;
        border: 1.5px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
        z-index: 9999;
        padding: 5px;
    }
    .combobox-item {
        padding: 7px 10px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 11.5px;
        transition: background 0.1s ease;
        border-bottom: 1px solid #F1F5F9;
    }
    .combobox-item:last-child {
        border-bottom: none;
    }
    .combobox-item:hover, .combobox-item.active {
        background: #EEF2FF;
    }
    .combobox-item-name {
        font-weight: 700;
        color: #0F172A;
    }
    .combobox-item-sub {
        font-size: 10px;
        color: #64748B;
        margin-top: 1px;
    }
    .combobox-item-stock {
        flex-shrink: 0;
        text-align: right;
    }
    .combobox-new-option {
        padding: 8px 11px;
        background: #F5F3FF;
        border: 1.5px dashed #A5B4FC;
        border-radius: 6px;
        color: #4338CA;
        font-size: 11.5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 5px;
        transition: all 0.12s ease;
    }
    .combobox-new-option:hover, .combobox-new-option.active {
        background: #EEF2FF;
        border-color: #6366F1;
        box-shadow: 0 2px 5px rgba(99, 102, 241, 0.15);
    }

    /* ==========================================================================
       MOBILE & TABLET VIEWPORT (< 880px): Clean Card Transformation & Sticky Dock
       ========================================================================== */
    @media (max-width: 879px) {
        .batch-table-header {
            display: none !important;
        }

        .batch-items-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            background: #F8FAFC;
            border-radius: 8px;
        }

        .batch-item-row {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mobile-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 8px;
        }

        .mobile-row-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #EEF2FF;
            color: #4F46E5;
            font-weight: 800;
            font-size: 10.5px;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
        }

        .mobile-card-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        /* 44px touch targets on mobile */
        .restock-input {
            height: 44px !important;
            font-size: 13.5px !important;
            padding: 8px 10px !important;
            border-radius: 7px !important;
        }
        .stepper-wrap {
            height: 44px;
            border-radius: 7px;
        }
        .stepper-btn {
            width: 44px;
            height: 44px;
            font-size: 18px;
        }
        .stepper-input {
            font-size: 14px !important;
        }
        .btn-ghost-add,
        .btn-ghost-delete {
            width: 36px;
            height: 36px;
        }

        .mobile-add-row-bar {
            display: block !important;
        }

        /* Mobile metric columns */
        .mobile-metric-grid {
            display: grid !important;
            grid-template-columns: 1.1fr 1fr 1fr 0.7fr;
            gap: 8px;
        }

        .mobile-card-footer {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            padding-top: 8px;
            border-top: 1px dashed #E2E8F0;
        }

        .desktop-total-cell {
            display: none !important;
        }
        .desktop-action-cell {
            display: none !important;
        }

        /* Ensure bottom margin so fixed dock doesn't obscure content */
        .restock-container {
            padding-bottom: 96px;
        }

        /* Mobile Sticky Footer Dock */
        .mobile-sticky-footer {
            display: flex !important;
            position: fixed;
            bottom: var(--bottom-nav-height, 58px);
            left: 0;
            right: 0;
            z-index: 960;
            background: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
            padding: 10px 14px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .desktop-summary-card {
            display: none !important;
        }
    }
</style>