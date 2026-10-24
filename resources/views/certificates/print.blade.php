<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Print {{ $certificate->certificate_number }}</title>
    @include('certificates._styles')
    <style>
        body { margin: 0; background: #e2e8f0; font-family: Arial, sans-serif; }
        .toolbar { display: flex; gap: 8px; justify-content: center; padding: 16px; }
        .toolbar button, .toolbar a { padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #132147; color: #fff; border-color: #132147; }
        .sheet { margin: 0 auto 24px; width: 297mm; box-shadow: 0 10px 30px rgba(15, 23, 42, .2); }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print certificate</button>
        <a href="{{ route('admin.certificates.show', $certificate) }}">Back</a>
    </div>
    <div class="sheet">
        @include('certificates._certificate')
    </div>
</body>
</html>
