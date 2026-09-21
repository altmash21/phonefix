@extends('mobileshop.public.layout')

@section('title', 'Access Restricted — PhoneFix Azamgarh')
@section('meta_description', 'Access to this resource is restricted.')

@section('subnav_title', 'Access Restricted')
@section('subnav_cta')
    <a href="{{ route('login') }}" class="apple-btn-primary text-[13px] py-1.5 px-4">
        Staff Login
    </a>
@endsection

@section('content')
<div class="bg-apple-canvas text-apple-ink py-24 sm:py-36 text-center">
    <div class="max-w-[768px] mx-auto px-4 space-y-5">
        <span class="apple-caption-strong text-apple-primary uppercase tracking-widest text-[12px]">
            403 Security Restriction
        </span>
        <h1 class="apple-hero-display text-apple-ink">
            Access to this section requires authorization.
        </h1>
        <p class="apple-lead text-apple-muted-48 max-w-lg mx-auto">
            {{ !empty($message) ? $message : 'This area is restricted to authorized store administration and counter terminal staff.' }}
        </p>
        
        <div class="pt-6 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('login') }}" class="apple-btn-primary">
                Sign In to Staff Terminal
            </a>
            <a href="{{ route('public.landing') }}" class="apple-btn-secondary-pill">
                Back to Storefront
            </a>
        </div>
    </div>
</div>
@endsection
