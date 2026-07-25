@component('emails.layout', ['subject' => $subject ?? 'Reset your password'])
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td style="padding:32px 32px 24px;text-align:center;border-bottom:1px solid #F4F4F5;">
            <span style="display:inline-block;padding:6px 14px;border-radius:999px;background-color:#FEE2E2;color:#E31E24;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                Password Reset
            </span>
            <h1 style="margin:16px 0 8px;font-size:26px;font-weight:900;color:#0A0A0A;line-height:1.2;letter-spacing:-0.5px;">
                Reset your password
            </h1>
            <p style="margin:0;font-size:14px;color:#71717A;">
                Hi {{ $user->name ?? 'there' }}, we received a request to reset your account password.
            </p>
        </td>
    </tr>

    <tr>
        <td style="padding:24px 32px;">
            <p style="margin:0 0 16px;font-size:15px;color:#3F3F46;line-height:1.7;">
                Click the button below to choose a new password. This secure link expires in
                <strong>{{ $expireMinutes }} minutes</strong> and can be used only once.
            </p>
            <p style="margin:0;font-size:14px;color:#71717A;line-height:1.6;">
                If you did not request a password reset, you can safely ignore this email. Your password will stay the same.
            </p>
        </td>
    </tr>

    <tr>
        <td style="padding:8px 32px 36px;text-align:center;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                <tr>
                    <td style="border-radius:10px;background-color:#E31E24;">
                        <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:14px 32px;font-size:14px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:10px;">
                            Reset Password
                        </a>
                    </td>
                </tr>
            </table>
            <p style="margin:20px 0 0;font-size:12px;color:#A1A1AA;line-height:1.6;word-break:break-all;">
                Or copy this link:<br>
                <a href="{{ $url }}" style="color:#E31E24;text-decoration:underline;">{{ $url }}</a>
            </p>
        </td>
    </tr>
</table>
@endcomponent
