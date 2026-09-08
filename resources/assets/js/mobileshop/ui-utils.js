/**
 * MobiTrack Universal UI Utilities & Table Pagination Engine
 * Shared by all mobile shop views via layout.blade.php
 */
(function () {
    'use strict';

    /* ─── Lucide icon initialisation ─── */
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    });

    /* ─── Date helpers ─── */
    window.formatDate = function (d) {
        if (!d) return '';
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    };

    window.getDateRangePreset = function (preset) {
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

        if (preset === 'today') {
            from = to = todayStr;
        } else if (preset === 'yesterday') {
            var yestDate = new Date(y, m, d - 1);
            from = to = formatDateStr(yestDate.getFullYear(), yestDate.getMonth(), yestDate.getDate());
        } else if (preset === 'week' || preset === '7days' || preset === '7_days' || preset === '7-days') {
            var weekAgo = new Date(y, m, d - 6);
            from = formatDateStr(weekAgo.getFullYear(), weekAgo.getMonth(), weekAgo.getDate());
            to = todayStr;
        } else if (preset === 'month' || preset === 'this_month' || preset === 'this-month') {
            from = formatDateStr(y, m, 1);
            to = todayStr;
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
                return { rows: rows, cards: cards, matchedIndices: matchedIndices, total: matchedIndices.length };
            }

            var maxLen = Math.max(rows.length, cards.length);
            var matchedIndices = [];
            for (var j = 0; j < maxLen; j++) {
                var r = rows[j] || null;
                var c = cards[j] || null;
                var rHidden = r && (r.dataset.mobiHidden === '1' || r.dataset.filterHidden === '1');
                var cHidden = c && (c.dataset.mobiHidden === '1' || c.dataset.filterHidden === '1');
                if (!rHidden && !cHidden) {
                    matchedIndices.push(j);
                }
            }
            return { rows: rows, cards: cards, matchedIndices: matchedIndices, total: matchedIndices.length };
        }

        function render() {
            var items = getVisibleItems();
            var rows = items.rows;
            var cards = items.cards;
            var matchedIndices = items.matchedIndices;
            var total = items.total;
            var totalPages = Math.max(1, Math.ceil(total / pageSize));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            var startIndex = total === 0 ? 0 : (currentPage - 1) * pageSize;
            var endIndex = Math.min(startIndex + pageSize, total);
            var activeIndices = {};
            var slice = matchedIndices.slice(startIndex, endIndex);
            for (var k = 0; k < slice.length; k++) {
                activeIndices[slice[k]] = true;
            }

            rows.forEach(function (r, idx) {
                if (matchedIndices.indexOf(idx) === -1) {
                    r.style.display = 'none';
                } else if (activeIndices[idx]) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });

            cards.forEach(function (c, idx) {
                if (matchedIndices.indexOf(idx) === -1) {
                    c.style.display = 'none';
                } else if (activeIndices[idx]) {
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

    /* ─── Flash message auto-dismiss ─── */
    var flashTimerSuccess = setTimeout(function () {
        var el = document.getElementById('flash-msg');
        if (el && el.classList.contains('flash-success')) {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 300);
        }
    }, 5000);

    var flashTimerError = setTimeout(function () {
        var el = document.getElementById('flash-msg');
        if (el && el.classList.contains('flash-error')) {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 300);
        }
    }, 8000);
})();
