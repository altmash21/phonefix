@extends('mobileshop.public.layout')

@section('title', 'Page Not Found — Maurya Mobile')
@section('meta_description', 'The page you requested could not be found in our store catalog.')

@section('subnav_title', 'Page Not Found')
@section('subnav_cta')
    <a href="{{ route('public.landing') }}" class="apple-btn-primary text-[13px] py-1.5 px-4">
        Home
    </a>
@endsection

@section('content')
<div class="bg-apple-canvas text-apple-ink py-24 sm:py-36 text-center">
    <div class="max-w-[768px] mx-auto px-4 space-y-5">
        <span class="apple-caption-strong text-apple-muted-48 uppercase tracking-widest text-[12px]">
            404 Error
        </span>
        <h1 class="apple-hero-display text-apple-ink">
            The page you’re looking for can’t be found.
        </h1>
        <p class="apple-lead text-apple-muted-48 max-w-lg mx-auto">
            {{ !empty($message) ? $message : 'Check the web address and try again, or browse our smartphone showroom.' }}
        </p>
        
        <div class="pt-6 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('public.landing') }}" class="apple-btn-primary">
                Return to Storefront
            </a>
            <a href="{{ route('public.store') }}" class="apple-btn-secondary-pill">
                Explore Smartphones
            </a>
        </div>
    </div>
</div>
@endsection
