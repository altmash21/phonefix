/**
 * MobiTrack Universal UI Utilities & Table Pagination Engine
 * Shared by all mobile shop views via layout.blade.php
 */
(function () {
    'use strict';

    /* ─── Lucide icon initialisation & auto-refresh ───
       Renders icons on load AND watches the DOM so dynamically
       injected <i data-lucide> elements (modals, filtered tables,
       FAB menus, JS-rendered rows) are also converted to SVGs. */
    window.refreshIcons = function () {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            try { window.lucide.createIcons(); } catch (e) { /* noop */ }
        }
    };

    function initIcons() {
        window.refreshIcons();

        // Re-render icons whenever new [data-lucide] nodes appear (debounced)
        var iconTimer = null;
        if ('MutationObserver' in window) {
            var observer = new MutationObserver(function (mutations) {
                var hasNewIcons = false;
                for (var i = 0; i < mutations.length; i++) {
                    if (mutations[i].addedNodes && mutations[i].addedNodes.length) {
                        for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                            var node = mutations[i].addedNodes[j];
                            if (node.nodeType === 1) {
                                if ((node.matches && node.matches('[data-lucide]')) ||
                                    (node.querySelector && node.querySelector('[data-lucide]'))) {
                                    hasNewIcons = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (hasNewIcons) break;
                }
                if (hasNewIcons) {
                    if (iconTimer) clearTimeout(iconTimer);
                    iconTimer = setTimeout(function () { window.refreshIcons(); }, 50);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIcons);
    } else {
        initIcons();
    }

    /* ─── Date helpers ─── */
    window.formatDate = window.formatDate || function (d) {
        if (!d) return '';
        var dt = d instanceof Date ? d : new Date(d);
        var y = dt.getFullYear();
        var m = String(dt.getMonth() + 1).padStart(2, '0');
        var day = String(dt.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    };

    window.getDateRangePreset = window.getDateRangePreset || function (preset) {
        var now = new Date();
        var y = now.getFullYear();
        var m = now.getMonth();
        var d = now.getDate();

        var formatDateStr = function (year, month, day) {
            var mStr = (month + 1 < 10 ? '0' : '') + (month + 1);
            var dStr = (day < 10 ? '0' : '') + day;
            return year + '-' + mStr + '-' + dStr;
        };

        var todayStr = formatDateStr(y, m, d);
        var from = '';
        var to = '';

        var p = String(preset || '').toLowerCase();
        if (p === 'today') {
            from = to = todayStr;
        } else if (p === 'yesterday') {
            var yestDate = new Date(y, m, d - 1);
            from = to = formatDateStr(yestDate.getFullYear(), yestDate.getMonth(), yestDate.getDate());
        } else if (p === 'week' || p === '7days' || p === '7_days' || p === '7-days') {
            var weekAgo = new Date(y, m, d - 6);
            from = formatDateStr(weekAgo.getFullYear(), weekAgo.getMonth(), weekAgo.getDate());
            to = todayStr;
        } else if (p === 'month' || p === 'this_month' || p === 'this-month') {
            from = formatDateStr(y, m, 1);
            var lastDay = new Date(y, m + 1, 0).getDate();
            to = formatDateStr(y, m, lastDay);
        }

        return { from: from, to: to };
    };

    /* ─── HTML escaping ─── */
    window.escapeHtml = function (str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    /* ─── Client-side table filter ─── */
    window.filterTable = function (tableId, query) {
        var q = (query || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        rows.forEach(function (row) {
            row.style.display = !q || row.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
        });
    };

    /* ─── MobiTrack Universal Table & Card Pagination Engine ─── */
    window.setupMobiTablePagination = function (config) {
        var tableId = config.tableId || null;
        var cardsContainerId = config.cardsContainerId || null;
        var paginationContainerId = config.paginationContainerId;
        var rowSelector = config.rowSelector || 'tbody tr:not(.empty-placeholder):not(.non-data-row)';
        var cardSelector = config.cardSelector || '.mobile-card, .sales-card-item, .new-mobile-card, .secondhand-mobile-card, .card-row, .mob-card-item, .repair-mobile-card, .customer-row-card';
        var itemName = config.itemName || 'entries';
        var pageSize = parseInt(config.pageSize || 10, 10);
        var currentPage = 1;
        var filterPredicate = config.filterPredicate || null;

        var paginationContainer = document.getElementById(paginationContainerId);
        if (!paginationContainer) return null;

        function getAllItems() {
            var rows = [];
            var cards = [];
            if (tableId) {
                var sel = '#' + tableId + ' ' + rowSelector;
                rows = Array.prototype.slice.call(document.querySelectorAll(sel));
            }
            if (cardsContainerId) {
                var cSel = '#' + cardsContainerId + ' ' + cardSelector;
                cards = Array.prototype.slice.call(document.querySelectorAll(cSel));
            }
            return { rows: rows, cards: cards };
        }

        function getVisibleItems() {
            var items = getAllItems();
            var rows = items.rows;
            var cards = items.cards;

            if (typeof filterPredicate === 'function') {
                var maxLen = Math.max(rows.length, cards.length);
                var matchedIndices = [];
                for (var i = 0; i < maxLen; i++) {
                    var r = rows[i] || null;
                    var c = cards[i] || null;
                    if (filterPredicate(r, c, i)) {
                        matchedIndices.push(i);
                    }
                }
                return { 
                    rows: rows, 
                    cards: cards, 
                    matchedRowIndices: matchedIndices.filter(function(idx) { return idx < rows.length; }), 
                    matchedCardIndices: matchedIndices.filter(function(idx) { return idx < cards.length; }), 
                    total: matchedIndices.length 
                };
            }

            var matchedRowIndices = [];
            for (var rIdx = 0; rIdx < rows.length; rIdx++) {
                var r = rows[rIdx];
                var rHidden = r && (r.dataset.mobiHidden === '1' || r.dataset.filterHidden === '1');
                if (!rHidden) {
                    matchedRowIndices.push(rIdx);
                }
            }

            var matchedCardIndices = [];
            for (var cIdx = 0; cIdx < cards.length; cIdx++) {
                var c = cards[cIdx];
                var cHidden = c && (c.dataset.mobiHidden === '1' || c.dataset.filterHidden === '1');
                if (!cHidden) {
                    matchedCardIndices.push(cIdx);
                }
            }

            var total = Math.max(matchedRowIndices.length, matchedCardIndices.length);
            return { 
                rows: rows, 
                cards: cards, 
                matchedRowIndices: matchedRowIndices, 
                matchedCardIndices: matchedCardIndices, 
                total: total 
            };
        }

        function render() {
            var items = getVisibleItems();
            var rows = items.rows;
            var cards = items.cards;
            var matchedRowIndices = items.matchedRowIndices;
            var matchedCardIndices = items.matchedCardIndices;
            var total = items.total;
            var totalPages = Math.max(1, Math.ceil(total / pageSize));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            var startIndex = total === 0 ? 0 : (currentPage - 1) * pageSize;
            var endIndex = Math.min(startIndex + pageSize, total);

            var activeRowIndices = {};
            var rSlice = matchedRowIndices.slice(startIndex, endIndex);
            for (var k = 0; k < rSlice.length; k++) {
                activeRowIndices[rSlice[k]] = true;
            }

            var activeCardIndices = {};
            var cSlice = matchedCardIndices.slice(startIndex, endIndex);
            for (var m = 0; m < cSlice.length; m++) {
                activeCardIndices[cSlice[m]] = true;
            }

            rows.forEach(function (r, idx) {
                if (matchedRowIndices.indexOf(idx) === -1) {
                    r.style.display = 'none';
                } else if (activeRowIndices[idx]) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });

            cards.forEach(function (c, idx) {
                if (matchedCardIndices.indexOf(idx) === -1) {
                    c.style.display = 'none';
                } else if (activeCardIndices[idx]) {
                    c.style.display = '';
                } else {
                    c.style.display = 'none';
                }
            });

            var showingFrom = total === 0 ? 0 : startIndex + 1;
            var showingTo = endIndex;

            var html = '<div class="mobi-pagination-wrap">' +
                '<div class="mobi-pagination-left">' +
                '<span class="mobi-page-info">Showing <strong>' + showingFrom + '</strong> to <strong>' + showingTo + '</strong> of <strong>' + total + '</strong> ' + itemName + '</span>' +
                '<div class="mobi-page-size-picker">' +
                '<label for="' + paginationContainerId + '_ps">Per page:</label>' +
                '<select id="' + paginationContainerId + '_ps" class="mobi-page-size-select">' +
                '<option value="10" ' + (pageSize === 10 ? 'selected' : '') + '>10</option>' +
                '<option value="25" ' + (pageSize === 25 ? 'selected' : '') + '>25</option>' +
                '<option value="50" ' + (pageSize === 50 ? 'selected' : '') + '>50</option>' +
                '<option value="100" ' + (pageSize === 100 ? 'selected' : '') + '>100</option>' +
                '</select>' +
                '</div>' +
                '</div>' +
                '<div class="mobi-pagination-right">' +
                '<div class="mobi-pagination-nav">' +
                '<button type="button" class="mobi-page-nav-btn prev-btn" ' + (currentPage <= 1 ? 'disabled' : '') + ' title="Previous Page">' +
                '<svg style="width:13px;height:13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg> Prev' +
                '</button>' +
                '<div class="mobi-page-numbers">';

            var pageNumbers = getPageNumbers(currentPage, totalPages);
            for (var p = 0; p < pageNumbers.length; p++) {
                var pg = pageNumbers[p];
                if (pg === '...') {
                    html += '<span class="mobi-page-ellipsis">…</span>';
                } else {
                    html += '<button type="button" class="mobi-page-num-btn ' + (pg === currentPage ? 'active' : '') + '" data-page="' + pg + '">' + pg + '</button>';
                }
            }

            html += '</div>' +
                '<button type="button" class="mobi-page-nav-btn next-btn" ' + (currentPage >= totalPages ? 'disabled' : '') + ' title="Next Page">' +
                'Next <svg style="width:13px;height:13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>' +
                '</button>' +
                '</div>' +
                '</div>' +
                '</div>';

            paginationContainer.innerHTML = html;

            var selectEl = document.getElementById(paginationContainerId + '_ps');
            if (selectEl) {
                selectEl.addEventListener('change', function (e) {
                    pageSize = parseInt(e.target.value, 10);
                    currentPage = 1;
                    render();
                });
            }

            var prevBtn = paginationContainer.querySelector('.prev-btn');
            if (prevBtn && !prevBtn.disabled) {
                prevBtn.addEventListener('click', function () {
                    if (currentPage > 1) {
                        currentPage--;
                        render();
                    }
                });
            }

            var nextBtn = paginationContainer.querySelector('.next-btn');
            if (nextBtn && !nextBtn.disabled) {
                nextBtn.addEventListener('click', function () {
                    if (currentPage < totalPages) {
                        currentPage++;
                        render();
                    }
                });
            }

            var btns = paginationContainer.querySelectorAll('.mobi-page-num-btn');
            for (var b = 0; b < btns.length; b++) {
                (function (btn) {
                    btn.addEventListener('click', function () {
                        var targetPage = parseInt(btn.dataset.page, 10);
                        if (targetPage && targetPage !== currentPage) {
                            currentPage = targetPage;
                            render();
                        }
                    });
                })(btns[b]);
            }
        }

        function getPageNumbers(current, total) {
            if (total <= 7) {
                var arr = [];
                for (var i = 0; i < total; i++) arr.push(i + 1);
                return arr;
            }
            var pages = [1];
            if (current > 3) pages.push('...');
            var start = Math.max(2, current - 1);
            var end = Math.min(total - 1, current + 1);
            for (var j = start; j <= end; j++) pages.push(j);
            if (current < total - 2) pages.push('...');
            pages.push(total);
            return pages;
        }

        render();

        return {
            refresh: function (resetToPage1) {
                if (resetToPage1) currentPage = 1;
                render();
            },
            setPage: function (p) {
                currentPage = p;
                render();
            },
            setFilterPredicate: function (fn) {
                filterPredicate = fn;
                currentPage = 1;
                render();
            },
            getPage: function () { return currentPage; },
            getPageSize: function () { return pageSize; }
        };
    };

    /* ─── User dropdown toggle ─── */
    var userBtn = document.getElementById('user-badge-btn');
    var userDD = document.getElementById('user-dropdown');
    if (userBtn && userDD) {
        userBtn.addEventListener('click', function (e) {
            if (e.target.closest('a')) return;
            e.stopPropagation();
            userDD.classList.toggle('open');
            userBtn.classList.toggle('open');
        });
        userDD.addEventListener('click', function (e) {
            e.stopPropagation();
        });
        document.addEventListener('click', function () {
            userDD.classList.remove('open');
            userBtn.classList.remove('open');
        });
    }

    /* ─── Mobile Sidebar Drawer (Hamburger) ─── */
    window.openMobileSidebar = function () {
        var overlay = document.getElementById('mobileSidebarOverlay');
        var drawer = document.getElementById('mobileSidebarDrawer');
        if (overlay) overlay.classList.add('open');
        if (drawer) {
            drawer.classList.add('open');
            // Prevent body scroll while drawer is open
            document.body.style.overflow = 'hidden';
        }
        if (window.refreshIcons) window.refreshIcons();
    };

    window.closeMobileSidebar = function () {
        var overlay = document.getElementById('mobileSidebarOverlay');
        var drawer = document.getElementById('mobileSidebarDrawer');
        if (overlay) overlay.classList.remove('open');
        if (drawer) drawer.classList.remove('open');
        document.body.style.overflow = '';
    };

    // Close drawer on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeMobileSidebar && window.closeMobileSidebar();
        }
    });

    /* ─── Universal Image File Upload Preview ─── */
    window.previewSelectedPhoto = window.previewSelectedPhoto || function (input, imgId, boxId) {
        if (input && input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById(imgId);
                var box = document.getElementById(boxId);
                if (img) img.src = e.target.result;
                if (box) box.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    /* ─── Flash message auto-dismiss ─── */
    setTimeout(function () {
        var el = document.getElementById('flash-msg');
        if (el && el.classList.contains('flash-success')) {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 300);
        }
    }, 5000);

    setTimeout(function () {
        var el = document.getElementById('flash-msg');
        if (el && el.classList.contains('flash-error')) {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 300);
        }
    }, 8000);

    /* ─── Mobile Bottom Nav & FAB Auto-Hide On Scroll (iPhone/Safari pattern) ─── */
    (function () {
        var getScrollTop = function () {
            return window.pageYOffset || (document.documentElement ? document.documentElement.scrollTop : 0) || (document.body ? document.body.scrollTop : 0) || 0;
        };
        var lastScrollY = getScrollTop();
        var ticking = false;
        var threshold = 8;

        function updateMobileNav() {
            var currentScrollY = getScrollTop();
            var diff = currentScrollY - lastScrollY;
            var nav = document.querySelector ? document.querySelector('.mobile-bottom-nav') : null;
            var fab = document.querySelector ? document.querySelector('.mobile-fab-container') : null;

            // Do not hide if any modal is currently visible
            var hasOpenModal = document.querySelector(
                '.mobi-modal-backdrop[style*="display: block"], ' +
                '.mobi-modal-backdrop[style*="display: flex"], ' +
                '[id$="Modal"][style*="display: block"], ' +
                '[id$="Modal"][style*="display: flex"], ' +
                '[id*="Modal"][style*="display: block"], ' +
                '[id*="Modal"][style*="display: flex"], ' +
                '[id*="Drawer"][style*="display: flex"], ' +
                '[id*="Drawer"][style*="display: block"]'
            );
            // Also check if mobile sidebar is open
            var sidebarDrawer = document.getElementById('mobileSidebarDrawer');
            if (sidebarDrawer && sidebarDrawer.classList.contains('open')) {
                hasOpenModal = true;
            }

            if (!hasOpenModal) {
                // Keep bottom navigation bar permanently fixed (never hide)
                if (nav) nav.classList.remove('nav-hidden');

                if (currentScrollY <= 40) {
                    if (fab) fab.classList.remove('fab-hidden');
                } else if (diff > threshold) {
                    // Scrolling DOWN: Hide FAB only
                    if (fab) fab.classList.add('fab-hidden');
                } else if (diff < -threshold) {
                    // Scrolling UP: Reveal FAB immediately
                    if (fab) fab.classList.remove('fab-hidden');
                }

                // If scrolled to the very bottom of the document, reveal FAB
                var windowHeight = window.innerHeight || document.documentElement.clientHeight;
                var totalDocHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
                if (windowHeight + currentScrollY >= totalDocHeight - 20) {
                    if (fab) fab.classList.remove('fab-hidden');
                }
            }

            lastScrollY = Math.max(0, currentScrollY);
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(updateMobileNav);
                ticking = true;
            }
        }, { passive: true });
    })();

    /* ─── Universal Store Owner OTP Authorization Modal ─── */
    window.promptOwnerOtp = function (options) {
        options = options || {};
        var action = options.action || 'destructive_action';
        var itemRef = options.item_reference || 'general';
        var onSuccess = options.onSuccess || function () {};
        var onCancel = options.onCancel || function () {};

        var modalId = 'mobiUniversalOtpModal';
        var modal = document.getElementById(modalId);
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.style.cssText = 'display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;';
            modal.innerHTML = '<div style="background:#fff; border-radius:12px; max-width:440px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); overflow:hidden;">' +
                '<div style="background:#DC2626; color:#fff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">' +
                    '<div style="display:flex; align-items:center; gap:8px;">' +
                        '<i data-lucide="shield-alert" style="width:20px;height:20px;"></i>' +
                        '<div style="font-weight:800; font-size:15px;">Store Owner Authorization</div>' +
                    '</div>' +
                    '<button type="button" id="mobiOtpCloseBtn" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">&times;</button>' +
                '</div>' +
                '<div style="padding:20px;">' +
                    '<div style="font-size:13px; color:#475569; margin-bottom:14px; line-height:1.5;">' +
                        'This action requires <strong>Store Owner OTP Authorization</strong>. Click below to send a 6-digit code to the store owner\'s registered email.' +
                    '</div>' +
                    '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">' +
                        '<button type="button" id="mobiOtpSendBtn" class="btn btn-outline btn-sm" style="font-weight:700; color:#2563EB; border-color:#93C5FD;">' +
                            'Send OTP to Owner' +
                        '</button>' +
                        '<span id="mobiOtpStatusMsg" style="font-size:11px; color:#64748B;"></span>' +
                    '</div>' +
                    '<div style="margin-bottom:16px;">' +
                        '<label style="display:block; font-size:12px; font-weight:700; color:#0F172A; margin-bottom:6px;">Enter 6-Digit OTP</label>' +
                        '<input type="text" id="mobiOtpInput" maxlength="6" placeholder="------" style="width:100%; text-align:center; letter-spacing:8px; font-size:22px; font-weight:900; padding:10px; border:2px solid #CBD5E1; border-radius:8px;">' +
                    '</div>' +
                    '<div id="mobiOtpError" style="display:none; color:#DC2626; font-size:12px; margin-bottom:12px;"></div>' +
                    '<div style="display:flex; justify-content:flex-end; gap:10px;">' +
                        '<button type="button" id="mobiOtpCancelBtn" class="btn btn-outline">Cancel</button>' +
                        '<button type="button" id="mobiOtpConfirmBtn" class="btn btn-primary" style="background:#DC2626; border-color:#DC2626; font-weight:700;">Authorize & Proceed</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
            document.body.appendChild(modal);
            if (window.refreshIcons) window.refreshIcons();
        }

        var input = document.getElementById('mobiOtpInput');
        var errBox = document.getElementById('mobiOtpError');
        var statusMsg = document.getElementById('mobiOtpStatusMsg');
        var sendBtn = document.getElementById('mobiOtpSendBtn');
        var confirmBtn = document.getElementById('mobiOtpConfirmBtn');
        var cancelBtn = document.getElementById('mobiOtpCancelBtn');
        var closeBtn = document.getElementById('mobiOtpCloseBtn');

        input.value = '';
        errBox.style.display = 'none';
        errBox.textContent = '';
        statusMsg.textContent = '';
        modal.style.display = 'flex';
        input.focus();

        function closeModal() {
            modal.style.display = 'none';
        }

        closeBtn.onclick = function () { closeModal(); onCancel(); };
        cancelBtn.onclick = function () { closeModal(); onCancel(); };

        sendBtn.onclick = function () {
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
            statusMsg.textContent = 'Sending...';

            var csrfToken = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var url = (window.mobiShopRoutes && window.mobiShopRoutes.otpRequest) || '/apps/mobile-shop/otp/request';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action, item_reference: itemRef })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Resend OTP';
                if (data.is_owner) {
                    closeModal();
                    onSuccess(null);
                } else if (data.success) {
                    statusMsg.innerHTML = '<span style="color:#16A34A;font-weight:700;">✓ Sent to ' + (data.target_email || 'owner') + '</span>';
                    input.focus();
                } else {
                    errBox.style.display = 'block';
                    errBox.textContent = data.message || 'Failed to send OTP.';
                }
            })
            .catch(function () {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Retry Send';
                errBox.style.display = 'block';
                errBox.textContent = 'Network error requesting OTP.';
            });
        };

        confirmBtn.onclick = function () {
            var code = (input.value || '').trim();
            if (code.length < 6) {
                errBox.style.display = 'block';
                errBox.textContent = 'Please enter a valid 6-digit OTP code.';
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Verifying...';

            var csrfToken = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var url = (window.mobiShopRoutes && window.mobiShopRoutes.otpVerify) || '/apps/mobile-shop/otp/verify';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action, item_reference: itemRef, otp_code: code })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Authorize & Proceed';
                if (data.success) {
                    closeModal();
                    onSuccess(code);
                } else {
                    errBox.style.display = 'block';
                    errBox.textContent = data.message || 'Invalid or expired OTP.';
                }
            })
            .catch(function () {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Authorize & Proceed';
                errBox.style.display = 'block';
                errBox.textContent = 'Network error verifying OTP.';
            });
        };
    };
})();
