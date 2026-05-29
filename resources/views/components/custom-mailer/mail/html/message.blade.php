@component('components.custom-mailer.mail.html.layout', ['cmm' => $cmm])
    {{-- Header --}}
    @slot('header')
        @component('components.custom-mailer.mail.html.header', ['cmm' => $cmm])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('components.custom-mailer.mail.html.subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('components.custom-mailer.mail.html.footer', ['cmm' => $cmm])
            © {{ date('Y') }} {{ $cmm->getName() }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
