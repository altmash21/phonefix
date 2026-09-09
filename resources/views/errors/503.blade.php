@extends('mobileshop.public.layout')

@section('title', 'Under Maintenance — Maurya Mobile')
@section('meta_description', 'Storefront maintenance in progress.')

@section('subnav_title', 'Maintenance')

@section('content')
<div class="bg-apple-canvas text-apple-ink py-24 sm:py-36 text-center">
    <div class="max-w-[768px] mx-auto px-4 space-y-5">
        <span class="apple-caption-strong text-apple-muted-48 uppercase tracking-widest text-[12px]">
            503 Maintenance Mode
        </span>
        <h1 class="apple-hero-display text-apple-ink">
            Storefront Maintenance in Progress
        </h1>
        <p class="apple-lead text-apple-muted-48 max-w-lg mx-auto">
            {{ !empty($message) ? $message : 'We are performing scheduled updates to improve our store catalog. Please check back shortly.' }}
        </p>
        
        <div class="pt-6 flex items-center justify-center gap-4 flex-wrap">
            <button onclick="window.location.reload()" class="apple-btn-primary">
                Refresh Page
            </button>
            <a href="{{ route('public.contact') }}" class="apple-btn-secondary-pill">
                Contact Store Desk
            </a>
        </div>
    </div>
</div>
@endsection
