@extends('mobileshop.public.layout')

@section('title', 'System Notice — PhoneFix Azamgarh')
@section('meta_description', 'An unexpected condition occurred on the server.')

@section('subnav_title', 'System Notice')
@section('subnav_cta')
    <a href="{{ route('public.landing') }}" class="apple-btn-primary text-[13px] py-1.5 px-4">
        Home
    </a>
@endsection

@section('content')
<div class="bg-apple-canvas text-apple-ink py-24 sm:py-36 text-center">
    <div class="max-w-[768px] mx-auto px-4 space-y-5">
        <span class="apple-caption-strong text-apple-muted-48 uppercase tracking-widest text-[12px]">
            500 Internal Notice
        </span>
        <h1 class="apple-hero-display text-apple-ink">
            Something went wrong.
        </h1>
        <p class="apple-lead text-apple-muted-48 max-w-lg mx-auto">
            {{ !empty($message) && !app()->isProduction() ? $message : 'Our technicians have been notified and are working on resolving this immediately.' }}
        </p>
        
        <div class="pt-6 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('public.landing') }}" class="apple-btn-primary">
                Return to Storefront
            </a>
            <a href="{{ route('public.contact') }}" class="apple-btn-secondary-pill">
                Contact Store Desk
            </a>
        </div>
    </div>
</div>
@endsection
