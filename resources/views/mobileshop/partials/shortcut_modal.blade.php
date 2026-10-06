{{-- PhoneFix Admin Shortcut & App Download Modal (Desktop & Mobile) --}}
<div id="adminShortcutModal" class="admin-shortcut-modal-overlay" style="display:none;" onclick="if(event.target===this) closeAdminShortcutModal()">
    <div class="admin-shortcut-modal-card" role="dialog" aria-modal="true" aria-labelledby="shortcutModalTitle">
        
        <!-- Header -->
        <div class="admin-shortcut-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div class="admin-shortcut-app-icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div>
                    <h3 id="shortcutModalTitle" style="margin:0; font-size:16px; font-weight:700; color:#0F172A; letter-spacing:-0.3px;">
                        PhoneFix Admin Shortcut
                    </h3>
                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">
                        1-Click access from your Desktop & Mobile Home Screen
                    </div>
                </div>
            </div>
            <button type="button" class="admin-shortcut-close-btn" onclick="closeAdminShortcutModal()" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- Detected Device Bar -->
        <div class="admin-shortcut-device-bar">
            <div style="display:flex; align-items:center; gap:6px;">
                <span class="admin-shortcut-dot-pulse"></span>
                <span id="shortcutDetectedOsText" style="font-size:11.5px; font-weight:600; color:#4338CA;">Detecting device...</span>
            </div>
            <span style="font-size:10.5px; color:#64748B; background:#FFFFFF; padding:2px 8px; border-radius:999px; border:1px solid #E2E8F0; font-weight:600;">Fast & Direct</span>
        </div>

        <!-- Options Container -->
        <div class="admin-shortcut-body">
            
            <!-- OPTION 1: Desktop Shortcut File (.url) -->
            <div class="admin-shortcut-card" id="cardDesktopShortcut">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div class="admin-shortcut-item-icon" style="background:#EEF2FF; color:#4F46E5;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                            <div style="font-weight:700; font-size:13.5px; color:#1E293B;">Desktop Shortcut File (.url)</div>
                            <span class="admin-shortcut-tag" style="background:#EDE9FE; color:#6D28D9;">Windows / PC</span>
                        </div>
                        <p style="margin:3px 0 10px; font-size:12px; color:#64748B; line-height:1.4;">
                            Downloads a ready-to-use desktop file. Double-click from your desktop to instantly open the Admin Console.
                        </p>
                        <button type="button" class="admin-shortcut-action-btn primary" onclick="downloadDesktopShortcutFile()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            <span>Download Desktop Shortcut</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- OPTION 2: PWA Web App Install (Chrome/Edge on Desktop & Android) -->
            <div class="admin-shortcut-card" id="cardInstallPwa">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div class="admin-shortcut-item-icon" style="background:#ECFDF5; color:#059669;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                            <div style="font-weight:700; font-size:13.5px; color:#1E293B;">Install Web App (No URL Bar)</div>
                            <span class="admin-shortcut-tag" style="background:#D1FAE5; color:#065F46;">Chrome / Edge</span>
                        </div>
                        <p style="margin:3px 0 10px; font-size:12px; color:#64748B; line-height:1.4;">
                            Runs PhoneFix as a standalone native app on your phone home screen or PC taskbar without browser borders.
                        </p>
                        <button type="button" id="btnPwaInstallAction" class="admin-shortcut-action-btn secondary" onclick="triggerPwaInstall()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v13"></path><path d="m16 11-4 4-4-4"></path><rect x="3" y="17" width="18" height="5" rx="1"></rect></svg>
                            <span id="btnPwaInstallText">Add to Home Screen / Install</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- OPTION 3: Mobile Home Screen Guidance (Android & iOS) -->
            <div class="admin-shortcut-card" id="cardMobileInstructions">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div class="admin-shortcut-item-icon" style="background:#FFFBEB; color:#D97706;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:700; font-size:13.5px; color:#1E293B;">Add Shortcut on Phone Browser</div>
                        
                        <!-- Android Steps -->
                        <div id="androidSteps" style="margin-top:8px; font-size:12px; color:#475569; line-height:1.5;">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                                <span style="display:inline-flex; width:18px; height:18px; border-radius:50%; background:#E2E8F0; align-items:center; justify-content:center; font-size:10px; font-weight:700;">1</span>
                                Tap Chrome menu <strong>⋮ (three dots)</strong> in top right.
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="display:inline-flex; width:18px; height:18px; border-radius:50%; background:#E2E8F0; align-items:center; justify-content:center; font-size:10px; font-weight:700;">2</span>
                                Select <strong>"Install app"</strong> or <strong>"Add to Home screen"</strong>.
                            </div>
                        </div>

                        <!-- iOS Safari Steps -->
                        <div id="iosSteps" style="margin-top:8px; font-size:12px; color:#475569; line-height:1.5; display:none;">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                                <span style="display:inline-flex; width:18px; height:18px; border-radius:50%; background:#E2E8F0; align-items:center; justify-content:center; font-size:10px; font-weight:700;">1</span>
                                Tap the <strong>Share button</strong>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:middle;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                                at the bottom of Safari.
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="display:inline-flex; width:18px; height:18px; border-radius:50%; background:#E2E8F0; align-items:center; justify-content:center; font-size:10px; font-weight:700;">2</span>
                                Scroll down and tap <strong>"Add to Home Screen ⊞"</strong>, then tap <strong>Add</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Link & Copy Box -->
            <div style="margin-top:10px; padding:10px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; display:flex; align-items:center; gap:8px;">
                <input type="text" id="adminShortcutUrlInput" readonly value="{{ url('/' . (session('company_id', 1)) . '/mobileshop') }}" style="flex:1; background:transparent; border:none; font-size:11.5px; font-family:'JetBrains Mono', monospace; color:#334155; outline:none; text-overflow:ellipsis;">
                <button type="button" onclick="copyAdminShortcutLink()" id="btnCopyShortcutLink" style="padding:4px 10px; font-size:11.5px; font-weight:700; color:#4338CA; background:#EEF2FF; border:1px solid #C7D2FE; border-radius:6px; cursor:pointer; white-space:nowrap; transition:all 0.15s ease;">
                    Copy Link
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="admin-shortcut-footer">
            <span style="font-size:11px; color:#94A3B8;">PhoneFix Azamgarh &bull; Standalone Web Application</span>
            <button type="button" class="admin-shortcut-done-btn" onclick="closeAdminShortcutModal()">Done</button>
        </div>

    </div>
