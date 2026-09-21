<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $request);
$kernel->bootstrap();

echo "=== DATABASE USERS ===\n";
foreach (App\Models\Auth\User::with('roles')->get() as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Roles: " . $u->roles->pluck('name')->implode(', ') . "\n";
}

echo "\n=== DATABASE ROLES ===\n";
foreach (App\Models\Auth\Role::all() as $r) {
    echo "ID: {$r->id}, Name: {$r->name}, Display: {$r->display_name}\n";
}
