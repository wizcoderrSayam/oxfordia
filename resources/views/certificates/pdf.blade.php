<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->certificate_number }}</title>
    @include('certificates._styles')
    <style>
        html, body { margin: 0; padding: 0; }
        .cert-page { padding: 0; }
    </style>
</head>
<body>
    @include('certificates._certificate')
</body>
</html>