</div>

<style>
/* ─── MODAL STYLES ─── */
.admin-shortcut-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: fadeInModal 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.admin-shortcut-modal-card {
    background: #FFFFFF;
    width: 100%;
    max-width: 480px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    animation: scaleInCard 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeInModal {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleInCard {
    from { opacity: 0; transform: scale(0.95) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.admin-shortcut-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
    background: #FFFFFF;
}

.admin-shortcut-app-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.admin-shortcut-close-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #F1F5F9;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.admin-shortcut-close-btn:hover {
    background: #E2E8F0;
    color: #0F172A;
}

.admin-shortcut-device-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 20px;
    background: #F8FAFC;
    border-bottom: 1px solid #F1F5F9;
}

.admin-shortcut-dot-pulse {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    display: inline-block;
}

.admin-shortcut-body {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 70vh;
    overflow-y: auto;
}

.admin-shortcut-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 14px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.admin-shortcut-card:hover {
    border-color: #CBD5E1;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
.admin-shortcut-card.highlighted {
    border-color: #818CF8;
    background: #FAF5FF;
}

.admin-shortcut-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.admin-shortcut-tag {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.admin-shortcut-action-btn {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
    border: none;
    text-decoration: none;
}
.admin-shortcut-action-btn.primary {
    background: #4F46E5;
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
}
.admin-shortcut-action-btn.primary:hover {
    background: #4338CA;
}
.admin-shortcut-action-btn.secondary {
    background: #0F172A;
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
}
.admin-shortcut-action-btn.secondary:hover {
    background: #1E293B;
}

.admin-shortcut-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #F8FAFC;
    border-top: 1px solid #F1F5F9;
}

.admin-shortcut-done-btn {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 700;
    background: #E2E8F0;
    color: #334155;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.admin-shortcut-done-btn:hover {
    background: #CBD5E1;
    color: #0F172A;
}

/* ─── TOPBAR BUTTON STYLES ─── */
.btn-app-shortcut {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 11px;
    background: #F1F5F9;
    color: #1E293B;
    border: 1px solid #CBD5E1;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    white-space: nowrap;
}
.btn-app-shortcut:hover {
    background: #E2E8F0;
    border-color: #94A3B8;
    color: #0F172A;
    transform: translateY(-0.5px);
}
.btn-app-shortcut .shortcut-badge {
    background: #4F46E5;
    color: #FFFFFF;
    font-size: 9.5px;
    font-weight: 800;
    padding: 1px 5px;
    border-radius: 4px;
    letter-spacing: 0.3px;
}

/* Mobile Sidebar Drawer Banner */
.mobile-shortcut-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 8px 12px 14px;
    padding: 10px 12px;
    background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
    border: 1px solid #C7D2FE;
    border-radius: 10px;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.mobile-shortcut-banner:active {
    transform: scale(0.98);
}
</style>

<script>
(function() {
    var deferredPrompt = null;

    // Listen for PWA install event
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        var btn = document.getElementById('btnPwaInstallAction');
        var txt = document.getElementById('btnPwaInstallText');
        if (btn && txt) {
            btn.classList.add('primary');
            txt.textContent = 'Install App Now (1-Click)';
        }
    });

    window.openAdminShortcutModal = function() {
        var modal = document.getElementById('adminShortcutModal');
        if (!modal) return;
        modal.style.display = 'flex';

        // Detect Operating System & Browser
        var ua = navigator.userAgent || '';
        var isAndroid = /android/i.test(ua);
        var isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        var isWindows = /windows/i.test(ua);
        var isMac = /macintosh|mac os x/i.test(ua) && !isIOS;
        var isMobile = isAndroid || isIOS || /mobile/i.test(ua);

        var osLabel = 'Desktop Device';
        if (isWindows) osLabel = 'Windows PC (Desktop)';
        else if (isAndroid) osLabel = 'Android Device';
        else if (isIOS) osLabel = 'Apple iPhone / iPad';
        else if (isMac) osLabel = 'Apple Mac';

        var osEl = document.getElementById('shortcutDetectedOsText');
        if (osEl) osEl.textContent = 'Detected: ' + osLabel;

        // Visual arrangement based on platform
        var cardDesktop = document.getElementById('cardDesktopShortcut');
        var cardPwa = document.getElementById('cardInstallPwa');
        var cardMobile = document.getElementById('cardMobileInstructions');
        var androidSteps = document.getElementById('androidSteps');
        var iosSteps = document.getElementById('iosSteps');

        if (cardDesktop) cardDesktop.classList.remove('highlighted');
        if (cardPwa) cardPwa.classList.remove('highlighted');
        if (cardMobile) cardMobile.classList.remove('highlighted');

        if (isMobile) {
            if (isIOS) {
                if (iosSteps) iosSteps.style.display = 'block';
                if (androidSteps) androidSteps.style.display = 'none';
                if (cardMobile) cardMobile.classList.add('highlighted');
            } else {
                if (iosSteps) iosSteps.style.display = 'none';
                if (androidSteps) androidSteps.style.display = 'block';
                if (cardPwa) cardPwa.classList.add('highlighted');
            }
        } else {
            // Desktop (Windows/Mac)
            if (cardDesktop) cardDesktop.classList.add('highlighted');
            if (androidSteps) androidSteps.style.display = 'block';
            if (iosSteps) iosSteps.style.display = 'none';
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    };

    window.closeAdminShortcutModal = function() {
        var modal = document.getElementById('adminShortcutModal');
        if (modal) modal.style.display = 'none';
    };

    // 1-Click Desktop Shortcut File Generator (.url)
    window.downloadDesktopShortcutFile = function() {
        var companyId = '{{ session("company_id", 1) }}';
        var adminUrl = window.location.origin + '/' + companyId + '/mobileshop';
        var iconUrl = window.location.origin + '/public/img/favicon.ico';

        var fileContent = "[InternetShortcut]\r\n" +
                          "URL=" + adminUrl + "\r\n" +
                          "IconIndex=0\r\n" +
                          "IconFile=" + iconUrl + "\r\n" +
                          "HotKey=0\r\n" +
                          "IDList=\r\n" +
                          "[{000214A0-0000-0000-C000-000000000046}]\r\n" +
                          "Prop3=19,11\r\n";

        var blob = new Blob([fileContent], { type: 'application/x-mswinurl;charset=utf-8' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'PhoneFix-Admin-Console.url';
        document.body.appendChild(a);
        a.click();
        setTimeout(function() {
            document.body.removeChild(a);
            URL.revokeObjectURL(a.href);
        }, 300);

        // Toast feedback
        var btn = event.currentTarget;
        if (btn) {
            var orig = btn.innerHTML;
            btn.innerHTML = '<span>✓ Shortcut Downloaded! Check Downloads</span>';
            btn.style.background = '#10B981';
            setTimeout(function() {
                btn.innerHTML = orig;
                btn.style.background = '';
            }, 3000);
        }
    };

    // PWA Install Trigger
    window.triggerPwaInstall = function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choice) {
                if (choice.outcome === 'accepted') {
                    closeAdminShortcutModal();
                }
                deferredPrompt = null;
            });
        } else {
            // If beforeinstallprompt hasn't fired yet or not supported, alert smooth guide
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            if (isIOS) {
                alert("To add PhoneFix to your iPhone/iPad:\n1. Tap the Share button at bottom of Safari\n2. Tap 'Add to Home Screen'");
            } else {
                alert("To add PhoneFix Admin to your Desktop / Phone:\n1. Tap your browser's menu (top right ⋮ or address bar install icon)\n2. Select 'Install app' or 'Add to Home screen'");
            }
        }
    };

    // Copy Admin Link
    window.copyAdminShortcutLink = function() {
        var input = document.getElementById('adminShortcutUrlInput');
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            var btn = document.getElementById('btnCopyShortcutLink');
            if (btn) {
                btn.textContent = 'Copied! ✓';
                btn.style.background = '#D1FAE5';
                btn.style.color = '#065F46';
                btn.style.borderColor = '#6EE7B7';
                setTimeout(function() {
                    btn.textContent = 'Copy Link';
                    btn.style.background = '';
                    btn.style.color = '';
                    btn.style.borderColor = '';
                }, 2500);
            }
        }).catch(function() {
            document.execCommand('copy');
        });
    };

    // Register Service Worker for PWA (with instant update check)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{{ asset("serviceworker.js") }}?v=4.0.0').then(function(reg) {
                if (reg) {
                    reg.update();
                }
            }).catch(function(e) {
                // Silently ignore if offline or dev
            });
        });
    }
})();
</script>
