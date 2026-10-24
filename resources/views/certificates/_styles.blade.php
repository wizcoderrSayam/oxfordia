<style>
    @page { size: A4 landscape; margin: 0; }
    /* Positions use explicit top/left/width/height because dompdf mis-sizes nested boxes positioned with right/bottom. */
    .cert-page { width: 297mm; height: 209mm; position: relative; overflow: hidden; background: #ffffff; font-family: 'DejaVu Sans', Arial, sans-serif; color: #1e293b; }
    .cert-frame { position: absolute; top: 8mm; left: 8mm; width: 275mm; height: 187mm; border: 3mm solid #132147; }
    .cert-border { position: absolute; top: 13.5mm; left: 13.5mm; width: 268.8mm; height: 180.8mm; border: 0.6mm solid #d4a017; }
    .cert-content { position: absolute; top: 20mm; left: 26mm; width: 245mm; text-align: center; }
    .cert-header { width: 100%; border-collapse: collapse; }
    .cert-header td { vertical-align: middle; }
    .cert-logo-cell { width: 45mm; text-align: left; }
    .cert-logo { max-height: 20mm; max-width: 40mm; }
    .cert-monogram { width: 18mm; height: 14.5mm; padding-top: 3.5mm; border-radius: 9mm; background: #132147; color: #f5b301; font-family: 'DejaVu Serif', Georgia, serif; font-size: 24pt; line-height: 1; font-weight: bold; text-align: center; }
    .cert-institute { text-align: center; }
    .cert-institute-name { font-family: 'DejaVu Serif', Georgia, serif; font-size: 22pt; font-weight: bold; color: #132147; letter-spacing: 1pt; text-transform: uppercase; }
    .cert-institute-tagline { font-size: 9pt; color: #64748b; margin-top: 1mm; }
    .cert-number-cell { width: 45mm; text-align: right; }
    .cert-label { font-size: 7pt; color: #64748b; text-transform: uppercase; letter-spacing: 1pt; }
    .cert-number { font-size: 9pt; font-weight: bold; color: #132147; font-family: 'DejaVu Sans Mono', monospace; }
    .cert-title { margin-top: 7mm; font-family: 'DejaVu Serif', Georgia, serif; font-size: 28pt; font-weight: bold; color: #b8860b; letter-spacing: 2pt; text-transform: uppercase; }
    .cert-rule { width: 60mm; height: 0.6mm; background: #132147; margin: 3mm auto 5mm; }
    .cert-text { font-size: 12pt; color: #475569; margin: 2mm 0; }
    .cert-name { font-family: 'DejaVu Serif', Georgia, serif; font-size: 28pt; font-weight: bold; color: #132147; margin: 3mm 0; }
    .cert-course { font-family: 'DejaVu Serif', Georgia, serif; font-size: 18pt; font-weight: bold; color: #1e293b; margin: 2mm 0 3mm; }
    .cert-date { margin-top: 3mm; font-size: 10.5pt; }
    .cert-footer { width: 100%; border-collapse: collapse; margin-top: 6mm; }
    .cert-footer td { vertical-align: bottom; }
    .cert-sign { width: 38%; text-align: center; }
    .cert-sign-image { height: 14mm; }
    .cert-sign-image img { max-height: 14mm; max-width: 50mm; }
    .cert-sign-line { width: 60mm; height: 0.3mm; background: #334155; margin: 1mm auto 2mm; }
    .cert-sign-name { font-size: 10.5pt; font-weight: bold; color: #132147; }
    .cert-sign-role { font-size: 9pt; color: #64748b; }
    .cert-qr { width: 24%; text-align: center; }
    .cert-qr-image { width: 26mm; height: 26mm; }
    .cert-qr-text { font-size: 7pt; color: #64748b; text-transform: uppercase; letter-spacing: 1pt; }
    .cert-small { position: absolute; top: 184mm; left: 26mm; width: 245mm; text-align: center; font-size: 7pt; color: #94a3b8; }
    .cert-void { position: absolute; top: 75mm; left: 0; width: 297mm; text-align: center; font-size: 90pt; font-weight: bold; color: #dc2626; opacity: 0.22; letter-spacing: 8pt; transform: rotate(-18deg); }
</style>
