    <div id="quickEditStockModal" style="display:none; position:fixed; inset:0; z-index:1250; background:rgba(15,23,42,0.65); backdrop-filter:blur(5px); align-items:center; justify-content:center; padding:16px;">
        <div class="card" style="max-width:580px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); max-height:90vh; display:flex; flex-direction:column; overflow:hidden; border-radius:12px; margin:0; border:1px solid #334155;">
            <div class="card-header" style="background:linear-gradient(135deg, #1E293B 0%, #0F172A 100%); color:#fff; border-bottom:1px solid #334155; padding:14px 18px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:7px; background:rgba(99,102,241,0.25); color:#818CF8; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="edit-3" style="width:18px;height:18px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="color:#fff; font-size:15px; margin:0; font-weight:800;" id="quickEditModalTitle">Edit Stock Item</div>
                        <div class="card-subtitle" style="color:#94A3B8; font-size:11px; margin-top:2px;">Adjust catalog baseline stock count, buy cost, or retail selling price</div>
                    </div>
                </div>
                <button type="button" onclick="closeQuickEditStockModal()" style="background:transparent; border:none; color:#94A3B8; font-size:20px; cursor:pointer; padding:4px 8px; line-height:1; border-radius:6px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">✕</button>
            </div>

            <form id="quickEditStockForm" onsubmit="submitQuickEditStockForm(event)" style="display:flex; flex-direction:column; flex:1; overflow-y:auto; margin:0;">
                <input type="hidden" id="quickEditRowIdx" value="">
                <input type="hidden" id="quickEditPartId" value="">
                <input type="hidden" name="item_type" value="part">

                <div style="padding:18px; display:flex; flex-direction:column; gap:12px; background:#FFFFFF;">
                    <div id="quickEditError" style="display:none; padding:8px 12px; border-radius:6px; background:#FEF2F2; border:1px solid #FCA5A5; color:#DC2626; font-size:11.5px; font-weight:600;"></div>

                    <div>
                        <label style="display:block; font-size:11.5px; font-weight:700; color:#334155; margin-bottom:3px;">Item Name <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="name" id="quickEditName" required class="restock-input" style="font-weight:700; font-size:13px;">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div>
                            <label style="display:block; font-size:11.5px; font-weight:700; color:#334155; margin-bottom:3px;">Category</label>
                            <select name="category" id="quickEditCategory" class="restock-input">
                                @if(isset($categories) && count($categories) > 0)
                                    @foreach($categories as $c)<option value="{{ $c->slug }}">{{ $c->name }}</option>@endforeach
                                @endif
                                <option value="general_accessory">General Accessory</option>
                                <option value="tempered_glass">Tempered Glass</option>
                                <option value="back_cover">Back Cover / Case</option>
                                <option value="folder_display">Folder / Combo Screen</option>
                                <option value="battery">Battery</option>
                                <option value="charging_port">Charging Port</option>
                                <option value="front_glass">Front Outer Glass</option>
                                <option value="charger_cable">Charger & Cable</option>
                                <option value="earphones_audio">Earphones & Audio</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:11.5px; font-weight:700; color:#334155; margin-bottom:3px;">Fits Brand / Model</label>
                            <input type="text" name="compatible_model" id="quickEditModel" class="restock-input" placeholder="e.g. Universal / iPhone 15">
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:11.5px; font-weight:700; color:#334155; margin-bottom:3px;">Display / Folder Quality Tier</label>
                        <select name="display_type" id="quickEditDisplayType" class="restock-input" style="font-weight:600;">
                            <option value="na">Standard / Normal Screen</option>
                            <option value="hd_plus">HD+ (High Definition Plus) ⚡</option>
                            <option value="original_oem">OG ✨ (Original OEM Screen)</option>
                            <option value="oled">OLED Screen</option>
                            <option value="in_cell">In-Cell LCD</option>
                        </select>
                    </div>

                    <!-- Hero Stock Qty Box -->
                    <div style="background:#F0FDF4; border:1.5px solid #86EFAC; border-radius:8px; padding:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-size:11.5px; font-weight:800; color:#166534; text-transform:uppercase; margin:0;">
                                Current Physical Stock Count
                            </label>
                            <span id="quickEditStockDiffBadge" style="font-size:10.5px; font-weight:700; color:#15803D; background:#DCFCE7; padding:2px 6px; border-radius:4px;">
                                Baseline: 0 units
                            </span>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button type="button" onclick="adjustQuickEditStock(-1)" class="btn btn-outline btn-xs" style="font-weight:800; border-color:#86EFAC; color:#166534; background:#FFFFFF;">-1</button>
                            <input type="number" name="stock_qty" id="quickEditStockQty" min="0" step="1" required class="restock-input num-field" style="width:110px; font-size:16px; font-weight:900; text-align:center; color:#166534; border:2px solid #22C55E;" oninput="onQuickEditStockChanged()">
                            <button type="button" onclick="adjustQuickEditStock(1)" class="btn btn-outline btn-xs" style="font-weight:800; border-color:#86EFAC; color:#166534; background:#FFFFFF;">+1</button>
                            <button type="button" onclick="adjustQuickEditStock(5)" class="btn btn-outline btn-xs" style="font-weight:800; border-color:#86EFAC; color:#166534; background:#FFFFFF;">+5</button>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div>
                            <label style="display:block; font-size:11.5px; font-weight:700; color:#475569; margin-bottom:3px;">Buy Cost (₹)</label>
                            <input type="number" name="unit_cost" id="quickEditCost" min="0" step="0.01" class="restock-input num-field cost-field" required>
                        </div>
                        <div>
                            <label style="display:block; font-size:11.5px; font-weight:700; color:#15803D; margin-bottom:3px;">Retail Price (₹)</label>
                            <input type="number" name="selling_price" id="quickEditPrice" min="0" step="0.01" class="restock-input num-field sell-field" required>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:11.5px; font-weight:700; color:#475569; margin-bottom:3px;">Adjustment Reason (Audit Trail)</label>
                        <input type="text" name="adjustment_reason" id="quickEditReason" class="restock-input" placeholder="e.g. Correcting stock count during purchase intake">
                    </div>
                </div>

                <div style="padding:12px 18px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                    <button type="button" onclick="closeQuickEditStockModal()" class="btn btn-outline btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnQuickEditSave" style="background:#4F46E5; border-color:#4F46E5; font-weight:800;">
                        Save &amp; Apply
                    </button>
                </div>
            </form>
        </div>
    </div>