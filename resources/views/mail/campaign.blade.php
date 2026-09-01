<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $headline }}</title>
</head>
<body style="margin:0;background:#f4f7fb;color:#17233d;font-family:{{ $bodyFont }};">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $headline }}</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #dbe4f0;">
          <tr>
            <td style="padding:24px 32px;background:{{ $primaryColor }};color:#ffffff;">
              <div style="font-size:20px;font-weight:700;letter-spacing:.04em;">{{ $brandName }}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:{{ $bodyPadding }};">
              <h1 style="margin:0 0 16px;color:{{ $primaryColor }};font-family:{{ $headingFont }};font-size:26px;line-height:32px;">{{ $headline }}</h1>

              @foreach ($paragraphs as $paragraph)
                <p style="margin:0 0 16px;color:#17233d;font-size:{{ $bodyFontSize }};line-height:1.6;">{{ $paragraph }}</p>
              @endforeach

              @foreach ($evidenceTexts as $evidence)
                <p style="margin:0 0 16px;color:#17233d;font-size:{{ $bodyFontSize }};line-height:1.6;">{{ $evidence }}</p>
              @endforeach

              @if ($offer)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background:#eef3fc;border:1px solid #dbe4f0;">
                  <tr>
                    <td style="padding:16px 20px;color:#17233d;font-size:{{ $bodyFontSize }};line-height:1.6;">{{ $offer }}</td>
                  </tr>
                </table>
              @endif

              @if ($nextStep)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background:#f2faf8;border:1px solid {{ $secondaryColor }};">
                  <tr>
                    <td style="padding:16px 20px;color:#17233d;font-size:{{ $bodyFontSize }};line-height:1.6;"><strong>Next step:</strong> {{ $nextStep }}</td>
                  </tr>
                </table>
              @endif

              <p style="margin:24px 0 0;color:#667085;font-size:12px;line-height:18px;">{{ $compliance }}</p>

              <p style="margin:24px 0 0;color:#17233d;font-size:{{ $bodyFontSize }};line-height:1.6;">
                {{ $valediction }},<br>{{ $signature }}
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:20px 32px;border-top:1px solid #dbe4f0;color:#667085;font-size:11px;line-height:17px;">
              You're receiving this because you opted in. <a href="{{ $unsubscribeUrl }}" style="color:#667085;">Unsubscribe</a>.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
