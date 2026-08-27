<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Heymo sign-in code</title>
  </head>
  <body style="margin:0;background:#f4f7fb;color:#17233d;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
      Your one-time Heymo sign-in code is {{ $code }}.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:32px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #dbe4f0;">
            <tr>
              <td style="padding:24px 32px;background:#112957;color:#ffffff;">
                <div style="font-size:20px;font-weight:700;letter-spacing:.04em;">
                  <span style="display:inline-block;width:10px;height:14px;margin-right:7px;border-radius:8px 8px 8px 2px;background:#d92d3a;transform:rotate(-38deg);vertical-align:-1px;"></span>heymo<span style="color:#d92d3a;">!</span>
                </div>
                <div style="margin-top:12px;font-size:11px;line-height:16px;letter-spacing:.16em;text-transform:uppercase;color:#b8c9e6;">Campaign operations</div>
              </td>
            </tr>
            <tr>
              <td style="padding:40px 32px 32px;">
                <p style="margin:0;color:#d92d3a;font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;">Secure access</p>
                <h1 style="margin:10px 0 12px;color:#112957;font-size:28px;line-height:34px;">Your sign-in code</h1>
                <p style="margin:0;color:#667085;font-size:16px;line-height:25px;">Hi {{ $user->name }}, use this one-time code to open your Heymo workspace.</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
                  <tr>
                    <td align="center" style="padding:22px 12px;background:#e9f1fb;border:1px solid #dbe4f0;">
                      <div style="color:#112957;font-size:34px;line-height:40px;font-weight:700;letter-spacing:.18em;">{{ $code }}</div>
                    </td>
                  </tr>
                </table>
                <p style="margin:0;color:#17233d;font-size:14px;line-height:22px;">This code expires at <strong>{{ $expiresAt->format('g:i A T') }}</strong> and can be used once.</p>
                <p style="margin:18px 0 0;color:#667085;font-size:13px;line-height:20px;">If you did not request access, you can safely ignore this email. Heymo will never ask you to share this code.</p>
              </td>
            </tr>
            <tr>
              <td style="padding:20px 32px;border-top:1px solid #dbe4f0;color:#667085;font-size:11px;line-height:17px;">
                This is an automated message for {{ $user->email }}.<br>
                Heymo campaign operations
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
