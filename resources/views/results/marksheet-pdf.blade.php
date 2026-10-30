{{--
    Marksheet PDF. Uses only tables so it renders correctly in dompdf (no flexbox/grid support).
--}}
@php
    $formatMarks = fn ($value): string => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    $student = $enrollment->student;
    $modules = $enrollment->course->modules;
    $isComplete = $modules->isNotEmpty() && $modules->every(fn ($module) => $marks->has($module->id));
    $obtained = fn (string $part) => $marks->sum(fn ($mark) => (float) $mark->{$part.'_marks'});
    $grandObtained = $isComplete ? $formatMarks($marks->sum(fn ($mark) => (float) $mark->marks)) : '—';
    $partCell = fn ($module, $value) => $module->hasPractical() ? $value : '—';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Marksheet {{ $enrollment->student->registration_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm; }
        body { margin: 0; font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1e293b; }
        .sheet { border: 2.5mm solid #132147; padding: 1.5mm; }
        .sheet-inner { border: 0.5mm solid #d4a017; padding: 8mm 8mm 10mm; }
        .head { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: middle; }
        .logo-cell { width: 30mm; }
        .logo { max-height: 18mm; max-width: 28mm; }
        .monogram { width: 15mm; height: 12mm; padding-top: 3mm; border-radius: 7.5mm; background: #132147; color: #f5b301; font-family: 'DejaVu Serif', Georgia, serif; font-size: 20pt; font-weight: bold; line-height: 1; text-align: center; }
        .institute { text-align: center; }
        .institute-name { font-family: 'DejaVu Serif', Georgia, serif; font-size: 18pt; font-weight: bold; color: #132147; letter-spacing: 1pt; text-transform: uppercase; }
        .tagline { margin-top: 1mm; font-size: 8.5pt; color: #64748b; }
        .title { margin: 6mm 0 1mm; font-family: 'DejaVu Serif', Georgia, serif; font-size: 16pt; font-weight: bold; color: #b8860b; letter-spacing: 2pt; text-align: center; text-transform: uppercase; }
        .rule { width: 40mm; height: 0.5mm; margin: 2mm auto 6mm; background: #132147; }
        .details { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
        .details td { width: 50%; padding: 2.5mm 3mm; border: 0.3mm solid #cbd5e1; vertical-align: top; }
        .label { display: block; font-size: 7pt; font-weight: bold; letter-spacing: 0.8pt; color: #64748b; text-transform: uppercase; }
        .value { display: block; margin-top: 0.8mm; font-family: 'DejaVu Serif', Georgia, serif; font-size: 11.5pt; color: #132147; }
        .marks { width: 100%; border-collapse: collapse; }
        .marks th, .marks td { padding: 2.5mm 2mm; border: 0.3mm solid #94a3b8; text-align: center; vertical-align: middle; }
        .marks th { background: #f1ede2; font-size: 8pt; color: #132147; text-transform: uppercase; }
        .marks .subject { width: 34%; text-align: left; font-weight: bold; color: #132147; }
        .marks .totals td { background: #faf8f3; font-weight: bold; color: #132147; }
        .summary { width: 100%; border-collapse: collapse; margin-top: 0; }
        .summary td { width: 33.33%; padding: 3mm; border: 0.3mm solid #94a3b8; border-top: 0; background: #132147; color: #e2e8f0; font-size: 10pt; }
        .summary strong { color: #f5b301; font-size: 11.5pt; }
        .summary .middle { text-align: center; }
        .summary .last { text-align: right; }
        .note { margin-top: 8mm; font-size: 7.5pt; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="sheet-inner">
            <table class="head">
                <tr>
                    <td class="logo-cell">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="" class="logo">
                        @else
                            <div class="monogram">{{ mb_substr($settings['name'], 0, 1) }}</div>
                        @endif
                    </td>
                    <td class="institute">
                        <div class="institute-name">{{ $settings['name'] }}</div>
                        @if ($settings['tagline'])
                            <div class="tagline">{{ $settings['tagline'] }}</div>
                        @endif
                    </td>
                    <td class="logo-cell"></td>
                </tr>
            </table>

            <div class="title">Statement of Marks</div>
            <div class="rule"></div>

            <table class="details">
                <tr>
                    <td><span class="label">Registration No</span><span class="value">{{ $student->registration_number }}</span></td>
                    <td><span class="label">Student Name</span><span class="value">{{ $student->full_name }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Father Name</span><span class="value">{{ $student->father_name ?: '—' }}</span></td>
                    <td><span class="label">Course</span><span class="value">{{ $enrollment->course->title }}</span></td>
                </tr>
            </table>

            <table class="marks">
                <thead>
                    <tr>
                        <th rowspan="2" class="subject">Subjects</th>
                        <th colspan="2">Max. Marks</th>
                        <th colspan="2">Min. Marks</th>
                        <th colspan="2">Marks Obt.</th>
                        <th rowspan="2">Total Marks</th>
                    </tr>
                    <tr>
                        @foreach (range(1, 3) as $group)
                            <th>Th.</th>
                            <th>Pr.</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $module)
                        @php($mark = $marks->get($module->id))
                        <tr>
                            <td class="subject">{{ $module->name }}</td>
                            <td>{{ $module->theory_max_marks }}</td>
                            <td>{{ $partCell($module, $module->practical_max_marks) }}</td>
                            <td>{{ $module->theory_pass_marks }}</td>
                            <td>{{ $partCell($module, $module->practical_pass_marks) }}</td>
                            <td>{{ $mark ? $formatMarks($mark->theory_marks) : '—' }}</td>
                            <td>{{ $mark ? $partCell($module, $formatMarks($mark->practical_marks)) : '—' }}</td>
                            <td><strong>{{ $mark ? $formatMarks($mark->marks) : 'Awaiting' }}</strong></td>
                        </tr>
                    @endforeach
                    <tr class="totals">
                        <td class="subject">Total</td>
                        <td>{{ $modules->sum('theory_max_marks') }}</td>
                        <td>{{ $modules->sum('practical_max_marks') }}</td>
                        <td>{{ $modules->sum('theory_pass_marks') }}</td>
                        <td>{{ $modules->sum('practical_pass_marks') }}</td>
                        <td>{{ $isComplete ? $formatMarks($obtained('theory')) : '—' }}</td>
                        <td>{{ $isComplete ? $formatMarks($obtained('practical')) : '—' }}</td>
                        <td>{{ $grandObtained }}</td>
                    </tr>
                    <tr class="totals">
                        <td class="subject">Grand Total</td>
                        <td colspan="2">{{ $modules->sum('max_marks') }}</td>
                        <td colspan="2">{{ $modules->sum('pass_marks') }}</td>
                        <td colspan="2">{{ $grandObtained }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <table class="summary">
                <tr>
                    <td>Result: <strong>{{ $enrollment->result ? Str::upper($enrollment->result->label()) : 'Awaited' }}</strong></td>
                    <td class="middle">Percentage <strong>{{ $enrollment->percentage !== null ? $formatMarks($enrollment->percentage).'%' : '—' }}</strong></td>
                    <td class="last">Grade: <strong>{{ $enrollment->final_grade ?: 'Awaited' }}</strong></td>
                </tr>
            </table>

            <p class="note">Generated on {{ now()->format('d F Y') }} from the {{ $settings['name'] }} online result service.</p>
        </div>
    </div>
</body>
</html>
