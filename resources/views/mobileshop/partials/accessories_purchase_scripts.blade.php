<script>
    /* ── HTML Sanitizer ── */
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    window.escapeHtml = escapeHtml;

    /* ── Local Currency Formatter (₹9,585.00) ── */
    function formatCurrency(num) {
        const val = parseFloat(num) || 0;
        return '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const suppliersData = {!! json_encode($suppliers ?? []) !!};
    const rawCatalogParts = {!! json_encode($parts ?? []) !!};
    const catalogParts = Array.isArray(rawCatalogParts) ? rawCatalogParts : Object.values(rawCatalogParts || {});
    const rawCatalogCategories = {!! json_encode($categories ?? []) !!};
    let catalogCategories = Array.isArray(rawCatalogCategories) ? rawCatalogCategories : Object.values(rawCatalogCategories || {});
    if (!catalogCategories || catalogCategories.length === 0) {
        catalogCategories = [
            { slug: 'folder_display', name: 'Display Screen / Folder' },
            { slug: 'front_glass', name: 'Front Glass' },
            { slug: 'charging_port', name: 'Charging Pin / Port' },
            { slug: 'ic_chip', name: 'IC / Motherboard Chip' },
            { slug: 'battery', name: 'Battery' },
            { slug: 'back_panel', name: 'Back Panel / Housing Glass' },
            { slug: 'back_cover', name: 'Back Cover & Cases 🎁' },
            { slug: 'tempered_glass', name: 'Tempered Glass 🎁' },
            { slug: 'charger_cable', name: 'Chargers & Cables 🎁' },
            { slug: 'earphones_audio', name: 'Earphones & Audio 🎁' },
            { slug: 'camera_module', name: 'Camera Module' },
            { slug: 'general_accessory', name: 'General Accessories 🎁' }
        ];
    }

    function formatCategoryLabel(slug) {
        if (!slug) return '';
        const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
        const found = cats.find(c => c && (c.slug === slug || canonicalizeCategory(c.slug) === slug));
        if (found) return found.name || found.slug;
        return slug.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    window.formatCategoryLabel = formatCategoryLabel;

    function getSelectedSupplier() {
        const input = document.getElementById('bulkSupplierName');
        if (!input) return null;
        const name = input.value.trim().toLowerCase();
        if (!name) return null;
        const list = Array.isArray(suppliersData) ? suppliersData : Object.values(suppliersData || {});
        return list.find(s => s && s.name && s.name.toLowerCase() === name);
    }
    window.getSelectedSupplier = getSelectedSupplier;

    function onSupplierSelected() {
        const s = getSelectedSupplier();
        const infoBox = document.getElementById('supplierLedgerInfo');
        const pastDueSummary = document.getElementById('supplierDueSummaryBlock');
        const pastDueLbl = document.getElementById('lblSupplierPastDue');
        const clearAllBtn = document.getElementById('btnQuickClearAll');

        if (s) {
            const outBal = parseFloat(s.outstanding_balance) || 0;
            const advBal = parseFloat(s.advance_balance) || 0;

            if (infoBox) {
                infoBox.style.display = 'block';
                let html = `<strong>${escapeHtml(s.name)}</strong> `;
                if (s.phone) html += `(${escapeHtml(s.phone)}) &bull; `;
                if (outBal > 0) {
                    html += `<span style="color:#DC2626; font-weight:800;">Previous Balance Due: ₹${outBal.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>`;
                } else {
                    html += `<span style="color:#059669; font-weight:700;">✅ Clean Ledger (No Past Due)</span>`;
                }
                if (advBal > 0) {
                    html += ` &bull; <span style="color:#4F46E5; font-weight:700;">Prepaid Advance: ₹${advBal.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>`;
                }
                infoBox.innerHTML = html;
            }

            if (outBal > 0) {
                if (pastDueSummary) pastDueSummary.style.display = 'block';
                if (pastDueLbl) pastDueLbl.textContent = formatCurrency(outBal);
                if (clearAllBtn) clearAllBtn.style.display = 'inline-flex';
            } else {
                if (pastDueSummary) pastDueSummary.style.display = 'none';
                if (clearAllBtn) clearAllBtn.style.display = 'none';
            }
        } else {
            if (infoBox) infoBox.style.display = 'none';
            if (pastDueSummary) pastDueSummary.style.display = 'none';
            if (clearAllBtn) clearAllBtn.style.display = 'none';
        }
        updateBulkSummary();
    }
    window.onSupplierSelected = onSupplierSelected;

    let bulkPriceMode = 'auto'; // 'auto' or 'custom'
    let customBillAmount = null;

    function setBulkPriceMode(mode) {
        bulkPriceMode = mode;
        const btnAuto = document.getElementById('btnPriceModeAuto');
        const btnCustom = document.getElementById('btnPriceModeCustom');
        const wrap = document.getElementById('bulkCustomPriceWrap');
        const customInput = document.getElementById('bulkCustomTotalCost');

        if (mode === 'custom') {
            if (btnAuto) { btnAuto.style.background = '#fff'; btnAuto.style.color = '#475569'; btnAuto.style.borderColor = '#CBD5E1'; }
            if (btnCustom) { btnCustom.style.background = '#EEF2FF'; btnCustom.style.color = '#4F46E5'; btnCustom.style.borderColor = '#C7D2FE'; }
            if (wrap) wrap.style.display = 'inline-flex';
            if (customInput && (!customInput.value || parseFloat(customInput.value) <= 0)) {
                const autoTot = getAutoTotalCost();
                customInput.value = autoTot > 0 ? autoTot.toFixed(2) : '';
                customBillAmount = autoTot;
            }
        } else {
            if (btnAuto) { btnAuto.style.background = '#EEF2FF'; btnAuto.style.color = '#4F46E5'; btnAuto.style.borderColor = '#C7D2FE'; }
            if (btnCustom) { btnCustom.style.background = '#fff'; btnCustom.style.color = '#475569'; btnCustom.style.borderColor = '#CBD5E1'; }
            if (wrap) wrap.style.display = 'none';
            if (customInput) customInput.value = '';
            customBillAmount = null;
        }
        updateBulkSummary();
    }
    window.setBulkPriceMode = setBulkPriceMode;

    function onCustomPriceChanged(val) {
        const parsed = parseFloat(val);
        customBillAmount = (!isNaN(parsed) && parsed >= 0) ? parsed : null;
        updateBulkSummary();
    }
    window.onCustomPriceChanged = onCustomPriceChanged;

    function resetCustomPriceToAuto() {
        const autoTot = getAutoTotalCost();
        const customInput = document.getElementById('bulkCustomTotalCost');
        if (customInput) customInput.value = autoTot > 0 ? autoTot.toFixed(2) : '';
        customBillAmount = autoTot;
        updateBulkSummary();
    }
    window.resetCustomPriceToAuto = resetCustomPriceToAuto;

    function getAutoTotalCost() {
        let totalCost = 0.0;
        document.querySelectorAll('#bulkTableBody .batch-item-row').forEach(r => {
            const qtyInput = r.querySelector('input[name*="[qty]"]');
            const costInput = r.querySelector('input[name*="[unit_cost]"]');
            if (qtyInput && costInput) {
                const q = parseInt(qtyInput.value) || 0;
                const c = parseFloat(costInput.value) || 0;
                totalCost += (q * c);
            }
        });
        return totalCost;
    }
    window.getAutoTotalCost = getAutoTotalCost;

    function getCurrentBatchTotalCost() {
        if (bulkPriceMode === 'custom' && customBillAmount !== null) {
            return customBillAmount;
        }
        return getAutoTotalCost();
    }
    window.getCurrentBatchTotalCost = getCurrentBatchTotalCost;

    function setBulkAmountPaid(mode) {
        const totalCost = getCurrentBatchTotalCost();
        const s = getSelectedSupplier();
        const outBal = s ? (parseFloat(s.outstanding_balance) || 0) : 0;
        const input = document.getElementById('bulkAmountPaid');
        if (!input) return;

        if (mode === 'invoice') {
            input.value = totalCost > 0 ? totalCost.toFixed(2) : '';
        } else if (mode === 'clear_all') {
            const totalWithDue = totalCost + outBal;
            input.value = totalWithDue > 0 ? totalWithDue.toFixed(2) : '';
        } else if (mode === 'zero') {
            input.value = '0.00';
        }
        onAmountPaidChanged();
    }
    window.setBulkAmountPaid = setBulkAmountPaid;

    function onAmountPaidChanged() {
        updateBulkSummary();
    }
    window.onAmountPaidChanged = onAmountPaidChanged;

    /* ── Canonicalize category names & aliases to standard DB slugs ── */
    function canonicalizeCategory(cat, name = '') {
        const text = (String(cat || '') + ' ' + String(name || '')).toLowerCase().trim();
        const c = String(cat || '').toLowerCase().trim().replace(/[-\s]/g, '_');

        if (!text && !c) {
            return '';
        }

        if (/folder|display|screen|lcd|oled|tft|combo|touch\s*display/.test(text) || c === 'display_folder' || c === 'folder_display') {
            return 'folder_display';
        }
        if (/front\s*glass|touch\s*glass|oca/.test(text) || c === 'front_glass') {
            return 'front_glass';
        }
        if (/pin|charging\s*port|connector|charging\s*jack|cc\s*board|sub\s*board/.test(text) || c === 'charging_pin' || c === 'charging_port') {
            return 'charging_port';
        }
        if (/ic\b|motherboard|power\s*ic|charging\s*ic|wtr|pmic|cpu\s*ic|chip/.test(text) || c === 'ic_motherboard' || c === 'ic_chip') {
            return 'ic_chip';
        }
        if (/battery|cell|mah\b/.test(text) || c === 'battery') {
            return 'battery';
        }
        if (/back\s*panel|housing|housing\s*glass|body/.test(text) || c === 'back_panel') {
            return 'back_panel';
        }
        if (/cover|case|smoke|bumper|pouch|silicone|leather|matte\s*case/.test(text) || c === 'back_cover_case' || c === 'back_cover') {
            return 'back_cover';
        }
        if (/tempered|11d|9d|21d|uv\s*glass|d\+|screen\s*guard|screen\s*protector/.test(text) || c === 'tempered_glass') {
            return 'tempered_glass';
        }
        if (/charger|cable|adapter|type-c|lightning|micro\s*usb|braided|fast\s*charg|power\s*bank|pd\s*cable/.test(text) || c === 'charger' || c === 'cable' || c === 'charger_cable') {
            return 'charger_cable';
        }
        if (/earphone|headphone|audio|buds|airpod|neckband|tws|bluetooth\s*headset|speaker|mic\b|aux|sound/.test(text) || c === 'audio' || c === 'earphones_audio') {
            return 'earphones_audio';
        }
        if (/camera|lens|camera\s*glass|camera\s*module/.test(text) || c === 'camera_module') {
            return 'camera_module';
        }
        return c || '';
    }
    window.canonicalizeCategory = canonicalizeCategory;

    /* ── Auto-detect brand from item name text or model code ── */
    function guessBrand(name) {
        const n = (name || '').toLowerCase();
        if (/iphone|apple/.test(n))                                                    return 'Apple';
        if (/galaxy|samsung|\bs\d{2}\b|\ba\d{2}\b|\bm\d{2}\b|\bf\d{2}\b/.test(n))     return 'Samsung';
        if (/redmi|poco|xiaomi|\bnote\s*\d/.test(n))                                   return 'Xiaomi';
        if (/vivo|iqoo|\by\d{2}\b|\bv\d{2}\b|\bt\d\b/.test(n))                        return 'Vivo';
        if (/oppo|reno|\ba\d{2}\b/.test(n))                                            return 'Oppo';
        if (/realme|narzo|\bc\d{2}\b|\bgt\b/.test(n))                                  return 'Realme';
        if (/oneplus|\bnord\b/.test(n))                                                return 'OnePlus';
        if (/moto|motorola|\bg\d{2}\b|\be\d{2}\b/.test(n))                             return 'Motorola';
        if (/pixel|google/.test(n))                                                    return 'Google';
        if (/infinix|\bx\d{3,4}\b|\bhot\s*\d|\bsmart\s*\d|\bzero\s*\d/.test(n))       return 'Infinix';
        if (/tecno|pova|camon|\bspark\s*\d/.test(n))                                   return 'Tecno';
        if (/itel|\ba\d{2}\b|\bp\d{2}\b/.test(n))                                      return 'Itel';
        if (/nokia/.test(n))                                                           return 'Nokia';
        return 'Universal';
    }
    window.guessBrand = guessBrand;

    /* ── Ensure Brand Name is included in Item Name field ── */
    function ensureBrandInItemName(name, brand) {
        if (!name) return '';
        let cleanName = String(name).trim();
        let targetBrand = brand;

        if (!targetBrand || targetBrand === 'Universal' || targetBrand.toLowerCase() === 'other') {
            const detected = guessBrand(cleanName);
            if (detected && detected !== 'Universal') {
                targetBrand = detected;
            } else {
                return cleanName;
            }
        }

        const escBrand = targetBrand.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        if (new RegExp('\\b' + escBrand + '\\b', 'i').test(cleanName)) {
            return cleanName;
        }

        // Special brand aliases
        const bLower = targetBrand.toLowerCase();
        if (bLower === 'apple' && /\biphone\b/i.test(cleanName)) return cleanName;
        if (bLower === 'xiaomi' && /\b(?:redmi|poco|mi)\b/i.test(cleanName)) return cleanName;
        if (bLower === 'vivo' && /\biqoo\b/i.test(cleanName)) return cleanName;
        if (bLower === 'oppo' && /\breno\b/i.test(cleanName)) return cleanName;
        if (bLower === 'realme' && /\bnarzo\b/i.test(cleanName)) return cleanName;

        return `${targetBrand} ${cleanName}`.trim();
    }
    window.ensureBrandInItemName = ensureBrandInItemName;

    /* ── Clean redundant category terms from product name ── */
    function cleanItemNameForCategory(name, cat, brand) {
        if (!name) return '';
        return String(name).trim();
    }
    window.cleanItemNameForCategory = cleanItemNameForCategory;

    let bulkRowIndex = 0;

    /* ── Focus and Start Typing Brand New Item ── */
    function focusAndTypeNew(idx) {
        const input = document.getElementById(`searchInput_${idx}`);
        if (input) {
            input.value = '';
            input.focus();
        }
        document.getElementById(`name_${idx}`).value = '';
        document.getElementById(`partId_${idx}`).value = '';
        const clearBtn = document.getElementById(`clearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = 'none';
        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'none';
        renderStockBadge(idx, 0, true);
        updateBulkRowTotal(idx);
    }
    window.focusAndTypeNew = focusAndTypeNew;

    /* ── Render Stock Badge Under Input ── */
    function renderStockBadge(idx, stockQty, isNew = false) {
        const badgeEl = document.getElementById(`stockBadge_${idx}`);
        if (!badgeEl) return;

        const qtyInput = document.getElementById(`qty_${idx}`);
        const currentQty = qtyInput ? parseInt(qtyInput.value || 0) : 0;
        const alertThreshold = parseInt(document.getElementById(`alert_${idx}`)?.value || 3);

        if (isNew) {
            if (currentQty <= 0) {
                badgeEl.innerHTML = `<span class="stock-badge-pill new-item" title="First time in inventory - initial stock is 0 (Low Stock Alert)">✨ New Item (First time in stock) &bull; <strong style="color:#DC2626;">⚠️ 0 Qty (Low Stock)</strong></span>`;
            } else if (currentQty <= alertThreshold) {
                badgeEl.innerHTML = `<span class="stock-badge-pill new-item" title="First time in inventory - initial stock is below alert threshold">✨ New Item (First time in stock) &bull; <strong style="color:#D97706;">⚠️ Low Stock (${currentQty})</strong></span>`;
            } else {
                badgeEl.innerHTML = `<span class="stock-badge-pill new-item" title="First time in inventory - initial stock entry">✨ New Item (First time in stock)</span>`;
            }
            badgeEl.style.display = 'inline-flex';
            return;
        }

        if (stockQty === null || stockQty === undefined) {
            badgeEl.innerHTML = '';
            badgeEl.style.display = 'none';
            return;
        }

        const qty = parseInt(stockQty) || 0;
        let badgeClass = 'in-stock';
        let icon = '📦';
        let text = `${qty} in stock`;

        if (qty <= 0) {
            badgeClass = 'out-of-stock';
            icon = '⚠️';
            text = `0 in stock (Low Stock)`;
        } else if (qty <= alertThreshold) {
            badgeClass = 'low-stock';
            icon = '⚠️';
            text = `Low stock: ${qty} pcs`;
        }

        const currentPartId = document.getElementById(`partId_${idx}`)?.value;
        let editBtnHtml = '';
        if (currentPartId) {
            editBtnHtml = ` <button type="button" onclick="openQuickEditStockModal(${idx}, ${currentPartId})" style="margin-left:6px; background:#EEF2FF; border:1px solid #C7D2FE; color:#4338CA; border-radius:4px; font-size:10px; font-weight:700; padding:1px 6px; cursor:pointer; vertical-align:middle;" title="Edit baseline inventory stock count or item details">✏️ Edit Stock</button>`;
        }

        badgeEl.innerHTML = `<span class="stock-badge-pill ${badgeClass}">${icon} <strong>${text}</strong></span>${editBtnHtml}`;
        badgeEl.style.display = 'inline-flex';
    }

    /* ── Quick Edit Stock Modal Functions ── */
    let quickEditOriginalStock = 0;

    function openQuickEditStockModal(idx, partId) {
        if (!partId) {
            partId = document.getElementById(`partId_${idx}`)?.value;
        }
        if (!partId) return;

        const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
        const p = parts.find(item => item && String(item.id) === String(partId));
        if (!p) return;

        document.getElementById('quickEditRowIdx').value = idx;
        document.getElementById('quickEditPartId').value = p.id;
        document.getElementById('quickEditName').value = p.name || '';
        document.getElementById('quickEditCategory').value = canonicalizeCategory(p.category, p.name) || 'general_accessory';
        document.getElementById('quickEditModel').value = p.compatible_model || '';

        quickEditOriginalStock = parseInt(p.stock_qty) || 0;
        document.getElementById('quickEditStockQty').value = quickEditOriginalStock;
        document.getElementById('quickEditCost').value = parseFloat(p.unit_cost || 0).toFixed(2);
        document.getElementById('quickEditPrice').value = parseFloat(p.selling_price || 0).toFixed(2);
        document.getElementById('quickEditReason').value = '';

        const dtSelect = document.getElementById('quickEditDisplayType');
        if (dtSelect) {
            const rawDt = (p.display_type || 'na').toLowerCase();
            if (rawDt === 'hd+' || rawDt === 'hd_plus') {
                dtSelect.value = 'hd_plus';
            } else if (rawDt === 'og' || rawDt === 'original_oem') {
                dtSelect.value = 'original_oem';
            } else {
                dtSelect.value = p.display_type || 'na';
            }
        }

        document.getElementById('quickEditModalTitle').textContent = `Edit Stock — ${p.name}`;
        const errBox = document.getElementById('quickEditError');
        if (errBox) { errBox.style.display = 'none'; errBox.textContent = ''; }

        onQuickEditStockChanged();

        const modal = document.getElementById('quickEditStockModal');
        if (modal) {
            modal.style.display = 'flex';
            if (window.lucide) window.lucide.createIcons();
        }
    }
    window.openQuickEditStockModal = openQuickEditStockModal;

    function closeQuickEditStockModal() {
        const modal = document.getElementById('quickEditStockModal');
        if (modal) modal.style.display = 'none';
    }
    window.closeQuickEditStockModal = closeQuickEditStockModal;

    function adjustQuickEditStock(delta) {
        const inEl = document.getElementById('quickEditStockQty');
        if (!inEl) return;
        const current = parseInt(inEl.value) || 0;
        inEl.value = Math.max(0, current + delta);
        onQuickEditStockChanged();
    }
    window.adjustQuickEditStock = adjustQuickEditStock;

    function onQuickEditStockChanged() {
        const inEl = document.getElementById('quickEditStockQty');
        const badge = document.getElementById('quickEditStockDiffBadge');
        if (!inEl || !badge) return;

        const val = parseInt(inEl.value) || 0;
        const diff = val - quickEditOriginalStock;

        if (diff === 0) {
            badge.textContent = `Baseline: ${quickEditOriginalStock} units (no change)`;
            badge.style.color = '#15803D';
            badge.style.background = '#DCFCE7';
        } else if (diff > 0) {
            badge.textContent = `+${diff} units (Inward to ${val})`;
            badge.style.color = '#15803D';
            badge.style.background = '#DCFCE7';
        } else {
            badge.textContent = `${diff} units (Reduced to ${val})`;
            badge.style.color = '#DC2626';
            badge.style.background = '#FEE2E2';
        }
    }
    window.onQuickEditStockChanged = onQuickEditStockChanged;

    function submitQuickEditStockForm(e) {
        e.preventDefault();
        const partId = document.getElementById('quickEditPartId').value;
        const rowIdx = document.getElementById('quickEditRowIdx').value;
        const form = document.getElementById('quickEditStockForm');
        const btn = document.getElementById('btnQuickEditSave');
        const errBox = document.getElementById('quickEditError');

        if (!partId) return;

        btn.disabled = true;
        btn.textContent = 'Saving...';
        if (errBox) { errBox.style.display = 'none'; errBox.textContent = ''; }

        const formData = new FormData(form);
        const updateUrlTemplate = "{{ route('mobileshop.stock.update', ['id' => ':id'], false) }}";
        const updateUrl = updateUrlTemplate.replace(':id', encodeURIComponent(partId));

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Save & Apply';

            if (!data.success) {
                if (errBox) {
                    errBox.style.display = 'block';
                    errBox.textContent = data.message || 'Error updating stock.';
                }
                return;
            }

            closeQuickEditStockModal();

            const updated = data.item;
            if (updated) {
                // Update in local catalogParts array
                if (Array.isArray(catalogParts)) {
                    const idxInArr = catalogParts.findIndex(p => p && String(p.id) === String(updated.id));
                    if (idxInArr >= 0) catalogParts[idxInArr] = { ...catalogParts[idxInArr], ...updated };
                } else if (catalogParts && typeof catalogParts === 'object') {
                    catalogParts[updated.id] = { ...(catalogParts[updated.id] || {}), ...updated };
                }

                // If rowIdx is provided, update that row's fields
                if (rowIdx !== '' && rowIdx !== null && rowIdx !== undefined) {
                    const idx = parseInt(rowIdx);
                    const nameInput = document.getElementById(`name_${idx}`);
                    const searchInput = document.getElementById(`searchInput_${idx}`);
                    if (nameInput) nameInput.value = updated.name;
                    if (searchInput) searchInput.value = updated.name;

                    const costInput = document.getElementById(`cost_${idx}`);
                    if (costInput && updated.unit_cost) costInput.value = parseFloat(updated.unit_cost).toFixed(2);

                    const priceInput = document.getElementById(`price_${idx}`);
                    if (priceInput && updated.selling_price) priceInput.value = parseFloat(updated.selling_price).toFixed(2);

                    const modelInput = document.getElementById(`model_${idx}`);
                    if (modelInput && updated.compatible_model) modelInput.value = updated.compatible_model;

                    if (updated.display_type) {
                        applyFolderQualityToRow(idx, updated.display_type);
                    }

                    // Refresh stock badge
                    renderStockBadge(idx, updated.stock_qty, false);
                    updateBulkRowTotal(idx);
                }
            }

            // Show flash banner
            const banner = document.createElement('div');
            banner.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:9999; background:#ECFDF5; border:1px solid #10B981; color:#065F46; padding:12px 18px; border-radius:8px; font-weight:700; font-size:13px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);';
            banner.innerHTML = `<span>✅ Stock item "<strong>${escapeHtml(updated?.name || 'Item')}</strong>" updated (New Stock: ${updated?.stock_qty || 0})</span>`;
            document.body.appendChild(banner);
            setTimeout(() => banner.remove(), 4000);
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Save & Apply';
            if (errBox) {
                errBox.style.display = 'block';
                errBox.textContent = 'Network or server error: ' + err.message;
            }
        });
    }
    window.submitQuickEditStockForm = submitQuickEditStockForm;

    /* ── Searchable Combobox: Filter & Render Menu ── */
    function renderComboboxMenu(idx, query = '') {
        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (!menuEl) return;

        const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
        const catValEl = document.getElementById(`catValue_${idx}`);
        const currentCat = catValEl ? catValEl.value : '';
        const canonicalCat = canonicalizeCategory(currentCat);

        const q = (query || '').toLowerCase().trim();
        const tokens = q ? q.split(/\s+/).filter(t => t.length > 0) : [];

        let matches = parts.filter(p => {
            if (!p || !p.name) return false;
            const pName = (p.name || '').toLowerCase();
            const pCat = (p.category || '').toLowerCase();
            const pBrand = (p.brand || '').toLowerCase();
            const pModel = (p.compatible_model || '').toLowerCase();
            const combined = `${pName} ${pCat} ${pBrand} ${pModel}`;

            if (tokens.length > 0) {
                return tokens.every(tok => combined.includes(tok));
            }

            if (currentCat && currentCat !== 'general_accessory') {
                return p.category === currentCat || canonicalizeCategory(p.category) === canonicalCat;
            }

            return true;
        });

        if (tokens.length === 0 && matches.length === 0) {
            matches = parts;
        }

        const displayMatches = matches.slice(0, 30);
        let html = '';

        if (tokens.length > 0) {
            // Prominent "Add as New Item" banner at the top
            const newNameText = query.trim();
            html += `
                <div class="combobox-new-option"
                    onmousedown="event.preventDefault(); chooseAsNewItem(${idx}, '${escapeHtml(newNameText)}');"
                    onclick="chooseAsNewItem(${idx}, '${escapeHtml(newNameText)}');">
                    <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                        <span style="font-size:14px;">✨</span>
                        <div style="min-width:0;">
                            <div style="font-weight:700; color:#4338CA; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                Add as New Item: "<strong>${escapeHtml(newNameText)}</strong>"
                            </div>
                            <div style="font-size:10px; color:#6366F1; font-weight:500;">
                                First time in inventory &bull; Starting stock: 0 pcs
                            </div>
                        </div>
                    </div>
                    <span style="background:#E0E7FF; color:#3730A3; font-size:10px; font-weight:800; padding:2px 7px; border-radius:4px; flex-shrink:0;">
                        + ADD NEW
                    </span>
                </div>
            `;

            if (displayMatches.length > 0) {
                html += `
                    <div style="padding:5px 8px 3px; font-size:10px; font-weight:800; color:#64748B; text-transform:uppercase; letter-spacing:0.4px; border-top:1px solid #F1F5F9; margin-top:2px;">
                        Or Pick Existing Stock (${matches.length})
                    </div>
                `;
            } else {
                html += `
                    <div style="padding:10px 12px; text-align:center; font-size:11.5px; color:#64748B; background:#F8FAFC; border-radius:6px; margin-top:4px;">
                        No existing inventory item matches "<strong>${escapeHtml(query)}</strong>".<br>
                        <span style="font-size:10.5px; color:#6366F1; font-weight:600;">Click "+ ADD NEW" above or simply press Enter / Tab to add as first-time stock!</span>
                    </div>
                `;
            }
        } else {
            // Query is empty: offer both options
            html += `
                <div class="combobox-new-option"
                    onmousedown="event.preventDefault(); focusAndTypeNew(${idx});"
                    onclick="focusAndTypeNew(${idx});">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:14px;">✨</span>
                        <div>
                            <div style="font-weight:700; color:#4338CA;">Type Brand New Item Name</div>
                            <div style="font-size:10px; color:#6366F1; font-weight:500;">Add a new cover, glass, cable or part for the first time</div>
                        </div>
                    </div>
                    <span style="background:#E0E7FF; color:#3730A3; font-size:10px; font-weight:800; padding:2px 7px; border-radius:4px;">
                        + NEW
                    </span>
                </div>
                <div style="padding:5px 8px 3px; font-size:10px; font-weight:800; color:#64748B; text-transform:uppercase; letter-spacing:0.4px; border-top:1px solid #F1F5F9; margin-top:2px;">
                    Or Pick Existing Inventory (${matches.length})
                </div>
            `;
        }

        if (displayMatches.length > 0) {
            displayMatches.forEach((p, itemIdx) => {
                const qty = parseInt(p.stock_qty || 0);
                const alertLvl = parseInt(p.min_stock_alert || 3);
                const badgeColor = qty <= 0 ? '#DC2626' : (qty <= alertLvl ? '#D97706' : '#059669');
                const badgeBg = qty <= 0 ? '#FEE2E2' : (qty <= alertLvl ? '#FEF3C7' : '#DCFCE7');
                const stockText = qty <= 0 ? '⚠️ 0 in stock (Low Stock)' : (qty <= alertLvl ? `⚠️ ${qty} in stock (Low)` : `${qty} in stock`);

                html += `
                    <div class="combobox-item" data-id="${p.id}" data-idx="${itemIdx}"
                        onmousedown="event.preventDefault(); selectExistingPart(${idx}, ${p.id});"
                        onclick="selectExistingPart(${idx}, ${p.id});">
                        <div style="min-width:0; flex:1;">
                            <div class="combobox-item-name">${escapeHtml(p.name)}</div>
                            <div class="combobox-item-sub">
                                ${p.brand && p.brand !== 'Universal' ? `<span style="font-weight:700; color:#475569;">${escapeHtml(p.brand)}</span> &bull; ` : ''}
                                ${escapeHtml(p.category || 'Accessory')} &bull; Cost: ₹${parseFloat(p.unit_cost || 0).toFixed(0)} &bull; Sell: ₹${parseFloat(p.selling_price || 0).toFixed(0)}
                            </div>
                        </div>
                        <div class="combobox-item-stock">
                            <span style="background:${badgeBg}; color:${badgeColor}; font-weight:800; font-size:10.5px; padding:2px 7px; border-radius:4px; display:inline-block;">
                                ${stockText}
                            </span>
                        </div>
                    </div>
                `;
            });
        }

        menuEl.innerHTML = html;
    }

    const itemBlurTimers = {};
    const categoryBlurTimers = {};
    const isMenuInteracting = {};

    /* ── Open / Show Combobox ── */
    function openCombobox(idx) {
        if (itemBlurTimers[idx]) {
            clearTimeout(itemBlurTimers[idx]);
            delete itemBlurTimers[idx];
        }

        document.querySelectorAll('.combobox-menu').forEach(m => {
            if (m.id !== `comboboxMenu_${idx}`) m.style.display = 'none';
        });

        const input = document.getElementById(`searchInput_${idx}`);
        const val = input ? input.value : '';
        renderComboboxMenu(idx, val);

        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'block';

        const clearBtn = document.getElementById(`clearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = val ? 'flex' : 'none';
    }
    window.openCombobox = openCombobox;

    /* ── On Combobox Blur (Auto-detect First Time vs Existing) ── */
    function onComboboxBlur(idx) {
        if (itemBlurTimers[idx]) {
            clearTimeout(itemBlurTimers[idx]);
        }

        itemBlurTimers[idx] = setTimeout(() => {
            if (isMenuInteracting[`item_${idx}`]) {
                return;
            }

            const wrap = document.getElementById(`comboboxWrap_${idx}`);
            if (wrap && wrap.contains(document.activeElement)) {
                return;
            }

            const menuEl = document.getElementById(`comboboxMenu_${idx}`);
            if (menuEl) menuEl.style.display = 'none';

            const input = document.getElementById(`searchInput_${idx}`);
            const typed = (input ? input.value : '').trim();
            const partId = document.getElementById(`partId_${idx}`)?.value;

            if (typed) {
                if (!partId) {
                    // Check if typed name exact-matches an existing inventory item
                    const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
                    const exact = parts.find(p => p && p.name && p.name.toLowerCase() === typed.toLowerCase());
                    if (exact) {
                        selectExistingPart(idx, exact.id);
                    } else {
                        chooseAsNewItem(idx, typed);
                    }
                } else {
                    const nameInput = document.getElementById(`name_${idx}`);
                    if (nameInput) nameInput.value = typed;
                }
            } else {
                if (input) input.value = '';
                document.getElementById(`name_${idx}`).value = '';
                document.getElementById(`partId_${idx}`).value = '';
                const clearBtn = document.getElementById(`clearBtn_${idx}`);
                if (clearBtn) clearBtn.style.display = 'none';
                renderStockBadge(idx, null);
                updateBulkRowTotal(idx);
            }
        }, 200);
    }
    window.onComboboxBlur = onComboboxBlur;

    /* ── On Typing in Search Combobox ── */
    function onComboboxInput(idx, val) {
        const trimmed = (val || '').trim();
        document.getElementById(`name_${idx}`).value = trimmed;

        const clearBtn = document.getElementById(`clearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = val ? 'flex' : 'none';

        const currentPartId = document.getElementById(`partId_${idx}`).value;
        if (currentPartId) {
            const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
            const p = parts.find(item => item && String(item.id) === String(currentPartId));
            if (!p || p.name.toLowerCase() !== trimmed.toLowerCase()) {
                document.getElementById(`partId_${idx}`).value = '';
            }
        }

        const partIdAfter = document.getElementById(`partId_${idx}`).value;
        if (!partIdAfter) {
            if (trimmed) {
                const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
                const exact = parts.find(p => p && p.name && p.name.toLowerCase() === trimmed.toLowerCase());
                if (exact) {
                    renderStockBadge(idx, exact.stock_qty || 0, false);
                } else {
                    renderStockBadge(idx, 0, true);
                }
            } else {
                renderStockBadge(idx, null);
            }
        }


        if (trimmed.length >= 2) {
            const autoCat = canonicalizeCategory('', trimmed);
            if (autoCat && autoCat !== 'general_accessory') {
                const catValEl = document.getElementById(`catValue_${idx}`);
                const catSearchEl = document.getElementById(`catSearchInput_${idx}`);
                if (catValEl && !catValEl.value) {
                    catValEl.value = autoCat;
                    if (catSearchEl) catSearchEl.value = formatCategoryLabel(autoCat);
                    const catClearBtn = document.getElementById(`catClearBtn_${idx}`);
                    if (catClearBtn) catClearBtn.style.display = 'flex';
                    onBulkCategoryChange(idx, autoCat, false);
                }
            }
        }

        renderComboboxMenu(idx, val);

        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'block';

        updateBulkRowTotal(idx);
    }
    window.onComboboxInput = onComboboxInput;

    /* ── Clear Selected Item ── */
    function clearComboboxItem(idx) {
        if (itemBlurTimers[idx]) {
            clearTimeout(itemBlurTimers[idx]);
            delete itemBlurTimers[idx];
        }

        const searchInput = document.getElementById(`searchInput_${idx}`);
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        document.getElementById(`name_${idx}`).value = '';
        document.getElementById(`partId_${idx}`).value = '';

        const clearBtn = document.getElementById(`clearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = 'none';

        renderStockBadge(idx, null);
        updateBulkRowTotal(idx);
        openCombobox(idx);
    }
    window.clearComboboxItem = clearComboboxItem;

    /* ── Select Existing Part from Dropdown ── */
    function selectExistingPart(idx, partId) {
        if (itemBlurTimers[idx]) {
            clearTimeout(itemBlurTimers[idx]);
            delete itemBlurTimers[idx];
        }

        const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
        const p = parts.find(item => item && String(item.id) === String(partId));
        if (!p) return;

        let partDisplayName = p.name;

        const searchInput = document.getElementById(`searchInput_${idx}`);
        if (searchInput) searchInput.value = partDisplayName;
        document.getElementById(`name_${idx}`).value = partDisplayName;
        document.getElementById(`partId_${idx}`).value = p.id;

        const clearBtn = document.getElementById(`clearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = 'flex';

        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'none';

        if (p.category) {
            const targetCat = canonicalizeCategory(p.category, p.name);
            const catValEl = document.getElementById(`catValue_${idx}`);
            const catSearchEl = document.getElementById(`catSearchInput_${idx}`);
            if (catValEl) catValEl.value = targetCat;
            if (catSearchEl) catSearchEl.value = formatCategoryLabel(targetCat);

            const catClearBtn = document.getElementById(`catClearBtn_${idx}`);
            if (catClearBtn) catClearBtn.style.display = 'flex';

            onBulkCategoryChange(idx, targetCat, false);
        }

        const modelEl = document.getElementById(`model_${idx}`);
        if (modelEl) modelEl.value = '';

        if (p.display_type) {
            applyFolderQualityToRow(idx, p.display_type);
        }

        if (p.description) document.getElementById(`desc_${idx}`).value = p.description;
        if (p.min_stock_alert !== undefined && p.min_stock_alert !== null) document.getElementById(`alert_${idx}`).value = p.min_stock_alert;
        if (p.unit_cost) document.getElementById(`cost_${idx}`).value = parseFloat(p.unit_cost).toFixed(2);
        if (p.selling_price) document.getElementById(`price_${idx}`).value = parseFloat(p.selling_price).toFixed(2);

        renderStockBadge(idx, p.stock_qty || 0, false);
        updateBulkRowTotal(idx);
    }
    window.selectExistingPart = selectExistingPart;

    /* ── Choose as New Product ── */
    function chooseAsNewItem(idx, customName) {
        if (itemBlurTimers[idx]) {
            clearTimeout(itemBlurTimers[idx]);
            delete itemBlurTimers[idx];
        }

        const searchInput = document.getElementById(`searchInput_${idx}`);
        let rawName = (customName !== undefined && customName !== null)
            ? customName.trim()
            : (searchInput?.value.trim() || '');

        if (!rawName) {
            if (searchInput) searchInput.value = '';
            document.getElementById(`name_${idx}`).value = '';
            document.getElementById(`partId_${idx}`).value = '';
            const clearBtn = document.getElementById(`clearBtn_${idx}`);
            if (clearBtn) clearBtn.style.display = 'none';
            const menuEl = document.getElementById(`comboboxMenu_${idx}`);
            if (menuEl) menuEl.style.display = 'none';
            renderStockBadge(idx, null);
            updateBulkRowTotal(idx);
            return;
        }

        const modelEl = document.getElementById(`model_${idx}`);
        if (modelEl) modelEl.value = '';

        const catValEl = document.getElementById(`catValue_${idx}`);
        const catSearchEl = document.getElementById(`catSearchInput_${idx}`);
        if (catValEl && !catValEl.value) {
            const autoCat = canonicalizeCategory('', rawName);
            if (autoCat && autoCat !== 'general_accessory') {
                catValEl.value = autoCat;
                if (catSearchEl) catSearchEl.value = formatCategoryLabel(autoCat);
                onBulkCategoryChange(idx, autoCat, false);
            }
        }

        if (searchInput) searchInput.value = rawName;
        document.getElementById(`name_${idx}`).value = rawName;
        document.getElementById(`partId_${idx}`).value = '';

        const clearBtn = document.getElementById(`clearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = 'flex';

        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'none';

        renderStockBadge(idx, 0, true);
        updateBulkRowTotal(idx);
    }
    window.chooseAsNewItem = chooseAsNewItem;

    /* ── Keyboard Navigation in Combobox Menu ── */
    function onComboboxKeydown(idx, e) {
        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        const isMenuOpen = menuEl && menuEl.style.display !== 'none';

        if (e.key === 'ArrowDown') {
            if (!isMenuOpen) {
                openCombobox(idx);
                e.preventDefault();
                return;
            }
            const items = menuEl.querySelectorAll('.combobox-item, .combobox-new-option');
            if (items.length === 0) return;
            e.preventDefault();
            let activeIdx = -1;
            items.forEach((it, i) => {
                if (it.classList.contains('active')) activeIdx = i;
            });
            if (activeIdx >= 0) items[activeIdx].classList.remove('active');
            activeIdx = (activeIdx + 1) % items.length;
            items[activeIdx].classList.add('active');
            items[activeIdx].scrollIntoView({ block: 'nearest' });
            return;
        }

        if (e.key === 'ArrowUp') {
            if (!isMenuOpen) return;
            const items = menuEl.querySelectorAll('.combobox-item, .combobox-new-option');
            if (items.length === 0) return;
            e.preventDefault();
            let activeIdx = -1;
            items.forEach((it, i) => {
                if (it.classList.contains('active')) activeIdx = i;
            });
            if (activeIdx >= 0) items[activeIdx].classList.remove('active');
            activeIdx = (activeIdx - 1 + items.length) % items.length;
            items[activeIdx].classList.add('active');
            items[activeIdx].scrollIntoView({ block: 'nearest' });
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent unintentional form submission!
            if (isMenuOpen) {
                const activeExisting = menuEl.querySelector('.combobox-item.active');
                if (activeExisting) {
                    const partId = activeExisting.getAttribute('data-id');
                    if (partId) {
                        selectExistingPart(idx, partId);
                        if (menuEl) menuEl.style.display = 'none';
                        const nextEl = document.getElementById(`desc_${idx}`) || document.getElementById(`qty_${idx}`);
                        if (nextEl) nextEl.focus();
                        return;
                    }
                }
                const activeNew = menuEl.querySelector('.combobox-new-option.active');
                if (activeNew) {
                    const input = document.getElementById(`searchInput_${idx}`);
                    chooseAsNewItem(idx, input ? input.value : '');
                    if (menuEl) menuEl.style.display = 'none';
                    const nextEl = document.getElementById(`desc_${idx}`) || document.getElementById(`qty_${idx}`);
                    if (nextEl) nextEl.focus();
                    return;
                }
            }
            const input = document.getElementById(`searchInput_${idx}`);
            const typed = (input ? input.value : '').trim();
            if (typed) {
                const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
                const exact = parts.find(p => p && p.name && p.name.toLowerCase() === typed.toLowerCase());
                if (exact) {
                    selectExistingPart(idx, exact.id);
                } else {
                    chooseAsNewItem(idx, typed);
                }
            } else {
                if (input) input.value = '';
                document.getElementById(`name_${idx}`).value = '';
                document.getElementById(`partId_${idx}`).value = '';
                const clearBtn = document.getElementById(`clearBtn_${idx}`);
                if (clearBtn) clearBtn.style.display = 'none';
                renderStockBadge(idx, null);
                updateBulkRowTotal(idx);
            }
            if (menuEl) menuEl.style.display = 'none';
            const nextEl = document.getElementById(`desc_${idx}`) || document.getElementById(`qty_${idx}`);
            if (nextEl) nextEl.focus();
            return;
        }

        if (e.key === 'Tab') {
            if (isMenuOpen) {
                const activeExisting = menuEl.querySelector('.combobox-item.active');
                if (activeExisting) {
                    const partId = activeExisting.getAttribute('data-id');
                    if (partId) {
                        selectExistingPart(idx, partId);
                    }
                }
                menuEl.style.display = 'none';
            }
            return;
        }

        if (e.key === 'Escape') {
            if (menuEl) menuEl.style.display = 'none';
            return;
        }
    }
    window.onComboboxKeydown = onComboboxKeydown;

    /* Close combobox menus when clicking outside */
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.combobox-wrapper')) {
            document.querySelectorAll('.combobox-menu').forEach(m => m.style.display = 'none');
            Object.keys(isMenuInteracting).forEach(k => isMenuInteracting[k] = false);
        }
    });

    /* ── Render Category Combobox Menu ── */
    function renderCategoryMenu(idx, query = '') {
        const menuEl = document.getElementById(`catComboboxMenu_${idx}`);
        if (!menuEl) return;

        const q = (query || '').toLowerCase().trim();
        const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
        const currentSlug = document.getElementById(`catValue_${idx}`)?.value || '';

        const matches = cats.filter(cat => {
            if (!cat) return false;
            if (!q) return true;
            const name = (cat.name || '').toLowerCase();
            const slug = (cat.slug || '').toLowerCase();
            return name.includes(q) || slug.includes(q);
        });

        if (matches.length === 0) {
            menuEl.innerHTML = `
                <div style="padding:10px 12px; text-align:center; font-size:11.5px; color:#64748B;">
                    No category matches "<strong>${escapeHtml(query)}</strong>".<br>
                    <span style="font-size:10.5px; color:#4F46E5; font-weight:600;">Press Enter to use standard category</span>
                </div>
            `;
            return;
        }

        let html = '';
        matches.forEach((c, itemIdx) => {
            const isSelected = (c.slug === currentSlug);
            const isActive = currentSlug ? isSelected : (itemIdx === 0);
            html += `
                <div class="combobox-item ${isActive ? 'active' : ''}"
                    data-slug="${escapeHtml(c.slug)}"
                    data-name="${escapeHtml(c.name)}"
                    data-idx="${itemIdx}"
                    onmousedown="event.preventDefault(); selectCategoryOption(${idx}, '${escapeHtml(c.slug)}', '${escapeHtml(c.name)}', true);"
                    onclick="selectCategoryOption(${idx}, '${escapeHtml(c.slug)}', '${escapeHtml(c.name)}', true);">
                    <div style="font-weight:700; color:#0F172A; font-size:11.5px;">${escapeHtml(c.name)}</div>
                    ${isSelected ? '<span style="font-size:10px; background:#DCFCE7; color:#15803D; font-weight:800; padding:1px 6px; border-radius:4px;">Selected</span>' : ''}
                </div>
            `;
        });

        menuEl.innerHTML = html;
    }

    /* ── Open / Show Category Combobox (One List at Once) ── */
    function openCategoryCombobox(idx, forceFullList = true) {
        if (categoryBlurTimers[idx]) {
            clearTimeout(categoryBlurTimers[idx]);
            delete categoryBlurTimers[idx];
        }

        // Enforce ONE LIST AT ONCE: close all other open comboboxes everywhere!
        document.querySelectorAll('.combobox-menu').forEach(m => {
            if (m.id !== `catComboboxMenu_${idx}`) m.style.display = 'none';
        });

        const input = document.getElementById(`catSearchInput_${idx}`);
        const query = forceFullList ? '' : (input ? input.value : '');
        renderCategoryMenu(idx, query);

        const menuEl = document.getElementById(`catComboboxMenu_${idx}`);
        if (menuEl) {
            menuEl.style.display = 'block';
            const activeItem = menuEl.querySelector('.combobox-item.active') || menuEl.querySelector('.combobox-item');
            if (activeItem) {
                activeItem.scrollIntoView({ block: 'nearest' });
            }
        }

        const clearBtn = document.getElementById(`catClearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = (input && input.value) ? 'flex' : 'none';
    }
    window.openCategoryCombobox = openCategoryCombobox;

    /* ── On Category Blur ── */
    function onCategoryComboboxBlur(idx) {
        if (categoryBlurTimers[idx]) {
            clearTimeout(categoryBlurTimers[idx]);
        }

        categoryBlurTimers[idx] = setTimeout(() => {
            if (isMenuInteracting[`cat_${idx}`]) {
                return;
            }

            const wrap = document.getElementById(`catComboboxWrap_${idx}`);
            if (wrap && wrap.contains(document.activeElement)) {
                return;
            }

            const menuEl = document.getElementById(`catComboboxMenu_${idx}`);
            if (menuEl) menuEl.style.display = 'none';

            const input = document.getElementById(`catSearchInput_${idx}`);
            const typed = (input ? input.value : '').trim();
            const currentSlug = document.getElementById(`catValue_${idx}`)?.value;

            if (typed) {
                const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
                const exact = cats.find(c => c && (c.name.toLowerCase() === typed.toLowerCase() || c.slug.toLowerCase() === typed.toLowerCase()));
                if (exact) {
                    selectCategoryOption(idx, exact.slug, exact.name, false);
                } else if (!currentSlug) {
                    const auto = canonicalizeCategory('', typed);
                    if (auto) {
                        const found = cats.find(c => c && c.slug === auto);
                        selectCategoryOption(idx, auto, found ? found.name : formatCategoryLabel(auto), false);
                    }
                }
            } else {
                const valInput = document.getElementById(`catValue_${idx}`);
                if (valInput) valInput.value = '';
                const clearBtn = document.getElementById(`catClearBtn_${idx}`);
                if (clearBtn) clearBtn.style.display = 'none';
                const mobileBadge = document.getElementById(`mobileCardCatLabel_${idx}`);
                if (mobileBadge) mobileBadge.textContent = '';
                onBulkCategoryChange(idx, '');
            }
        }, 220);
    }
    window.onCategoryComboboxBlur = onCategoryComboboxBlur;

    /* ── On Typing Category ── */
    function onCategoryComboboxInput(idx, val) {
        const clearBtn = document.getElementById(`catClearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = val ? 'flex' : 'none';

        renderCategoryMenu(idx, val);

        const menuEl = document.getElementById(`catComboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'block';
    }
    window.onCategoryComboboxInput = onCategoryComboboxInput;

    /* ── Category Keyboard Navigation (Arrow Keys + Enter Selection) ── */
    function onCategoryComboboxKeydown(idx, e) {
        const menuEl = document.getElementById(`catComboboxMenu_${idx}`);
        const isMenuOpen = menuEl && menuEl.style.display !== 'none';

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!isMenuOpen) {
                openCategoryCombobox(idx, true);
                return;
            }
            const items = menuEl.querySelectorAll('.combobox-item');
            if (items.length === 0) return;
            let activeIdx = -1;
            items.forEach((it, i) => {
                if (it.classList.contains('active')) activeIdx = i;
            });
            if (activeIdx >= 0) items[activeIdx].classList.remove('active');
            activeIdx = (activeIdx + 1) % items.length;
            const nextItem = items[activeIdx];
            nextItem.classList.add('active');
            nextItem.scrollIntoView({ block: 'nearest' });

            const previewName = nextItem.getAttribute('data-name');
            const input = document.getElementById(`catSearchInput_${idx}`);
            if (input && previewName) {
                input.value = previewName;
            }
            return;
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!isMenuOpen) {
                openCategoryCombobox(idx, true);
                return;
            }
            const items = menuEl.querySelectorAll('.combobox-item');
            if (items.length === 0) return;
            let activeIdx = -1;
            items.forEach((it, i) => {
                if (it.classList.contains('active')) activeIdx = i;
            });
            if (activeIdx >= 0) items[activeIdx].classList.remove('active');
            activeIdx = (activeIdx - 1 + items.length) % items.length;
            const prevItem = items[activeIdx];
            prevItem.classList.add('active');
            prevItem.scrollIntoView({ block: 'nearest' });

            const previewName = prevItem.getAttribute('data-name');
            const input = document.getElementById(`catSearchInput_${idx}`);
            if (input && previewName) {
                input.value = previewName;
            }
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent accidental form submission
            if (isMenuOpen) {
                const activeItem = menuEl.querySelector('.combobox-item.active') || menuEl.querySelector('.combobox-item');
                if (activeItem) {
                    const slug = activeItem.getAttribute('data-slug');
                    const name = activeItem.getAttribute('data-name');
                    if (slug && name) {
                        selectCategoryOption(idx, slug, name, true);
                        return;
                    }
                }
            }
            const input = document.getElementById(`catSearchInput_${idx}`);
            const typed = (input ? input.value : '').trim();
            if (typed) {
                const auto = canonicalizeCategory('', typed);
                const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
                const found = cats.find(c => c && c.slug === auto);
                selectCategoryOption(idx, auto, found ? found.name : formatCategoryLabel(auto), true);
            }
            if (menuEl) menuEl.style.display = 'none';
            const searchInput = document.getElementById(`searchInput_${idx}`);
            if (searchInput) searchInput.focus();
            return;
        }

        if (e.key === 'Tab') {
            if (isMenuOpen) {
                const activeItem = menuEl.querySelector('.combobox-item.active') || menuEl.querySelector('.combobox-item');
                if (activeItem) {
                    const slug = activeItem.getAttribute('data-slug');
                    const name = activeItem.getAttribute('data-name');
                    if (slug && name) {
                        selectCategoryOption(idx, slug, name, false);
                    }
                }
                menuEl.style.display = 'none';
            }
            return;
        }

        if (e.key === 'Escape') {
            if (menuEl) menuEl.style.display = 'none';
            return;
        }
    }
    window.onCategoryComboboxKeydown = onCategoryComboboxKeydown;

    /* ── Select Category Option ── */
    function selectCategoryOption(idx, slug, name, autoAdvance = true) {
        if (categoryBlurTimers[idx]) {
            clearTimeout(categoryBlurTimers[idx]);
            delete categoryBlurTimers[idx];
        }

        const valInput = document.getElementById(`catValue_${idx}`);
        if (valInput) valInput.value = slug;

        const searchInput = document.getElementById(`catSearchInput_${idx}`);
        if (searchInput) searchInput.value = name;

        const clearBtn = document.getElementById(`catClearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = 'flex';

        const menuEl = document.getElementById(`catComboboxMenu_${idx}`);
        if (menuEl) menuEl.style.display = 'none';

        onBulkCategoryChange(idx, slug);

        // Auto-focus next field only when explicitly requested
        if (autoAdvance) {
            const itemInput = document.getElementById(`searchInput_${idx}`);
            if (itemInput) {
                setTimeout(() => itemInput.focus(), 40);
            }
        }
    }
    window.selectCategoryOption = selectCategoryOption;

    /* ── Clear Category Item ── */
    function clearCategoryItem(idx) {
        if (categoryBlurTimers[idx]) {
            clearTimeout(categoryBlurTimers[idx]);
            delete categoryBlurTimers[idx];
        }

        const searchInput = document.getElementById(`catSearchInput_${idx}`);
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        const valInput = document.getElementById(`catValue_${idx}`);
        if (valInput) valInput.value = '';

        const clearBtn = document.getElementById(`catClearBtn_${idx}`);
        if (clearBtn) clearBtn.style.display = 'none';

        const mobileBadge = document.getElementById(`mobileCardCatLabel_${idx}`);
        if (mobileBadge) mobileBadge.textContent = '';

        onBulkCategoryChange(idx, '');
        openCategoryCombobox(idx);
    }
    window.clearCategoryItem = clearCategoryItem;

    /* ── Add Bulk Row and Automatically Focus Category Input ── */
    function addBulkRowAndFocus() {
        addBulkRow();
        setTimeout(() => {
            const newRow = document.getElementById(`bulk-row-${bulkRowIndex}`);
            if (newRow && window.innerWidth < 880) {
                newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            const newCatInput = document.getElementById(`catSearchInput_${bulkRowIndex}`);
            if (newCatInput) {
                newCatInput.focus();
                newCatInput.select();
            }
        }, 60);
    }
    window.addBulkRowAndFocus = addBulkRowAndFocus;

    /* ── Add New Row to Batch Intake Table ── */
    function addBulkRow(itemData = null) {
        try {
            bulkRowIndex++;
            const idx = bulkRowIndex;
            const container = document.getElementById('bulkTableBody');
            if (!container) return;

            const row = document.createElement('div');
            row.className = 'batch-item-row batch-grid-row';
            row.id = `bulk-row-${idx}`;

            const targetCat = itemData ? (canonicalizeCategory(itemData?.category, itemData?.name) || '') : '';
            const cats = Array.isArray(catalogCategories) ? catalogCategories : Object.values(catalogCategories || {});
            const foundCat = targetCat ? cats.find(c => c && (c.slug === targetCat || canonicalizeCategory(c.slug) === targetCat || canonicalizeCategory(c.name) === targetCat)) : null;
            const selectedCatName = foundCat ? (foundCat.name || foundCat.slug) : (targetCat ? formatCategoryLabel(targetCat) : '');

            // Check if itemData matches an existing part in inventory
            const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
            let selectedPart = null;

            if (itemData) {
                if (itemData.part_id) {
                    selectedPart = parts.find(p => p && String(p.id) === String(itemData.part_id));
                }
                if (!selectedPart && itemData.name) {
                    selectedPart = parts.find(p => p && p.name && p.name.toLowerCase() === itemData.name.toLowerCase().trim());
                }
            }

            let rawItemName = itemData?.name || selectedPart?.name || '';
            const nameVal = escapeHtml(rawItemName);
            let rawBrand = itemData?.brand || '';
            if (rawBrand === 'Universal') rawBrand = '';
            const brandVal = escapeHtml(rawBrand);
            const rawFolderDt = (itemData?.display_type || selectedPart?.display_type || 'Normal').trim();
            let folderType = 'Normal';
            let folderLabel = 'Normal';
            let folderClass = 'quality-pill-normal';
            const fLower = rawFolderDt.toLowerCase();
            if (fLower === 'hd+' || fLower === 'hd_plus') {
                folderType = 'HD+';
                folderLabel = 'HD+ ⚡';
                folderClass = 'quality-pill-hd';
            } else if (fLower === 'og' || fLower === 'original_oem') {
                folderType = 'OG';
                folderLabel = 'OG ✨';
                folderClass = 'quality-pill-og';
            }
            const descVal = escapeHtml(itemData?.description || selectedPart?.description || '');
            const alertVal = itemData?.min_stock_alert !== undefined ? itemData.min_stock_alert : (selectedPart?.min_stock_alert ?? 3);
            const qtyVal = (itemData?.qty !== undefined && itemData?.qty !== null && !isNaN(parseInt(itemData.qty)))
                ? Math.max(0, parseInt(itemData.qty))
                : 1;
            const costVal = itemData?.unit_cost ? parseFloat(itemData.unit_cost).toFixed(2) : (selectedPart?.unit_cost ? parseFloat(selectedPart.unit_cost).toFixed(2) : '0.00');
            const priceVal = itemData?.selling_price ? parseFloat(itemData.selling_price).toFixed(2) : (selectedPart?.selling_price ? parseFloat(selectedPart.selling_price).toFixed(2) : (parseFloat(costVal) * 1.5).toFixed(2));
            const isGiftChecked = (itemData?.is_gift_eligible || (targetCat && (targetCat === 'tempered_glass' || targetCat === 'back_cover' || targetCat === 'charger_cable' || targetCat === 'earphones_audio' || targetCat === 'general_accessory'))) ? 'checked' : '';
            const lineTotal = (qtyVal * parseFloat(costVal));
            const isFolder = (targetCat === 'folder_display');

            row.innerHTML = `
                <!-- Hidden form fields -->
                <input type="hidden" name="items[${idx}][part_id]" id="partId_${idx}" value="${selectedPart?.id || itemData?.part_id || ''}">
                <input type="hidden" name="items[${idx}][name]"    id="name_${idx}"   value="${nameVal}">
                <input type="hidden" name="items[${idx}][compatible_model]" id="model_${idx}" value="">
                <input type="hidden" name="items[${idx}][is_gift_eligible]" id="gift_${idx}" value="${isGiftChecked ? '1' : '0'}">
                <input type="hidden" name="items[${idx}][display_type]" id="displayTypeVal_${idx}" value="${folderType}">

                <!-- MOBILE CARD TOP BAR -->
                <div class="mobile-card-top">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span class="mobile-row-badge">#${idx}</span>
                        <span style="font-size:11px; font-weight:700; color:#64748B;" id="mobileCardCatLabel_${idx}">${selectedCatName ? escapeHtml(selectedCatName) : ''}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button type="button" class="btn-ghost-add" onclick="addBulkRowAndFocus()" title="Add another item row (+)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </button>
                        <button type="button" class="btn-ghost-delete" onclick="removeBulkRow(${idx})" title="Remove item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- 1. CATEGORY SEARCHABLE COMBOBOX -->
                <div class="combobox-wrapper" id="catComboboxWrap_${idx}">
                    <label class="mobile-card-label">Category</label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="text" id="catSearchInput_${idx}" class="restock-input search-combobox-input cat-search-input"
                            value="${selectedCatName ? escapeHtml(selectedCatName) : ''}" placeholder="Category"
                            autocomplete="off"
                            onfocus="openCategoryCombobox(${idx})"
                            onclick="openCategoryCombobox(${idx})"
                            onblur="onCategoryComboboxBlur(${idx})"
                            oninput="onCategoryComboboxInput(${idx}, this.value)"
                            onkeydown="onCategoryComboboxKeydown(${idx}, event)">
                        <button type="button" id="catClearBtn_${idx}" class="combobox-clear-btn"
                            onclick="clearCategoryItem(${idx})"
                            style="${selectedCatName ? 'display:flex;' : 'display:none;'}"
                            title="Clear category">✕</button>
                    </div>
                    <input type="hidden" name="items[${idx}][category]" id="catValue_${idx}" value="${targetCat ? escapeHtml(targetCat) : ''}">
                    <div id="catComboboxMenu_${idx}" class="combobox-menu category-combobox-menu" style="display:none;" tabindex="-1" onmousedown="event.preventDefault()" onmouseenter="isMenuInteracting['cat_${idx}'] = true" onmouseleave="isMenuInteracting['cat_${idx}'] = false"></div>
                </div>

                <!-- 2. SEARCHABLE DROPDOWN (COMBOBOX) -->
                <div class="combobox-wrapper" id="comboboxWrap_${idx}">
                    <label class="mobile-card-label">Item &amp; Current Stock</label>
                    
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="text" id="searchInput_${idx}" class="restock-input search-combobox-input"
                            value="${nameVal}" placeholder="Item"
                            autocomplete="off"
                            onfocus="openCombobox(${idx})"
                            onclick="openCombobox(${idx})"
                            onblur="onComboboxBlur(${idx})"
                            oninput="onComboboxInput(${idx}, this.value)"
                            onkeydown="onComboboxKeydown(${idx}, event)">
                        <button type="button" id="clearBtn_${idx}" class="combobox-clear-btn"
                            onclick="clearComboboxItem(${idx})"
                            style="${nameVal ? 'display:flex;' : 'display:none;'}"
                            title="Clear selection">
                            ✕
                        </button>
                    </div>

                    <!-- Floating Dropdown Menu -->
                    <div id="comboboxMenu_${idx}" class="combobox-menu" style="display:none;" tabindex="-1" onmousedown="event.preventDefault()" onmouseenter="isMenuInteracting['item_${idx}'] = true" onmouseleave="isMenuInteracting['item_${idx}'] = false"></div>

                    <!-- Stock Status Badge -->
                    <div id="stockBadge_${idx}" style="margin-top:3px;"></div>

                    <!-- Quality tier switch pill (Normal -> HD+ -> OG) -->
                    <div class="quality-toggle-pill ${folderClass} ${isFolder ? 'visible' : ''}" id="qualityToggle_${idx}" onclick="toggleFolderQuality(${idx})" title="Click to cycle quality: Normal &rarr; HD+ ⚡ &rarr; OG ✨">
                        <span>Quality:</span> <strong id="qualityLabel_${idx}">${folderLabel}</strong>
                    </div>

                    <!-- Instant Row Warning / Health Badge -->
                    <div id="rowWarning_${idx}" style="margin-top:3px; display:none;"></div>
                </div>

                <!-- 3. BRAND NAME INPUT (hidden for folder_display category) -->
                <div id="brandWrap_${idx}" style="${isFolder ? 'display:none;' : ''}">
                    <label class="mobile-card-label">Brand</label>
                    <input type="text" name="items[${idx}][brand]" id="brand_${idx}"
                        value="${isFolder ? '' : brandVal}" placeholder="Brand" list="bulkBrandsDatalist"
                        class="restock-input" autocomplete="off">
                </div>

                <!-- 4. DESCRIPTION / SPECS (spans into Brand column for folder_display) -->
                <div style="${isFolder ? 'grid-column: span 2;' : ''}">
                    <label class="mobile-card-label">Description / Specs</label>
                    <input type="text" name="items[${idx}][description]" id="desc_${idx}"
                        value="${descVal}" placeholder="Description"
                        class="restock-input">
                </div>

                <!-- 4. METRICS GRID -->
                <div class="mobile-metric-grid" style="display:contents;">
                    <!-- Qty Stepper -->
                    <div>
                        <label class="mobile-card-label">Qty</label>
                        <div class="stepper-wrap">
                            <button type="button" tabindex="-1" class="stepper-btn stepper-btn-minus" onclick="adjustBulkQty(${idx}, -1)">−</button>
                            <input type="number" name="items[${idx}][qty]" id="qty_${idx}"
                                value="${qtyVal}" min="0" required
                                class="bulk-num-input stepper-input"
                                oninput="updateBulkRowTotal(${idx})">
                            <button type="button" tabindex="-1" class="stepper-btn stepper-btn-plus" onclick="adjustBulkQty(${idx}, 1)">+</button>
                        </div>
                        <!-- Live preview of updated stock total -->
                        <div id="stockAfter_${idx}" style="font-size:10px; font-weight:700; text-align:center; margin-top:2px;"></div>
                    </div>

                    <!-- Cost ₹ -->
                    <div>
                        <label class="mobile-card-label">Cost ₹</label>
                        <input type="number" step="0.01" name="items[${idx}][unit_cost]" id="cost_${idx}"
                            value="${costVal}" min="0" required
                            class="bulk-num-input restock-input num-field cost-field"
                            oninput="updateBulkRowTotal(${idx})">
                    </div>

                    <!-- Sell ₹ -->
                    <div>
                        <label class="mobile-card-label">Sell ₹</label>
                        <input type="number" step="0.01" name="items[${idx}][selling_price]" id="price_${idx}"
                            value="${priceVal}" min="0" required
                            class="bulk-num-input restock-input num-field sell-field"
                            oninput="updateBulkRowTotal(${idx})">
                        <div id="priceWarning_${idx}" style="font-size:9.5px; font-weight:800; text-align:center; margin-top:2px; display:none;"></div>
                    </div>

                    <!-- Alert -->
                    <div>
                        <label class="mobile-card-label">Alert</label>
                        <input type="number" name="items[${idx}][min_stock_alert]" id="alert_${idx}"
                            value="${alertVal}" min="0" placeholder="Alert"
                            class="bulk-num-input restock-input num-field" style="text-align:center;"
                            title="Low stock threshold">
                    </div>
                </div>

                <!-- 5. DESKTOP TOTAL CELL -->
                <div class="desktop-total-cell">
                    <div class="desktop-total-amt" id="lineTotal_${idx}">${formatCurrency(lineTotal)}</div>
                </div>

                <!-- 6. DESKTOP ACTION BUTTONS (+ and Remove) -->
                <div class="desktop-action-cell" style="display:flex; align-items:center; justify-content:center; gap:5px; padding-top:2px;">
                    <button type="button" class="btn-ghost-add" onclick="addBulkRowAndFocus()" title="Add another item row (+)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <button type="button" class="btn-ghost-delete" onclick="removeBulkRow(${idx})" title="Remove item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- 7. MOBILE CARD FOOTER (Only for mobile card view) -->
                <div class="mobile-card-footer" style="display:none;">
                    <span style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">Line Total</span>
                    <strong style="font-size:14px; font-family:'JetBrains Mono', monospace; font-weight:800; color:#0F172A;" id="mobileLineTotal_${idx}">${formatCurrency(lineTotal)}</strong>
                </div>
            `;

            container.appendChild(row);

            // Initialize stock badge and selection
            if (selectedPart) {
                selectExistingPart(idx, selectedPart.id);
            } else if (itemData?.name) {
                chooseAsNewItem(idx, rawItemName || itemData.name);
            } else {
                renderStockBadge(idx, null);
            }

            // Ensure category inputs match targetCat
            const catValInput = document.getElementById(`catValue_${idx}`);
            const catSearchInput = document.getElementById(`catSearchInput_${idx}`);
            if (catValInput && targetCat) {
                catValInput.value = targetCat;
                if (catSearchInput) catSearchInput.value = selectedCatName;
            }

            updateBulkRowTotal(idx);
            updateBulkSummary();

            try {
                if (window.refreshIcons) window.refreshIcons();
                else if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            } catch (e) { /* noop */ }
        } catch (err) {
            console.error('addBulkRow failed:', err);
        }
    }
    window.addBulkRow = addBulkRow;

    function applyFolderQualityToRow(idx, dtRaw) {
        const valInput = document.getElementById(`displayTypeVal_${idx}`);
        const labelEl = document.getElementById(`qualityLabel_${idx}`);
        const togglePill = document.getElementById(`qualityToggle_${idx}`);
        if (!valInput || !labelEl) return;

        const lower = (dtRaw || '').toLowerCase().trim();
        if (lower === 'hd+' || lower === 'hd_plus') {
            valInput.value = 'HD+';
            labelEl.textContent = 'HD+ ⚡';
            if (togglePill) {
                togglePill.className = 'quality-toggle-pill quality-pill-hd visible';
            }
        } else if (lower === 'og' || lower === 'original_oem') {
            valInput.value = 'OG';
            labelEl.textContent = 'OG ✨';
            if (togglePill) {
                togglePill.className = 'quality-toggle-pill quality-pill-og visible';
            }
        } else {
            valInput.value = 'Normal';
            labelEl.textContent = 'Normal';
            if (togglePill) {
                togglePill.className = 'quality-toggle-pill quality-pill-normal visible';
            }
        }
    }
    window.applyFolderQualityToRow = applyFolderQualityToRow;

    function toggleFolderQuality(idx) {
        const valInput = document.getElementById(`displayTypeVal_${idx}`);
        if (!valInput) return;

        const current = (valInput.value || '').toUpperCase();
        if (current === 'NORMAL' || current === 'NA' || !current) {
            applyFolderQualityToRow(idx, 'HD+');
        } else if (current === 'HD+' || current === 'HD_PLUS') {
            applyFolderQualityToRow(idx, 'OG');
        } else {
            applyFolderQualityToRow(idx, 'Normal');
        }
    }
    window.toggleFolderQuality = toggleFolderQuality;

    function addMultipleRows(count = 5) {
        for (let i = 0; i < count; i++) {
            addBulkRow();
        }
    }
    window.addMultipleRows = addMultipleRows;

    function removeBulkRow(idx) {
        const row = document.getElementById(`bulk-row-${idx}`);
        if (row) {
            row.remove();
            updateBulkSummary();
            validateAllRowsInstant();
        }
    }
    window.removeBulkRow = removeBulkRow;

    function clearAllRows() {
        if (confirm('Are you sure you want to clear all items in the batch?')) {
            document.getElementById('bulkTableBody').innerHTML = '';
            bulkRowIndex = 0;
            addBulkRow();
            updateBulkSummary();
            validateAllRowsInstant();
        }
    }
    window.clearAllRows = clearAllRows;

    function onBulkCategoryChange(idx, val, cleanName = false) {
        const canonicalVal = canonicalizeCategory(val);
        const isFolder = (canonicalVal === 'folder_display');

        const qualityToggle = document.getElementById(`qualityToggle_${idx}`);
        if (qualityToggle) {
            qualityToggle.classList.toggle('visible', isFolder || val === 'display_folder');
        }

        // Show/hide Brand input — folders don't need brand, only item name + description
        const brandWrap = document.getElementById(`brandWrap_${idx}`);
        const brandInput = document.getElementById(`brand_${idx}`);
        if (brandWrap) {
            brandWrap.style.display = isFolder ? 'none' : '';
            if (isFolder && brandInput) brandInput.value = ''; // clear brand for folders
        }

        const giftEl = document.getElementById(`gift_${idx}`);
        if (giftEl) {
            const isGift = (
                canonicalVal === 'tempered_glass' || 
                canonicalVal === 'back_cover' || 
                canonicalVal === 'charger_cable' || 
                canonicalVal === 'earphones_audio' || 
                canonicalVal === 'general_accessory'
            );
            giftEl.value = isGift ? '1' : '0';
        }

        const mobileBadge = document.getElementById(`mobileCardCatLabel_${idx}`);
        if (mobileBadge) {
            mobileBadge.textContent = formatCategoryLabel(canonicalVal);
        }

        // If combobox menu is open, re-render it with new category prioritization
        const menuEl = document.getElementById(`comboboxMenu_${idx}`);
        if (menuEl && menuEl.style.display !== 'none') {
            const searchInput = document.getElementById(`searchInput_${idx}`);
            renderComboboxMenu(idx, searchInput ? searchInput.value : '');
        }
    }

    function updateBulkRowTotal(idx) {
        const qtyVal = document.getElementById(`qty_${idx}`)?.value;
        const qty = (qtyVal !== '' && !isNaN(parseInt(qtyVal))) ? parseInt(qtyVal) : 0;
        const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value || 0);
        const sell = parseFloat(document.getElementById(`price_${idx}`)?.value || 0);
        const total = qty * cost;

        const lineTotalEl = document.getElementById(`lineTotal_${idx}`);
        if (lineTotalEl) lineTotalEl.textContent = formatCurrency(total);

        const mobileLineTotalEl = document.getElementById(`mobileLineTotal_${idx}`);
        if (mobileLineTotalEl) mobileLineTotalEl.textContent = formatCurrency(total);

        // Live preview of updated stock total
        const partId = document.getElementById(`partId_${idx}`)?.value;
        const stockAfterEl = document.getElementById(`stockAfter_${idx}`);
        const alertThreshold = parseInt(document.getElementById(`alert_${idx}`)?.value || 3);
        if (stockAfterEl) {
            if (partId) {
                const parts = Array.isArray(catalogParts) ? catalogParts : Object.values(catalogParts || {});
                const p = parts.find(item => item && String(item.id) === String(partId));
                const currentStock = parseInt(p?.stock_qty || 0);
                const newTotal = currentStock + qty;
                if (newTotal <= 0) {
                    stockAfterEl.innerHTML = `<span title="Current: ${currentStock} + Inward: ${qty} (Low Stock Alert)" style="color:#DC2626; font-weight:800;">⚠️ Total: <strong>0</strong> pcs (Low Stock)</span>`;
                } else if (newTotal <= alertThreshold) {
                    stockAfterEl.innerHTML = `<span title="Current: ${currentStock} + Inward: ${qty} (Low Stock Alert)" style="color:#D97706; font-weight:700;">⚠️ Total: <strong>${newTotal}</strong> pcs (Low Stock)</span>`;
                } else {
                    stockAfterEl.innerHTML = `<span title="Current: ${currentStock} + Inward: ${qty}" style="color:#059669; font-weight:700;">Total: <strong>${newTotal}</strong> pcs</span>`;
                }
                stockAfterEl.style.display = 'block';
            } else {
                const name = (document.getElementById(`name_${idx}`)?.value || '').trim();
                if (name) {
                    if (qty <= 0) {
                        stockAfterEl.innerHTML = `<span title="New item starting inventory - 0 quantity registered as Low Stock" style="color:#DC2626; font-weight:800;">⚠️ New: <strong>0</strong> pcs (Low Stock)</span>`;
                    } else if (qty <= alertThreshold) {
                        stockAfterEl.innerHTML = `<span title="New item starting inventory is below alert threshold" style="color:#D97706; font-weight:700;">⚠️ New: <strong>${qty}</strong> pcs (Low Stock)</span>`;
                    } else {
                        stockAfterEl.innerHTML = `<span title="New item starting inventory" style="color:#6366F1; font-weight:700;">New: <strong>${qty}</strong> pcs</span>`;
                    }
                    stockAfterEl.style.display = 'block';
                } else {
                    stockAfterEl.innerHTML = '';
                    stockAfterEl.style.display = 'none';
                }
            }
        }

        // Keep stock badge updated for new items dynamically
        if (!partId && (document.getElementById(`name_${idx}`)?.value || '').trim()) {
            renderStockBadge(idx, 0, true);
        }

        updateBulkSummary();
        validateAllRowsInstant();
    }
    window.updateBulkRowTotal = updateBulkRowTotal;

    /* ── Live Row Health & Instant Row Validation Engine ── */
    function validateAllRowsInstant() {
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-row');
        let errorCount = 0;
        let warningCount = 0;
        let validCount = 0;
        let firstErrorRowId = null;

        // Build name map to catch instant duplicates across batch
        const nameMap = {};
        rows.forEach(r => {
            const idMatch = r.id.match(/\d+$/);
            if (!idMatch) return;
            const i = parseInt(idMatch[0]);
            const nameVal = (document.getElementById(`searchInput_${i}`)?.value || document.getElementById(`name_${i}`)?.value || '').trim().toLowerCase();
            if (nameVal) {
                nameMap[nameVal] = nameMap[nameVal] || [];
                nameMap[nameVal].push(i);
            }
        });

        rows.forEach(r => {
            const idMatch = r.id.match(/\d+$/);
            if (!idMatch) return;
            const i = parseInt(idMatch[0]);
            const res = validateRowInstant(i, nameMap);
            if (res.status === 'error') {
                errorCount++;
                if (!firstErrorRowId) firstErrorRowId = r.id;
            } else if (res.status === 'warning') {
                warningCount++;
            } else if (res.status === 'valid') {
                validCount++;
            }
        });

        // Update header toolbar badge
        const healthEl = document.getElementById('batchHealthSummary');
        if (healthEl) {
            if (errorCount > 0) {
                healthEl.innerHTML = `
                    <span style="background:#FEE2E2; color:#B91C1C; border:1px solid #FCA5A5; padding:2px 8px; border-radius:12px; font-size:10.5px; font-weight:800; cursor:pointer;" onclick="const el=document.getElementById('${firstErrorRowId}'); if(el){ el.scrollIntoView({behavior:'smooth', block:'center'}); el.querySelector('input')?.focus(); }" title="Click to jump straight to first problem row">
                        ❌ ${errorCount} row${errorCount > 1 ? 's have' : ' has'} errors (Click to fix)
                    </span>
                `;
            } else if (warningCount > 0) {
                healthEl.innerHTML = `
                    <span style="background:#FEF3C7; color:#92400E; border:1px solid #FCD34D; padding:2px 8px; border-radius:12px; font-size:10.5px; font-weight:800;" title="Loss or Duplicate notice">
                        ⚠️ ${warningCount} row notice${warningCount > 1 ? 's' : ''} (Check pricing / duplicates)
                    </span>
                `;
            } else if (validCount > 0) {
                healthEl.innerHTML = `
                    <span style="background:#DCFCE7; color:#15803D; border:1px solid #86EFAC; padding:2px 8px; border-radius:12px; font-size:10.5px; font-weight:800;">
                        ✅ All ${validCount} items verified
                    </span>
                `;
            } else {
                healthEl.innerHTML = '';
            }
        }
    }
    window.validateAllRowsInstant = validateAllRowsInstant;

    function validateRowInstant(idx, nameMap = null) {
        const rowEl = document.getElementById(`bulk-row-${idx}`);
        if (!rowEl) return { status: 'pristine' };

        const nameInput = document.getElementById(`name_${idx}`);
        const searchInput = document.getElementById(`searchInput_${idx}`);
        const name = (searchInput?.value || nameInput?.value || '').trim();

        const qtyInput = document.getElementById(`qty_${idx}`);
        const qty = parseInt(qtyInput?.value || 0);

        const costInput = document.getElementById(`cost_${idx}`);
        const rawCost = costInput ? costInput.value : '';
        const cost = parseFloat(rawCost || 0);

        const priceInput = document.getElementById(`price_${idx}`);
        const rawPrice = priceInput ? priceInput.value : '';
        const price = parseFloat(rawPrice || 0);

        const warnBox = document.getElementById(`rowWarning_${idx}`);
        const priceWarn = document.getElementById(`priceWarning_${idx}`);

        // Pristine empty row
        const isPristine = (!name && (!rawCost || rawCost === '') && (!rawPrice || rawPrice === ''));
        if (isPristine) {
            rowEl.className = rowEl.className.replace(/row-state-\w+/g, '').trim() + ' row-state-pristine';
            if (warnBox) { warnBox.style.display = 'none'; warnBox.innerHTML = ''; }
            if (priceWarn) { priceWarn.style.display = 'none'; priceWarn.innerHTML = ''; }
            if (searchInput) searchInput.style.borderColor = '';
            if (qtyInput) qtyInput.style.borderColor = '';
            if (costInput) costInput.style.borderColor = '';
            if (priceInput) { priceInput.style.borderColor = ''; priceInput.style.background = ''; }
            return { status: 'pristine' };
        }

        let errorMsg = null;
        let warningMsg = null;
        let priceMsg = null;

        // Check 1: Missing Item Name
        if (!name) {
            errorMsg = '❌ Item name required';
            if (searchInput) searchInput.style.borderColor = '#DC2626';
        } else {
            if (searchInput) searchInput.style.borderColor = '';
        }

        // Check 2: Invalid Qty
        if (isNaN(qty) || qty < 0) {
            errorMsg = errorMsg || '❌ Qty cannot be negative';
            if (qtyInput) qtyInput.style.borderColor = '#DC2626';
        } else {
            if (qtyInput) qtyInput.style.borderColor = '';
        }

        // Check 3: Negative Cost (Cost CAN be 0.00)
        if (isNaN(cost) || cost < 0) {
            errorMsg = errorMsg || '❌ Cost cannot be negative';
            if (costInput) costInput.style.borderColor = '#DC2626';
        } else {
            if (costInput) costInput.style.borderColor = '';
        }

        // Check 4: Negative Price & Loss Margin (Sell price CAN be 0.00)
        if (isNaN(price) || price < 0) {
            errorMsg = errorMsg || '❌ Sell price cannot be negative';
            if (priceInput) priceInput.style.borderColor = '#DC2626';
        } else if (cost > 0 && price > 0 && price < cost) {
            const lossPerUnit = (cost - price).toFixed(2);
            priceMsg = `⚠️ Loss: -₹${lossPerUnit}/pc`;
            warningMsg = warningMsg || `⚠️ Selling at Loss (-₹${lossPerUnit} / unit: Sell ₹${price.toFixed(2)} < Cost ₹${cost.toFixed(2)})`;
            if (priceInput) {
                priceInput.style.borderColor = '#DC2626';
                priceInput.style.background = '#FEF2F2';
            }
        } else {
            if (priceInput) {
                priceInput.style.borderColor = '';
                priceInput.style.background = '';
            }
        }

        // Check 5: Duplicate Items in same batch
        if (name && nameMap) {
            const lowerName = name.toLowerCase();
            const matches = nameMap[lowerName] || [];
            if (matches.length > 1) {
                const otherIdxs = matches.filter(id => id !== idx);
                if (otherIdxs.length > 0) {
                    warningMsg = `⚠️ Duplicate: Also in Row #${otherIdxs.join(', #')}`;
                }
            }
        }

        // Render Price Warning
        if (priceWarn) {
            if (priceMsg) {
                priceWarn.innerHTML = `<span style="color:#DC2626; font-size:9.5px; font-weight:800; display:block; line-height:1.2;">${priceMsg}</span>`;
                priceWarn.style.display = 'block';
            } else {
                priceWarn.style.display = 'none';
                priceWarn.innerHTML = '';
            }
        }

        // Render Row Warning Box
        if (warnBox) {
            if (errorMsg) {
                warnBox.innerHTML = `<span class="instant-row-badge badge-err">${errorMsg}</span>`;
                warnBox.style.display = 'inline-block';
            } else if (warningMsg) {
                warnBox.innerHTML = `<span class="instant-row-badge badge-warn">${warningMsg}</span>`;
                warnBox.style.display = 'inline-block';
            } else {
                const margin = (price > 0 && cost > 0) ? Math.round(((price - cost) / price) * 100) : 0;
                const profit = (price - cost).toFixed(2);
                const infoText = (price > 0 && cost > 0) ? `• Margin: +₹${profit} (${margin}%)` : '';
                warnBox.innerHTML = `<span class="instant-row-badge badge-ok">✅ Ready ${infoText}</span>`;
                warnBox.style.display = 'inline-block';
            }
        }

        // Apply row border state
        rowEl.className = rowEl.className.replace(/row-state-\w+/g, '').trim();
        if (errorMsg) {
            rowEl.className += ' row-state-error';
            return { status: 'error', message: errorMsg };
        } else if (warningMsg) {
            rowEl.className += ' row-state-warning';
            return { status: 'warning', message: warningMsg };
        } else {
            rowEl.className += ' row-state-valid';
            return { status: 'valid' };
        }
    }
    window.validateRowInstant = validateRowInstant;

    function adjustBulkQty(idx, delta) {
        const input = document.getElementById(`qty_${idx}`);
        if (!input) return;
        let val = parseInt(input.value) || 0;
        val += delta;
        if (val < 0) val = 0;
        input.value = val;
        updateBulkRowTotal(idx);
    }
    window.adjustBulkQty = adjustBulkQty;


    function updateBulkSummary() {
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-row');
        let totalUnits = 0;
        let calculatedItemCost = 0.0;
        let totalSellValue = 0.0;

        rows.forEach(r => {
            const qtyInput = r.querySelector('input[name*="[qty]"]');
            const costInput = r.querySelector('input[name*="[unit_cost]"]');
            const sellInput = r.querySelector('input[name*="[selling_price]"]');
            if (qtyInput && costInput) {
                const q = parseInt(qtyInput.value) || 0;
                const c = parseFloat(costInput.value) || 0;
                const s = sellInput ? (parseFloat(sellInput.value) || 0) : 0;
                totalUnits += q;
                calculatedItemCost += (q * c);
                totalSellValue += (q * s);
            }
        });

        // Determine effective bill cost (custom price or items total)
        const effectiveBillCost = (bulkPriceMode === 'custom' && customBillAmount !== null)
            ? customBillAmount
            : calculatedItemCost;

        const formattedCost = formatCurrency(effectiveBillCost);
        const formattedSell = formatCurrency(totalSellValue);

        const rowCountEl = document.getElementById('bulkRowCount');
        if (rowCountEl) rowCountEl.textContent = rows.length;

        const lineCountEl = document.getElementById('lblBulkLineCount');
        if (lineCountEl) lineCountEl.textContent = `${rows.length} lines`;

        const totalUnitsEl = document.getElementById('lblBulkTotalUnits');
        if (totalUnitsEl) totalUnitsEl.textContent = `${totalUnits} units`;

        const totalCostEl = document.getElementById('lblBulkTotalCost');
        if (totalCostEl) totalCostEl.textContent = formattedCost;

        const totalSellEl = document.getElementById('lblBulkTotalSellValue');
        if (totalSellEl) totalSellEl.textContent = formattedSell;

        const retailTotalEl = document.getElementById('lblBulkRetailTotal');
        if (retailTotalEl) retailTotalEl.textContent = formattedSell;

        const autoTagEl = document.getElementById('lblAutoTotalTag');
        if (autoTagEl) autoTagEl.textContent = formatCurrency(calculatedItemCost);

        // Mobile Sticky Dock elements
        const mobileTotalEl = document.getElementById('mobileStickyTotal');
        if (mobileTotalEl) mobileTotalEl.textContent = formattedCost;

        const mobileCountEl = document.getElementById('mobileStickyCount');
        if (mobileCountEl) mobileCountEl.textContent = `${rows.length} ITEMS • ${totalUnits} UNITS`;

        // Supplier Ledger & Settlement sync
        const s = getSelectedSupplier();
        const outBal = s ? (parseFloat(s.outstanding_balance) || 0) : 0;

        const btnBillAmt = document.getElementById('btnLblBillAmt');
        if (btnBillAmt) btnBillAmt.textContent = formatCurrency(effectiveBillCost);

        const btnClearAll = document.getElementById('btnLblClearAll');
        if (btnClearAll) btnClearAll.textContent = formatCurrency(effectiveBillCost + outBal);

        const paidInput = document.getElementById('bulkAmountPaid');
        const rawPaidStr = paidInput ? (paidInput.value !== '' ? paidInput.value.trim() : '') : '';

        // If user hasn't explicitly entered anything or input is untouched, default to effectiveBillCost
        let actualPaid = (rawPaidStr !== '' && rawPaidStr !== null && !isNaN(parseFloat(rawPaidStr)))
            ? parseFloat(rawPaidStr)
            : effectiveBillCost;

        // Update live Supplier Debt Breakdown box
        const totalDueWithPast = effectiveBillCost + outBal;
        const remainingDebt = Math.max(0, totalDueWithPast - actualPaid);

        const elPastDebt = document.getElementById('valSupplierPastDebt');
        if (elPastDebt) elPastDebt.textContent = formatCurrency(outBal);

        const elBillAmt = document.getElementById('valCurrentBillAmt');
        if (elBillAmt) elBillAmt.textContent = formatCurrency(effectiveBillCost);

        const elTotalPayable = document.getElementById('valTotalPayable');
        if (elTotalPayable) elTotalPayable.textContent = formatCurrency(totalDueWithPast);

        const elAmtPaidDisp = document.getElementById('valAmountPaidDisplay');
        if (elAmtPaidDisp) elAmtPaidDisp.textContent = formatCurrency(actualPaid);

        const elRemDebt = document.getElementById('valSupplierRemainingDebt');
        if (elRemDebt) {
            elRemDebt.textContent = formatCurrency(remainingDebt);
            elRemDebt.style.color = remainingDebt > 0 ? '#DC2626' : '#15803D';
        }

        const pill = document.getElementById('bulkSettlementPill');
        const mobSettlement = document.getElementById('mobileStickySettlement');

        if (pill) {
            if (rawPaidStr === '') {
                pill.style.background = '#EEF2FF';
                pill.style.color = '#4338CA';
                pill.innerHTML = `Default: <strong>Full Invoice Payment (₹${effectiveBillCost.toFixed(2)})</strong>. Change amount to clear balance.`;
            } else if (actualPaid <= 0) {
                pill.style.background = '#FEF2F2';
                pill.style.color = '#B91C1C';
                pill.innerHTML = `⚠️ <strong>Full Credit (Udhari)</strong>: Entire ₹${effectiveBillCost.toFixed(2)} will be added to supplier balance`;
            } else if (actualPaid < effectiveBillCost) {
                const due = effectiveBillCost - actualPaid;
                pill.style.background = '#FFFBEB';
                pill.style.color = '#B45309';
                pill.innerHTML = `🟡 <strong>Partial Payment (₹${actualPaid.toFixed(2)})</strong>: Remaining invoice due ₹${due.toFixed(2)} added to supplier debt`;
            } else if (Math.abs(actualPaid - effectiveBillCost) < 0.01) {
                pill.style.background = '#ECFDF5';
                pill.style.color = '#047857';
                pill.innerHTML = `✅ <strong>Invoice Paid in Full (₹${effectiveBillCost.toFixed(2)})</strong>: Supplier balance unaffected`;
            } else {
                const excess = actualPaid - effectiveBillCost;
                if (outBal > 0) {
                    const cleared = Math.min(excess, outBal);
                    const remainingDue = Math.max(0, outBal - cleared);
                    pill.style.background = '#F0FDF4';
                    pill.style.color = '#15803D';
                    if (remainingDue <= 0.01) {
                        pill.innerHTML = `🎉 <strong>100% Cleared Out!</strong> Invoice paid + ₹${cleared.toFixed(2)} completely CLEARS supplier's previous debt!`;
                    } else {
                        pill.innerHTML = `🟢 <strong>Invoice Paid + Clears ₹${cleared.toFixed(2)}</strong> from past debt (Remaining past due: ₹${remainingDue.toFixed(2)})`;
                    }
                } else {
                    pill.style.background = '#EEF2FF';
                    pill.style.color = '#4338CA';
                    pill.innerHTML = `💎 <strong>Invoice Paid + ₹${excess.toFixed(2)} Advance</strong> credited to supplier prepaid wallet`;
                }
            }
        }

        if (mobSettlement) {
            mobSettlement.textContent = `Paid: ₹${(actualPaid || 0).toFixed(2)}`;
        }
    }

    function validateAndSubmitBulkRestock(form) {
        const rows = document.querySelectorAll('#bulkTableBody .batch-item-row');
        if (rows.length === 0) {
            alert('⚠️ Please add at least 1 item to the batch intake.');
            return false;
        }

        let filledRows = [];
        let trailingEmptyCount = 0;

        // 1. Sync fields and separate completely empty rows from active rows
        rows.forEach((r, idx) => {
            const nameInput = r.querySelector('input[name*="[name]"]');
            const searchInput = r.querySelector('.search-combobox-input:not(.cat-search-input)');
            const rawName = (nameInput?.value || searchInput?.value || '').trim();
            const partId = r.querySelector('input[name*="[part_id]"]')?.value;
            const costVal = parseFloat(r.querySelector('input[name*="[unit_cost]"]')?.value || 0);

            // Row is completely un-entered if name and partId are both missing
            if (!rawName && !partId) {
                r.remove();
                trailingEmptyCount++;
            } else {
                // Explicitly sync text into submitted name input
                if (nameInput) nameInput.value = rawName;
                if (searchInput) searchInput.value = rawName;

                // Explicitly sync category
                const catValEl = r.querySelector('input[name*="[category]"]');
                const catSearchEl = r.querySelector('.cat-search-input');
                if (catValEl && (!catValEl.value || catValEl.value === '')) {
                    const guessedCat = canonicalizeCategory('', catSearchEl?.value || rawName);
                    catValEl.value = guessedCat || 'general_accessory';
                }

                filledRows.push({ row: r, displayIndex: filledRows.length + 1 });
            }
        });

        if (filledRows.length === 0) {
            // Re-add one fresh blank row so user can start typing
            addBulkRow();
            alert('⚠️ Please enter at least 1 valid product item name, quantity, and cost.');
            return false;
        }

        // 2. Validate every active filled row
        for (let item of filledRows) {
            const r = item.row;
            const rowNum = item.displayIndex;
            const nameInput = r.querySelector('input[name*="[name]"]');
            const searchInput = r.querySelector('.search-combobox-input:not(.cat-search-input)');
            const name = (nameInput?.value || searchInput?.value || '').trim();

            const qtyInput = r.querySelector('input[name*="[qty]"]');
            const qty = parseInt(qtyInput?.value || 0);

            const costInput = r.querySelector('input[name*="[unit_cost]"]');
            const cost = parseFloat(costInput?.value || 0);

            const priceInput = r.querySelector('input[name*="[selling_price]"]');
            const sell = parseFloat(priceInput?.value || 0);

            // Validate Name
            if (!name) {
                r.scrollIntoView({ behavior: 'smooth', block: 'center' });
                r.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.4)';
                if (searchInput) {
                    searchInput.focus();
                    searchInput.style.borderColor = '#DC2626';
                }
                alert(`⚠️ Item Name Missing on Row #${rowNum}:\nPlease pick an existing inventory item or type a product name.`);
                return false;
            }

            // Validate Quantity
            if (isNaN(qty) || qty < 0) {
                r.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.style.borderColor = '#DC2626';
                }
                alert(`⚠️ Invalid Quantity on Row #${rowNum} ("${name}"):\nQuantity cannot be negative (currently: ${qtyInput?.value || 0}).`);
                return false;
            }

            // Validate Unit Cost (Cost CAN be 0.00)
            if (isNaN(cost) || cost < 0) {
                r.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (costInput) {
                    costInput.focus();
                    costInput.style.borderColor = '#DC2626';
                }
                alert(`⚠️ Invalid Buy Cost on Row #${rowNum} ("${name}"):\nCost price cannot be negative.`);
                return false;
            }

            // Validate Selling Price (Price CAN be 0.00)
            if (!isNaN(sell) && sell < 0) {
                r.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (priceInput) {
                    priceInput.focus();
                    priceInput.style.borderColor = '#DC2626';
                }
                alert(`⚠️ Invalid Retail Price on Row #${rowNum} ("${name}"):\nSelling price cannot be negative.`);
                return false;
            }

            // Margin / Loss Warning: sell < cost (only when cost > 0 and sell > 0)
            if (cost > 0 && sell > 0 && sell < cost) {
                const proceedLoss = confirm(`⚠️ PRICING WARNING on Row #${rowNum} ("${name}"):\n\nRetail Selling Price (₹${sell.toFixed(2)}) is LESS than Buy Cost (₹${cost.toFixed(2)}).\nThis item will incur a loss of ₹${(cost - sell).toFixed(2)} per unit.\n\nDo you want to proceed with this price? Click OK to proceed, or Cancel to correct.`);
                if (!proceedLoss) {
                    r.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (priceInput) {
                        priceInput.focus();
                        priceInput.select();
                    }
                    return false;
                }
            }
        }

        // Re-calculate totals
        updateBulkSummary();

        // Prevent double submit without aborting native form submission
        setTimeout(() => {
            const btn = document.getElementById('btnSubmitBulkRestock');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader-2" style="width:16px;height:16px;" class="spin"></i> Processing Batch Restock...';
            }
            const mobBtn = document.getElementById('btnMobileSubmitRestock');
            if (mobBtn) {
                mobBtn.disabled = true;
                mobBtn.innerHTML = '<i data-lucide="loader-2" style="width:16px;height:16px;" class="spin"></i> Processing...';
            }
        }, 50);

        return true;
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    async function handleInvoiceFile(file) {
        if (!file) return;

        const fileInput = document.getElementById('invoiceFileInput');
        const progressBox = document.getElementById('ocrProgressBox');
        const progressBar = document.getElementById('ocrProgressBar');
        const statusText = document.getElementById('ocrStatusText');
        const percentText = document.getElementById('ocrPercentText');

        if (file.size > 15 * 1024 * 1024) {
            alert('File size exceeds 15MB. Please upload or snap a clearer, compressed photo.');
            if (fileInput) fileInput.value = '';
            return;
        }

        progressBox.style.display = 'block';
        progressBar.style.width = '15%';
        statusText.textContent = '🚀 Uploading invoice slip...';
        percentText.textContent = '15%';

        // Progressive indicator ticks
        const progressTimer1 = setTimeout(() => {
            progressBar.style.width = '45%';
            statusText.textContent = '🔍 Gemini AI reading handwriting, ditto marks & items...';
            percentText.textContent = '45%';
        }, 1200);

        const progressTimer2 = setTimeout(() => {
            progressBar.style.width = '75%';
            statusText.textContent = '⚙️ Extracting display folders, quantities & wholesale rates...';
            percentText.textContent = '75%';
        }, 3200);

        try {
            const formData = new FormData();
            formData.append('invoice_image', file);

            const response = await fetch("{{ route('mobileshop.purchase.scan_invoice') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            clearTimeout(progressTimer1);
            clearTimeout(progressTimer2);

            const json = await response.json();
            if (fileInput) fileInput.value = '';

            if (!response.ok || !json.success) {
                progressBox.style.display = 'none';
                const errMsg = json.message || 'Could not scan the invoice image. Please check camera clarity or try again.';
                alert('OCR Scan Notice: ' + errMsg);
                return;
            }

            progressBar.style.width = '100%';
            statusText.textContent = '✅ Extraction complete! Populating restock items...';
            percentText.textContent = '100%';

            setTimeout(() => {
                progressBox.style.display = 'none';
            }, 1200);

            const ocrData = (json.data && json.data.items) ? json.data : json;
            populateOcrDataIntoTable(ocrData);

        } catch (err) {
            clearTimeout(progressTimer1);
            clearTimeout(progressTimer2);
            if (fileInput) fileInput.value = '';
            console.error('OCR Upload Error:', err);
            progressBox.style.display = 'none';
            alert('Network error while processing document: ' + err.message);
        }
    }

    function populateOcrDataIntoTable(ocrData) {
        if (!ocrData) return;

        // 1. Supplier Name auto-population
        if (ocrData.supplier_name) {
            const suppInput = document.getElementById('bulkSupplierName');
            if (suppInput) {
                suppInput.value = ocrData.supplier_name;
                if (typeof onSupplierSelected === 'function') {
                    onSupplierSelected();
                }
            }
        }

        // 2. Invoice / PO / Slip No
        const invNo = ocrData.supplier_invoice_no || ocrData.invoice_number || ocrData.invoice_no;
        if (invNo) {
            const invInput = document.getElementById('bulkInvoiceNo');
            if (invInput) {
                invInput.value = invNo;
            }
        }

        // 3. Intake Date
        if (ocrData.invoice_date) {
            const dateInput = document.querySelector('input[name="order_date"]');
            if (dateInput) {
                dateInput.value = ocrData.invoice_date;
            }
        }

        // 4. Populate Items
        const items = ocrData.items || [];
        if (!items || items.length === 0) {
            alert('AI processed the slip, but could not detect any item lines. Please verify image clarity.');
            return;
        }

        const tbody = document.getElementById('bulkTableBody');
        tbody.innerHTML = '';
        bulkRowIndex = 0;

        items.forEach(it => {
            const rawName = it.name || it.model || 'Item';
            const cat = canonicalizeCategory(it.category, rawName);
            let cleanedName = cleanItemNameForCategory(rawName, cat);
            const nameLower = cleanedName.toLowerCase();

            let displayType = 'Normal';
            if (/hd\+|\bhd plus\b|\bhdplus\b/i.test(nameLower) || (it.display_type && (it.display_type.toLowerCase() === 'hd_plus' || it.display_type.toUpperCase() === 'HD+'))) {
                displayType = 'HD+';
            } else if (/og\b|\boriginal/i.test(nameLower) || (it.display_type && (it.display_type.toUpperCase() === 'OG' || it.display_type.toLowerCase() === 'original_oem'))) {
                displayType = 'OG';
            }

            const qty = (it.qty !== undefined && it.qty !== null && !isNaN(parseInt(it.qty)))
                ? Math.max(0, parseInt(it.qty))
                : 1;
            const cost = parseFloat(it.unit_cost) || 0;
            let sellingPrice = parseFloat(it.selling_price) || 0;
            if (sellingPrice < cost && cost > 0) {
                if (cat === 'folder_display' || cat === 'display_folder') {
                    sellingPrice = cost + 250;
                } else if (cat === 'tempered_glass' || cat === 'back_cover' || cat === 'back_cover_case') {
                    sellingPrice = Math.round(cost * 2.5);
                } else {
                    sellingPrice = Math.round(cost * 1.35);
                }
            }

            addBulkRow({
                name: cleanedName,
                category: cat,
                brand: '',
                compatible_model: '',
                display_type: displayType,
                qty: qty,
                unit_cost: cost,
                selling_price: sellingPrice,
                is_gift_eligible: (cat === 'tempered_glass' || cat === 'back_cover' || cat === 'charger_cable' || cat === 'earphones_audio' || cat === 'general_accessory') ? 1 : 0
            });
        });

        updateBulkSummary();

        const supplierName = ocrData.supplier_name || 'Vendor Slip';
        const totalFormatted = ocrData.bill_total ? '₹' + parseFloat(ocrData.bill_total).toLocaleString('en-IN') : '';
        const flashMsg = `🎉 Extracted ${items.length} items from ${supplierName} ${totalFormatted ? '(' + totalFormatted + ')' : ''}! Review rows and click Process Restock.`;

        const banner = document.createElement('div');
        banner.style.cssText = 'background:#ECFDF5; border:1px solid #10B981; color:#065F46; padding:12px 16px; border-radius:8px; font-weight:700; font-size:13px; display:flex; align-items:center; gap:8px; margin-top:10px;';
        banner.innerHTML = `<span>${flashMsg}</span>`;
        const container = document.querySelector('.restock-container');
        const form = document.getElementById('bulkRestockForm');
        if (container && form) {
            container.insertBefore(banner, form);
            setTimeout(() => banner.remove(), 8000);
        }
    }

    function loadSampleInvoiceData() {
        document.getElementById('bulkSupplierName').value = 'National Mobile Wholesale Hub, Mumbai';
        document.getElementById('bulkInvoiceNo').value = 'INV-2026-8942';

        document.getElementById('bulkTableBody').innerHTML = '';
        bulkRowIndex = 0;

        const sampleItems = [
            { name: '9D Super Clear Tempered Glass (iPhone 14/15)', category: 'tempered_glass', brand: 'Apple', compatible_model: '', qty: 50, unit_cost: 22.00, selling_price: 149.00, is_gift_eligible: 1 },
            { name: 'Matte Smoke Anti-Drop Bumper Case (Galaxy S24)', category: 'back_cover', brand: 'Samsung', compatible_model: '', qty: 25, unit_cost: 45.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Original OLED Display Screen Folder (iPhone 14)', category: 'folder_display', brand: 'Apple', compatible_model: '', display_type: 'OG', qty: 5, unit_cost: 1650.00, selling_price: 2499.00, is_gift_eligible: 0 },
            { name: 'HD+ High Definition Folder Combo (Redmi Note 12)', category: 'folder_display', brand: 'Xiaomi', compatible_model: '', display_type: 'HD+', qty: 10, unit_cost: 850.00, selling_price: 1499.00, is_gift_eligible: 0 },
            { name: 'Samsung Galaxy A54 Front Outer Glass with OCA', category: 'front_glass', brand: 'Samsung', compatible_model: '', qty: 15, unit_cost: 110.00, selling_price: 399.00, is_gift_eligible: 0 },
            { name: 'Type-C 65W Braided Fast Charging Cable (1.5m)', category: 'charger_cable', brand: '', compatible_model: '', qty: 30, unit_cost: 38.00, selling_price: 199.00, is_gift_eligible: 1 },
            { name: 'Universal Type-C Charging Pin Connector Jack', category: 'charging_port', brand: '', compatible_model: '', qty: 40, unit_cost: 12.00, selling_price: 99.00, is_gift_eligible: 0 },
            { name: 'High Capacity 5000mAh Battery (Redmi Note 12)', category: 'battery', brand: 'Xiaomi', compatible_model: '', qty: 8, unit_cost: 340.00, selling_price: 799.00, is_gift_eligible: 0 }
        ];

        sampleItems.forEach(item => addBulkRow(item));
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize 1 starter row if table is empty
        const tbody = document.getElementById('bulkTableBody');
        if (tbody && tbody.querySelectorAll('.batch-item-row').length === 0) {
            addBulkRow();
        }
    });
</script>