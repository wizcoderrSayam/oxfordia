{{-- Header "Verify Employee" button linking to the public employee verification page. --}}
@props(['mobile' => false])
@if ($mobile)
    <div {{ $attributes->merge(['class' => 'verify-mobile']) }}>
        <a href="{{ route('employees.verify') }}" class="btn-oxf btn-gold" data-verify-employee><i class="bi bi-person-check" aria-hidden="true"></i> Verify Employee</a>
    </div>
@else
    <a href="{{ route('employees.verify') }}" {{ $attributes->merge(['class' => 'btn-oxf btn-navy']) }} data-verify-employee><i class="bi bi-person-check" aria-hidden="true"></i> Verify Employee</a>
@endif
