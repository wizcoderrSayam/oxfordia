@php
    $formatMarks = fn ($value): string => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp
<x-layouts.public title="Results" description="Check your course results using your registration number and date of birth.">
    <section class="page-hero">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/student-study.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Results</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">Student results</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">Check your <em>result</em></h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Enter your registration number and date of birth to see your marksheet and download your result.</p>
        </div>
    </section>

    @if ($enrollments->isNotEmpty())
        @foreach ($enrollments as $enrollment)
        @php
            $student = $enrollment->student;
            $marks = $enrollment->marks->keyBy('course_module_id');
            $sessionStart = $enrollment->start_date ?? $enrollment->enrollment_date;
            $sessionEnd = $enrollment->completion_date ?? $enrollment->expected_completion_date;
            $marksheetUrl = $enrollment->course->modules->isNotEmpty() ? URL::temporarySignedRoute('results.marksheet', now()->addMinutes(30), ['enrollment' => $enrollment]) : null;
            $details = [
                ['icon' => 'bi-person-vcard', 'label' => 'Registration No', 'value' => $student->registration_number],
                ['icon' => 'bi-person', 'label' => 'Student Name', 'value' => $student->full_name],
                ['icon' => 'bi-person-badge', 'label' => 'Father Name', 'value' => $student->father_name ?: '—'],
                ['icon' => 'bi-journal-bookmark', 'label' => 'Course', 'value' => $enrollment->course->title],
            ];
        @endphp
        <section class="result-section">
            <div class="oxf-wrap result-wrap">
                <article class="result-sheet" data-animate>
                    <header class="result-sheet__head">
                        <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="" class="result-sheet__emblem">
                        <div class="result-sheet__title">
                            <span>{{ $institute->get('name') }}</span>
                            <h2>Statement of Result</h2>
                        </div>                    </header>

                    <dl class="result-details">
                        @foreach ($details as $detail)
                            <div class="result-detail">
                                <span class="result-detail__icon"><i class="bi {{ $detail['icon'] }}" aria-hidden="true"></i></span>
                                <div>
                                    <dt>{{ $detail['label'] }}</dt>
                                    <dd>{{ $detail['value'] }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </article>

                @if ($enrollment->course->modules->isNotEmpty())
                    @php
                        $modules = $enrollment->course->modules;
                        $obtained = fn (string $part) => $marks->sum(fn ($mark) => (float) $mark->{$part.'_marks'});
                        $isComplete = $modules->every(fn ($module) => $marks->has($module->id));
                        $grandObtained = $isComplete ? $formatMarks($marks->sum(fn ($mark) => (float) $mark->marks)) : '—';
                        $partCell = fn ($module, $value) => $module->hasPractical() ? $value : '—';
                    @endphp
                    <article class="result-card" data-animate>
                        <header class="result-card__head">
                            <div>
                                <span class="result-card__code">Marksheet</span>
                                <h3>Subject-wise marks</h3>
                            </div>
                        </header>
                        <div class="marks-table-wrap">
                            <table class="marksheet">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="marksheet__subject">Subjects</th>
                                        <th colspan="2">Max. Marks</th>
                                        <th colspan="2">Min. Marks</th>
                                        <th colspan="2">Marks Obt.</th>
                                        <th rowspan="2">Total Marks</th>
                                    </tr>
                                    <tr>
                                        @foreach (range(1, 3) as $group)
                                            <th><abbr title="Theory">Th.</abbr></th>
                                            <th><abbr title="Practical">Pr.</abbr></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $module)
                                        @php($mark = $marks->get($module->id))
                                        <tr>
                                            <td class="marksheet__subject">{{ $module->name }}</td>
                                            <td>{{ $module->theory_max_marks }}</td>
                                            <td>{{ $partCell($module, $module->practical_max_marks) }}</td>
                                            <td>{{ $module->theory_pass_marks }}</td>
                                            <td>{{ $partCell($module, $module->practical_pass_marks) }}</td>
                                            <td><strong>{{ $mark ? $formatMarks($mark->theory_marks) : '—' }}</strong></td>
                                            <td><strong>{{ $mark ? $partCell($module, $formatMarks($mark->practical_marks)) : '—' }}</strong></td>
                                            <td><strong>{{ $mark ? $formatMarks($mark->marks) : 'Awaiting' }}</strong></td>
                                        </tr>
                                    @endforeach
                                    <tr class="marksheet__total">
                                        <td class="marksheet__subject">Total</td>
                                        <td>{{ $modules->sum('theory_max_marks') }}</td>
                                        <td>{{ $modules->sum('practical_max_marks') }}</td>
                                        <td>{{ $modules->sum('theory_pass_marks') }}</td>
                                        <td>{{ $modules->sum('practical_pass_marks') }}</td>
                                        <td>{{ $isComplete ? $formatMarks($obtained('theory')) : '—' }}</td>
                                        <td>{{ $isComplete ? $formatMarks($obtained('practical')) : '—' }}</td>
                                        <td>{{ $grandObtained }}</td>
                                    </tr>
                                    <tr class="marksheet__grand">
                                        <td class="marksheet__subject">Grand Total</td>
                                        <td colspan="2">{{ $modules->sum('max_marks') }}</td>
                                        <td colspan="2">{{ $modules->sum('pass_marks') }}</td>
                                        <td colspan="2">{{ $grandObtained }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <footer class="marksheet__summary">
                            <span>Result: <strong>{{ $enrollment->result ? Str::upper($enrollment->result->label()) : 'Awaited' }}</strong></span>
                            <span>Percentage <strong>{{ $enrollment->percentage !== null ? $formatMarks($enrollment->percentage).'%' : '—' }}</strong></span>
                            <span>Grade: <strong>{{ $enrollment->final_grade ?: 'Awaited' }}</strong></span>
                        </footer>
                        <div class="marksheet-download no-print">
                            <span class="marksheet-download__icon"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i></span>
                            <div class="marksheet-download__text">
                                <strong>Your marksheet is ready</strong>
                                <span>Download a PDF copy of your result to save or print.</span>
                            </div>
                            <a href="{{ $marksheetUrl }}" class="marksheet-download__btn">
                                <i class="bi bi-download" aria-hidden="true"></i> Download result
                            </a>
                        </div>
                    </article>
                @endif
            </div>
        </section>
        @endforeach
    @elseif ($searched)
        <section class="result-section">
            <div class="oxf-wrap">
                <div class="result-empty" role="alert" data-animate>
                    <span class="result-empty__icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <h3>No matching record found</h3>
                    <p>We couldn't find a result for registration number <strong>{{ $registrationNumber }}</strong> with date of birth <strong>{{ $dateOfBirth->format('d F Y') }}</strong>. Please check both and try again. If your date of birth isn't on our records yet, <a href="{{ route('contact.create') }}">contact the office</a>.</p>
                </div>
            </div>
        </section>
    @endif

    @unless ($enrollments->isNotEmpty())
    <section @class(['lookup-section', 'no-print', 'bg-ivory' => $searched])>
        <div class="oxf-wrap">
            <div class="lookup-card" data-animate>
                <div class="lookup-card__info">
                    <span class="lookup-card__icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                    <h2>{{ $searched ? 'Check another result' : 'Find your result' }}</h2>
                    <p>Results are shown only when both details match our records.</p>
                    <ul>
                        <li><i class="bi bi-person-vcard" aria-hidden="true"></i><span><strong>Registration number</strong> Your student Reg. no., e.g. OXF-2026-0001. Your roll number also works.</span></li>
                        <li><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Date of birth</strong> As given at admission</span></li>
                        <li><i class="bi bi-shield-lock" aria-hidden="true"></i><span><strong>Private</strong> Your details are never shown in the page address</span></li>
                    </ul>
                </div>
                <div class="lookup-card__form contact-form">
                    <span class="label">Result lookup</span>
                    <h3 class="title-lg">Enter your details</h3>
                    <form method="POST" action="{{ route('results.lookup') }}" class="grid gap-6" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <x-input name="registration_number" label="Registration number" required :value="request('registration_number')" placeholder="e.g. OXF-2026-0001" maxlength="30" autocomplete="off" class="uppercase placeholder:normal-case" />
                        <x-dob-field />
                        <div>
                            <button type="submit" class="btn-oxf btn-gold" :disabled="submitting">
                                <i class="bi bi-search" aria-hidden="true"></i> <span x-text="submitting ? 'Checking…' : 'View result'">View result</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @endunless
</x-layouts.public>
