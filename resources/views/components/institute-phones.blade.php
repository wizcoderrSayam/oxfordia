{{-- The institute's phone number(s) from settings as tel: links, separated by $separator. --}}
@props(['separator' => ', '])
@php
    $phones = array_filter([$institute->get('phone'), $institute->get('phone_alt')]);
@endphp
@foreach ($phones as $phone)
    <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $phone) }}">{{ $phone }}</a>@unless ($loop->last){!! $separator !!}@endunless
@endforeach
