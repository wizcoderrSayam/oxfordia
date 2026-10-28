@php
    $formatMarks = fn ($value): string => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp
<x-layouts.public title="Results" description="Check your course results using your roll number and date of birth.">
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
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Enter your roll number and date of birth to see your result details and the official result document.</p>
        </div>
    </section>

    @if ($searched && $enrollment)
        @php
            $student = $enrollment->student;
            $marks = $enrollment->marks->keyBy('course_module_id');
            $sessionStart = $enrollment->start_date ?? $enrollment->enrollment_date;
            $sessionEnd = $enrollment->completion_date ?? $enrollment->expected_completion_date;
            $hasDocument = $enrollment->hasResultDocument();
            $documentUrl = $hasDocument ? URL::temporarySignedRoute('results.document', now()->addMinutes(30), ['enrollment' => $enrollment]) : null;
            $details = [
                ['icon' => 'bi-person-vcard', 'label' => 'Roll No', 'value' => $enrollment->enrollment_number],
                ['icon' => 'bi-person', 'label' => 'Candidate Name', 'value' => $student->full_name],
                ['icon' => 'bi-calendar-range', 'label' => 'Session', 'value' => $sessionStart->format('d/m/Y').' – '.($sessionEnd?->format('d/m/Y') ?? 'Present')],
                ['icon' => 'bi-journal-bookmark', 'label' => 'Course Name', 'value' => $enrollment->course->title],
                ['icon' => 'bi-hourglass-split', 'label' => 'Duration', 'value' => $enrollment->course->duration_label ? Str::title($enrollment->course->duration_label) : '—'],
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
                        </div>
                        <button type="button" class="result-sheet__print no-print" onclick="window.print()"><i class="bi bi-printer" aria-hidden="true"></i> Print</button>
                    </header>

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
                        <div class="result-detail result-detail--grade">
                            <span class="result-detail__icon"><i class="bi bi-award" aria-hidden="true"></i></span>
                            <div>
                                <dt>Grade</dt>
                                <dd>{{ $enrollment->final_grade ?: 'Awaited' }}</dd>
                            </div>
                            @if ($enrollment->result)
                                <span class="result-chip result-chip--{{ $enrollment->result->value }}">{{ $enrollment->result->label() }}</span>
                            @endif
                        </div>
                    </dl>

                    @if ($enrollment->validCertificate)
                        <footer class="result-card__foot">
                            <span><i class="bi bi-patch-check" aria-hidden="true"></i> Certificate issued &middot; <strong>{{ $enrollment->validCertificate->certificate_number }}</strong></span>
                            <a href="{{ route('certificates.verify.show', $enrollment->validCertificate->certificate_number) }}" class="link-arrow no-print">Verify certificate <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </footer>
                    @endif
                </article>

                @if ($hasDocument)
                    <section class="result-doc" data-animate>
                        <div class="result-doc__head">
                            <span class="label">Official copy</span>
                            <h3 class="title-lg">Result document</h3>
                        </div>
                        <div class="result-doc__frame">
                            @if ($enrollment->resultDocumentIsPdf())
                                <iframe src="{{ $documentUrl }}#view=FitH&toolbar=0" title="Result document for {{ $student->full_name }}" class="result-doc__pdf"></iframe>
                            @else
                                <img src="{{ $documentUrl }}" alt="Result document for {{ $student->full_name }}">
                            @endif
                        </div>
                    </section>
                @else
                    <div class="result-doc-note" data-animate>
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        <p>The official result document will appear here once the office uploads it.</p>
                    </div>
                @endif

                {{-- Module-wise marks are hidden for now; change false to $marks->isNotEmpty() to show them again. --}}
                @if (false)
                    <article class="result-card" data-animate>
                        <header class="result-card__head">
                            <div>
                                <span class="result-card__code">Module-wise marks</span>
                                <h3>{{ $enrollment->total_marks !== null ? $formatMarks($enrollment->total_marks).' / '.$enrollment->max_total_marks : 'Marks' }}@if ($enrollment->percentage !== null) &middot; {{ $formatMarks($enrollment->percentage) }}%@endif</h3>
                            </div>
                        </header>
                        <div class="marks-table-wrap">
                            <table class="marks-table">
                                <thead>
                                    <tr><th>Module</th><th class="num">Max</th><th class="num">Pass</th><th class="num">Obtained</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($enrollment->course->modules as $module)
                                        @php($mark = $marks->get($module->id))
                                        <tr>
                                            <td>
                                                <span class="marks-table__module">{{ $module->name }}</span>
                                                @if ($mark)
                                                    <span class="result-bar result-bar--sm"><span style="width: {{ $module->max_marks > 0 ? min(100, (float) $mark->marks / $module->max_marks * 100) : 0 }}%"></span></span>
                                                @endif
                                            </td>
                                            <td class="num">{{ $module->max_marks }}</td>
                                            <td class="num">{{ $module->pass_marks }}</td>
                                            <td class="num"><strong>{{ $mark ? $formatMarks($mark->marks) : '—' }}</strong></td>
                                            <td>
                                                @if (! $mark)
                                                    <span class="result-chip result-chip--pending">Awaiting</span>
                                                @elseif ((float) $mark->marks >= $module->pass_marks)
                                                    <span class="result-chip result-chip--pass">Passed</span>
                                                @else
                                                    <span class="result-chip result-chip--fail">Not passed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endif
            </div>
        </section>
    @elseif ($searched)
        <section class="result-section">
            <div class="oxf-wrap">
                <div class="result-empty" role="alert" data-animate>
                    <span class="result-empty__icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <h3>No matching record found</h3>
                    <p>We couldn't find a result for roll number <strong>{{ $rollNumber }}</strong> with date of birth <strong>{{ $dateOfBirth->format('d F Y') }}</strong>. Please check both and try again. If your date of birth isn't on our records yet, <a href="{{ route('contact.create') }}">contact the office</a>.</p>
                </div>
            </div>
        </section>
    @endif

    <section @class(['lookup-section', 'no-print', 'bg-ivory' => $searched])>
        <div class="oxf-wrap">
            <div class="lookup-card" data-animate>
                <div class="lookup-card__info">
                    <span class="lookup-card__icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                    <h2>{{ $searched ? 'Check another result' : 'Find your result' }}</h2>
                    <p>Results are shown only when both details match our records.</p>
                    <ul>
                        <li><i class="bi bi-person-vcard" aria-hidden="true"></i><span><strong>Roll number</strong> Your enrollment number, e.g. ENR-2026-00001</span></li>
                        <li><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Date of birth</strong> As given at admission</span></li>
                        <li><i class="bi bi-shield-lock" aria-hidden="true"></i><span><strong>Private</strong> Your details are never shown in the page address</span></li>
                    </ul>
                </div>
                <div class="lookup-card__form contact-form">
                    <span class="label">Result lookup</span>
                    <h3 class="title-lg">Enter your details</h3>
                    <form method="POST" action="{{ route('results.lookup') }}" class="grid gap-6" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <x-input name="roll_number" label="Roll number" required :value="request('roll_number')" placeholder="e.g. ENR-2026-00001" maxlength="30" autocomplete="off" class="uppercase placeholder:normal-case" />
                        <fieldset class="dob-field">
                            <legend class="form-label">Date of birth <span class="text-red-500" aria-hidden="true">*</span></legend>
                            <div class="dob-field__selects">
                                <label class="sr-only" for="dob_day">Day</label>
                                <select id="dob_day" name="dob_day" class="form-input" required>
                                    <option value="">Day</option>
                                    @foreach (range(1, 31) as $day)
                                        <option value="{{ $day }}" @selected((int) old('dob_day', request('dob_day')) === $day)>{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endforeach
                                </select>
                                <label class="sr-only" for="dob_month">Month</label>
                                <select id="dob_month" name="dob_month" class="form-input" required>
                                    <option value="">Month</option>
                                    @foreach (range(1, 12) as $month)
                                        <option value="{{ $month }}" @selected((int) old('dob_month', request('dob_month')) === $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
                                    @endforeach
                                </select>
                                <label class="sr-only" for="dob_year">Year</label>
                                <select id="dob_year" name="dob_year" class="form-input" required>
                                    <option value="">Year</option>
                                    @foreach (range(now()->year, 1950) as $year)
                                        <option value="{{ $year }}" @selected((int) old('dob_year', request('dob_year')) === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @foreach (['date_of_birth', 'dob_day', 'dob_month', 'dob_year'] as $dobField)
                                @error($dobField) <p class="form-error">{{ $message }}</p> @break @enderror
                            @endforeach
                        </fieldset>
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
</x-layouts.public>
