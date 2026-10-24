@php($instituteSettings = app(App\Services\InstituteSettings::class))
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')" />
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
**{{ $instituteSettings->get('name') }}**<br>
{{ $instituteSettings->get('address') }}<br>
{{ $instituteSettings->get('phone') }} · {{ $instituteSettings->get('email') }}

© {{ date('Y') }} {{ $instituteSettings->get('name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
