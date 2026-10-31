{{-- Header "Employee Login" button linking to the public employee lookup page. --}}
@props(['mobile' => false])
@if ($mobile)
    <div {{ $attributes->merge(['class' => 'verify-mobile']) }}>
        <a href="{{ route('employees.verify') }}" class="btn-oxf btn-gold" data-verify-employee><i class="bi bi-person-lock" aria-hidden="true"></i> Employee Login</a>
    </div>
@else
    <a href="{{ route('employees.verify') }}" {{ $attributes->merge(['class' => 'btn-oxf btn-navy']) }} data-verify-employee><i class="bi bi-person-lock" aria-hidden="true"></i> Employee Login</a>
@endif
