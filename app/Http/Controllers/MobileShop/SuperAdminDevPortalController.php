<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class SuperAdminDevPortalController extends Controller
{
    /**
     * Check if a given user is specifically Altmash (Master Developer).
     * Strictly disallows Store Admin, Store Owner, or any other staff.
     */
    protected function isMasterDeveloper($user): bool
    {
        if (!$user) {
            return false;
        }

        $email = strtolower(trim((string) ($user->email ?? '')));
        $name  = strtolower(trim((string) ($user->name ?? '')));

        return ($user->id === 16)
            || ($email === 'altmash@mobitrack.local')
            || (str_starts_with($email, 'altmash@'))
            || ($name === 'altmash');
    }

    /**
     * Enforce exclusive Altmash authentication & dedicated terminal lock.
     * Store Admin and all other store staff are strictly forbidden.
     */
    protected function authorizeDevAdmin()
    {
        // 1. Must be logged in
        if (!auth()->check()) {
            return response()->view('mobileshop.dev_admin_login');
        }

        $currentUser = auth()->user();

        // 2. Must specifically be Altmash! NOT store-admin or any other role!
        if (!$this->isMasterDeveloper($currentUser)) {
            return response()->view('mobileshop.dev_admin_login', [
                'error' => 'Access Denied: The Developer Control Center is strictly restricted to Master Administrator (altmash). Store Admin and all other store accounts are not permitted in this area.'
            ]);
        }

        // 3. Must have verified the developer password for this terminal session
        if (!session('dev_console_unlocked')) {
            return response()->view('mobileshop.dev_admin_login', [
                'info' => 'Terminal Locked: Re-enter your Developer Password to access the control center.'
            ]);
        }

        return null;
    }

    /**
     * Process direct login / unlock of Developer Control Center
     */
    public function login(Request $request)
    {
        $loginInput = trim((string) $request->input('id', $request->input('email', 'altmash')));
        $password   = (string) $request->input('password', '');

        // Resolve user record
        $matchedUser = \App\Models\Auth\User::where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->orWhere(function ($q) use ($loginInput) {
                if (!str_contains($loginInput, '@')) {
                    $q->where('email', $loginInput . '@mobitrack.local');
                }
            })
            ->first();

        // Strictly verify that this user is specifically Altmash
        if (!$matchedUser || !$this->isMasterDeveloper($matchedUser)) {
            return response()->view('mobileshop.dev_admin_login', [
                'error' => 'Access Denied: This Developer Console is restricted exclusively to altmash. Store Admin and staff accounts cannot access this area.'
            ]);
        }

        // Verify password using Hash check
        if (!\Illuminate\Support\Facades\Hash::check($password, $matchedUser->password)) {
            return response()->view('mobileshop.dev_admin_login', [
                'error' => 'Invalid Developer Password. Access Denied.'
            ]);
        }

        // Log user in as Altmash (if not already)
        auth()->login($matchedUser, true);

        // Grant active terminal unlock lease in session
        session([
            'dev_console_unlocked'    => true,
            'dev_console_unlocked_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('dev.portal')->with('success', "Terminal Unlocked: Welcome Master Developer ({$matchedUser->name})!");
    }

    /**
     * Re-lock the Developer Console immediately
     */
    public function lock(Request $request)
    {
        session()->forget('dev_console_unlocked');
        session()->forget('dev_console_unlocked_at');

        return redirect()->route('dev.portal')->with('warning', 'Developer Console locked successfully. Re-authentication required.');
    }

    /**
     * Show Super Admin & Developer Control Dashboard
     */
    public function index()
    {
        if ($redirect = $this->authorizeDevAdmin()) {
            return $redirect;
        }

        $telemetry = DatabaseBackupService::getTelemetry();
        $backups   = DatabaseBackupService::listBackups();
        $settings  = DatabaseBackupService::getBackupSettings();

        // Resolve company dashboard link for quick switch back
        $companyId = session('company_id')
            ?? (auth()->check() ? (auth()->user()->company_id ?? auth()->user()->companies()->first()?->id) : 1)
            ?? 1;

        return view('mobileshop.dev_admin_portal', compact('telemetry', 'backups', 'settings', 'companyId'));
    }

    /**
     * Create an on-demand SQL dump backup
     */
    public function createBackup(Request $request)
    {
        if ($redirect = $this->authorizeDevAdmin()) {
            return $redirect;
        }

        $tag = preg_replace('/[^a-zA-Z0-9_\-]/', '', $request->input('tag', 'manual'));
        if (empty($tag)) {
            $tag = 'manual';
        }

        $result = DatabaseBackupService::createBackup($tag);

        if ($result['success']) {
            $settings = DatabaseBackupService::getBackupSettings();
            $settings['last_backup_at'] = now()->toDateTimeString();
            $settings['last_backup_file'] = $result['filename'];
            $settings['last_backup_status'] = 'success';
            DatabaseBackupService::saveBackupSettings($settings);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Backup {$result['filename']} generated successfully ({$result['size_formatted']})",
                    'backup'  => $result,
                ]);
            }

            return redirect()->back()->with('success', "Database backup created successfully ({$result['filename']})!");
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Backup failed',
            ], 500);
        }

        return redirect()->back()->with('error', "Backup failed: " . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Download backup SQL file
     */
    public function downloadBackup($filename)
    {
        if ($redirect = $this->authorizeDevAdmin()) {
            return $redirect;
        }

        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/i', $filename)) {
            abort(400, 'Invalid backup filename.');
        }

        $filepath = DatabaseBackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filepath)) {
            abort(404, 'Backup file does not exist on disk.');
        }

        return response()->download($filepath, $filename, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delete an existing backup file
     */
    public function deleteBackup(Request $request, $filename)
    {
        if ($redirect = $this->authorizeDevAdmin()) {
            return $redirect;
        }

        $deleted = DatabaseBackupService::deleteBackup($filename);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? "Backup '{$filename}' deleted." : "Backup file not found.",
            ]);
        }

        return redirect()->back()->with($deleted ? 'success' : 'error', $deleted ? "Backup '{$filename}' deleted." : "File not found.");
    }

    /**
     * Save automated backup settings
     */
    public function saveSettings(Request $request)
    {
        if ($redirect = $this->authorizeDevAdmin()) {
            return $redirect;
        }

        $settings = DatabaseBackupService::getBackupSettings();
        $settings['enabled']        = $request->boolean('enabled');
        $settings['daily_time']     = $request->input('daily_time', '02:00');
        $settings['retention_days'] = max(1, min(365, intval($request->input('retention_days', 14))));

        DatabaseBackupService::saveBackupSettings($settings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Automated daily backup preferences saved successfully.',
                'settings' => $settings,
            ]);
        }

        return redirect()->back()->with('success', 'Automated daily backup preferences saved successfully.');
    }

    /**
     * Dangerous Action: Factory Reset database for fresh client handover
     */
    public function resetDatabase(Request $request)
    {
        if ($redirect = $this->authorizeDevAdmin()) {
            return $redirect;
        }

        $confirm = trim($request->input('confirm_text', ''));
        if ($confirm !== 'RESET') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Confirmation phrase mismatch. You must type 'RESET' in all capital letters.",
                ], 422);
            }
            return redirect()->back()->with('error', "Reset cancelled: Confirmation phrase did not match 'RESET'.");
        }

        $options = [
            'clean_media'     => $request->boolean('clean_media', true),
            'reset_sequences' => $request->boolean('reset_sequences', true),
            'preserve_staff'  => $request->boolean('preserve_staff', true),
        ];

        $result = DatabaseBackupService::resetDatabaseForClientHandover($options);

        if ($result['success']) {
            $msg = "Database successfully reset for fresh client delivery! Total records wiped: " . ($result['total_records'] ?? 0) . ". Pre-reset snapshot saved: " . ($result['snapshot'] ?? 'N/A');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'result'  => $result,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Reset operation failed.',
            ], 500);
        }

        return redirect()->back()->with('error', "Database reset failed: " . ($result['error'] ?? 'Unknown error'));
    }
}
