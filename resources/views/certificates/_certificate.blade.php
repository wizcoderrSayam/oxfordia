{{--
    Shared certificate layout. Uses only tables and absolute positioning so it renders
    identically in dompdf (no flexbox/grid support) and in browsers.
--}}
<div class="cert-page">
    <div class="cert-frame"></div>
    <div class="cert-border"></div>

    <div class="cert-content">
        <table class="cert-header">
            <tr>
                <td class="cert-logo-cell">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="" class="cert-logo">
                    @else
                        <div class="cert-monogram">{{ mb_substr($settings['name'], 0, 1) }}</div>
                    @endif
                </td>
                <td class="cert-institute">
                    <div class="cert-institute-name">{{ $settings['name'] }}</div>
                    @if ($settings['tagline'])
                        <div class="cert-institute-tagline">{{ $settings['tagline'] }}</div>
                    @endif
                </td>
                <td class="cert-number-cell">
                    <div class="cert-label">Certificate No.</div>
                    <div class="cert-number">{{ $certificate->certificate_number }}</div>
                </td>
            </tr>
        </table>

        <div class="cert-title">{{ $settings['certificate_title'] }}</div>
        <div class="cert-rule"></div>

        <div class="cert-text">This is to certify that</div>
        <div class="cert-name">{{ $certificate->student_name_snapshot }}</div>
        <div class="cert-text">has successfully completed the course</div>
        <div class="cert-course">{{ $certificate->course_title_snapshot }}</div>
        @if ($certificate->grade_snapshot)
            <div class="cert-text">with <strong>Grade {{ $certificate->grade_snapshot }}</strong></div>
        @endif
        <div class="cert-text cert-date">Issued on {{ $certificate->issue_date->format('d F Y') }}</div>

        <table class="cert-footer">
            <tr>
                <td class="cert-sign">
                    <div class="cert-sign-image">@if ($signature1)<img src="{{ $signature1 }}" alt="">@endif</div>
                    <div class="cert-sign-line"></div>
                    <div class="cert-sign-name">{{ $settings['signatory_1_name'] }}</div>
                    <div class="cert-sign-role">{{ $settings['signatory_1_designation'] }}</div>
                </td>
                <td class="cert-qr">
                    <img src="{{ $qrCode }}" alt="QR code to verify this certificate" class="cert-qr-image">
                    <div class="cert-qr-text">Scan to verify</div>
                </td>
                <td class="cert-sign">
                    <div class="cert-sign-image">@if ($signature2)<img src="{{ $signature2 }}" alt="">@endif</div>
                    <div class="cert-sign-line"></div>
                    <div class="cert-sign-name">{{ $settings['signatory_2_name'] }}</div>
                    <div class="cert-sign-role">{{ $settings['signatory_2_designation'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="cert-small">
        {{ $settings['certificate_footer'] }} {{ $verificationUrl }}
        @if ($settings['accreditation_statement'])
            <br>{{ $settings['accreditation_statement'] }}
        @endif
    </div>

    @unless ($certificate->isValid())
        <div class="cert-void">REVOKED</div>
    @endunless
</div>
