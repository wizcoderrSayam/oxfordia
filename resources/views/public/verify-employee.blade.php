<x-layouts.public title="Verify Employee" description="Verify that a person is an employee of the institute using their registration number.">
    <section class="page-hero">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/px-team.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Verify Employee</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">Employee verification</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">Verify an <em>employee</em></h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Enter the registration number from an employee's ID card and their date of birth to confirm they work with {{ $institute->get('name') }}.</p>
        </div>
    </section>

    @if ($searched && $employee)
        @php
            $seal = match ($employee->status) {
                App\Enums\EmployeeStatus::Active => ['class' => 'verified', 'icon' => 'bi-patch-check-fill', 'text' => 'Verified employee'],
                App\Enums\EmployeeStatus::Inactive => ['class' => 'inactive', 'icon' => 'bi-pause-circle-fill', 'text' => 'Inactive'],
                App\Enums\EmployeeStatus::Relieved => ['class' => 'relieved', 'icon' => 'bi-x-octagon-fill', 'text' => 'No longer employed'],
            };
            $details = [
                ['icon' => 'bi-person-vcard', 'label' => 'Registration No', 'value' => $employee->registration_number],
                ['icon' => 'bi-person', 'label' => 'Name', 'value' => $employee->full_name],
                ['icon' => 'bi-person-badge', 'label' => 'Father Name', 'value' => $employee->father_name ?: '—'],
                ['icon' => 'bi-person-heart', 'label' => 'Mother Name', 'value' => $employee->mother_name ?: '—'],
                ['icon' => 'bi-calendar3', 'label' => 'Date of Birth', 'value' => $employee->date_of_birth?->format('d M Y') ?? '—'],
                ['icon' => 'bi-calendar-check', 'label' => 'Date of Joining', 'value' => $employee->joining_date->format('d M Y')],
                ['icon' => 'bi-briefcase', 'label' => 'Position', 'value' => $employee->position],
                ['icon' => 'bi-shield-check', 'label' => 'Status', 'value' => $employee->status->label()],
                ['icon' => 'bi-geo-alt', 'label' => 'Address', 'value' => $employee->address ?: '—', 'wide' => true],
            ];
        @endphp
        <section class="result-section">
            <div class="oxf-wrap result-wrap">
                <article class="result-sheet" data-animate>
                    <header class="result-sheet__head">
                        <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="" class="result-sheet__emblem">
                        <div class="result-sheet__title">
                            <span>{{ $institute->get('name') }}</span>
                            <h2>Employee Verification</h2>
                        </div>
                        <span class="employee-seal employee-seal--{{ $seal['class'] }}"><i class="bi {{ $seal['icon'] }}" aria-hidden="true"></i> {{ $seal['text'] }}</span>
                    </header>

                    @if ($employee->status !== App\Enums\EmployeeStatus::Active)
                        <div @class(['employee-notice', 'employee-notice--inactive' => $employee->status === App\Enums\EmployeeStatus::Inactive]) role="alert">
                            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                            <span>
                                @if ($employee->status === App\Enums\EmployeeStatus::Relieved)
                                    This person has been relieved and <strong>is no longer an employee</strong> of {{ $institute->get('name') }}.
                                @else
                                    This employee record is currently <strong>inactive</strong>. Please contact the office before relying on it.
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="employee-profile">
                        @if ($employee->photo_url)
                            <img src="{{ $employee->photo_url }}" alt="Photo of {{ $employee->full_name }}" class="employee-profile__photo">
                        @else
                            <span class="employee-profile__photo employee-profile__initials" aria-hidden="true">{{ $employee->initials }}</span>
                        @endif
                        <div class="employee-profile__text">
                            <span>{{ $employee->registration_number }}</span>
                            <h3>{{ $employee->full_name }}</h3>
                            <p>{{ $employee->position }}</p>
                        </div>
                    </div>

                    <dl class="result-details employee-details">
                        @foreach ($details as $detail)
                            <div @class(['result-detail', 'result-detail--wide' => $detail['wide'] ?? false])>
                                <span class="result-detail__icon"><i class="bi {{ $detail['icon'] }}" aria-hidden="true"></i></span>
                                <div>
                                    <dt>{{ $detail['label'] }}</dt>
                                    <dd>{{ $detail['value'] }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>

                    @if ($employee->bio)
                        <div class="employee-bio">
                            <h4>Bio</h4>
                            <p>{{ $employee->bio }}</p>
                        </div>
                    @endif
                </article>
                <p class="employee-checked">Checked against our records on {{ now()->format('d M Y, h:i A') }}. <a href="{{ route('employees.verify') }}" class="no-print">Verify another employee</a></p>
            </div>
        </section>
    @elseif ($searched)
        <section class="result-section">
            <div class="oxf-wrap">
                <div class="result-empty" role="alert" data-animate>
                    <span class="result-empty__icon"><i class="bi bi-person-x" aria-hidden="true"></i></span>
                    <h3>No employee found</h3>
                    <p>We couldn't find an employee with registration number <strong>{{ Str::limit($registrationNumber, 30) }}</strong> and date of birth <strong>{{ $dateOfBirth->format('d F Y') }}</strong>. Please check both and try again, or <a href="{{ route('contact.create') }}">contact the office</a>.</p>
                </div>
            </div>
        </section>
    @endif

    @unless ($searched && $employee)
    <section @class(['lookup-section', 'no-print', 'bg-ivory' => $searched])>
        <div class="oxf-wrap">
            <div class="lookup-card" data-animate>
                <div class="lookup-card__info">
                    <span class="lookup-card__icon"><i class="bi bi-person-check" aria-hidden="true"></i></span>
                    <h2>{{ $searched ? 'Check another employee' : 'Verify an employee' }}</h2>
                    <p>Details are shown only when both the registration number and date of birth match our records.</p>
                    <ul>
                        <li><i class="bi bi-person-vcard" aria-hidden="true"></i><span><strong>Registration number</strong> Printed on the employee ID card, e.g. EMP-2026-0001</span></li>
                        <li><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Date of birth</strong> The employee's date of birth</span></li>
                        <li><i class="bi bi-shield-lock" aria-hidden="true"></i><span><strong>Private</strong> These details are never shown in the page address</span></li>
                    </ul>
                </div>
                <div class="lookup-card__form contact-form">
                    <span class="label">Employee lookup</span>
                    <h3 class="title-lg">Enter employee details</h3>
                    <form method="POST" action="{{ route('employees.verify.lookup') }}" class="grid gap-6" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <x-input name="registration_number" label="Registration number" required :value="$registrationNumber" placeholder="e.g. EMP-2026-0001" maxlength="30" autocomplete="off" class="uppercase placeholder:normal-case" />
                        <x-dob-field />
                        <div>
                            <button type="submit" class="btn-oxf btn-gold" :disabled="submitting">
                                <i class="bi bi-search" aria-hidden="true"></i> <span x-text="submitting ? 'Checking…' : 'Verify employee'">Verify employee</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @endunless
</x-layouts.public>
