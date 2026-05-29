<x-custom-mailer.mail.text.layout :cmm="$cmm">
    {{-- Header --}}
    @slot('header')
        <x-custom-mailer.mail.text.header :cmm="$cmm">
            {{ config('app.name') }}
        </x-custom-mailer.mail.text.header>
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            <x-custom-mailer.mail.text.subcopy :cmm="$cmm">
                {{ $subcopy }}
            </x-custom-mailer.mail.text.subcopy>
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        <x-custom-mailer.mail.text.footer :cmm="$cmm">
            © {{ date('Y') }} {{ $cmm->getName() }}. @lang('All rights reserved.')
        </x-custom-mailer.mail.text.footer>
    @endslot
</x-custom-mailer.mail.text.layout>
