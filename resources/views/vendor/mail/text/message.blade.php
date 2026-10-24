@php($instituteSettings = app(App\Services\InstituteSettings::class))
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ $instituteSettings->get('name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            {{ $instituteSettings->get('name') }}
            {{ $instituteSettings->get('address') }}
            {{ $instituteSettings->get('phone') }} · {{ $instituteSettings->get('email') }}

            © {{ date('Y') }} {{ $instituteSettings->get('name') }}. @lang('All rights reserved.')
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
