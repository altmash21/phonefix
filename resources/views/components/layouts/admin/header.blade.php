@stack('header_start')

<div id="header" class="xl:pt-4 -mt-1">
    <div class="flex flex-col sm:flex-row items-start justify-between sm:space-x-3 sm:rtl:space-x-reverse hide-empty-page">
        <div data-page-title-first class="w-full sm:w-6/12 items-center mb-1 sm:mb-0">
            <div class="flex items-center space-y-2">
                <h1 class="flex items-center text-xl sm:text-2xl xl:text-3xl text-black font-light ltr:-ml-0.5 rtl:-mr-0.5 mt-1 whitespace-nowrap">
                    <x-title>
                        {!! $title !!}
                    </x-title>

                    @yield('dashboard_action')
                </h1>

                @if (! empty($status))
                <div class="ltr:ml-4 rtl:mr-4 -mt-4">
                    {!! $status !!}
                </div>
                @endif

                {!! $info ?? '' !!}

                {!! $favorite ?? '' !!}
            </div>
        </div>

        <div data-page-title-second class="w-full flex flex-wrap flex-col sm:flex-row sm:items-center justify-end sm:space-x-2 sm:rtl:space-x-reverse suggestion-buttons">
            @stack('header_button_start')

            {!! $buttons !!}

            @stack('header_button_end')

            @stack('header_suggestion_start')

            <x-suggestions />

            @stack('header_suggestion_end')

            {!! $moreButtons !!}
        </div>
    </div>
</div>

@stack('header_end')
