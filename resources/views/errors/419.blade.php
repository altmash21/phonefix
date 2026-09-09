@extends('mobileshop.public.layout')

@section('title', 'Page Expired — Maurya Mobile')
@section('meta_description', 'Your session has expired. Please refresh the page and try again.')

@section('subnav_title', 'Page Expired')
@section('subnav_cta')
    <a href="{{ route('login') }}" class="apple-btn-primary text-[13px] py-1.5 px-4">
        Sign In
    </a>
@endsection

@section('content')
<div class="bg-apple-canvas text-apple-ink py-24 sm:py-36 text-center">
    <div class="max-w-[768px] mx-auto px-4 space-y-5">
        <span class="apple-caption-strong text-apple-muted-48 uppercase tracking-widest text-[12px]">
            419 Session Expired
        </span>
        <h1 class="apple-hero-display text-apple-ink">
            Page Expired
        </h1>
        <p class="apple-lead text-apple-muted-48 max-w-lg mx-auto">
            {{ !empty($message) ? $message : 'Your session timed out due to inactivity. Please refresh and try again.' }}
        </p>
        
        <div class="pt-6 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('login') }}" class="apple-btn-primary">
                Sign In to Staff Terminal
            </a>
            <a href="{{ route('public.landing') }}" class="apple-btn-secondary-pill">
                Return to Storefront
            </a>
        </div>
    </div>
</div>
@endsection
