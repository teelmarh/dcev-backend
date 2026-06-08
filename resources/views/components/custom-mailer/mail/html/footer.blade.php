@php
/** @var \App\Classes\CustomMailerManager $cmm */
@endphp
<!-- Thin border above footer -->
<table align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width:600px; margin:0 auto;">
    <tr>
        <td style="height:5px; background-image: repeating-linear-gradient(90deg, #C9A84C 0px, #C9A84C 10px, #002b80 10px, #002b80 20px, #E8C97A 20px, #E8C97A 30px, #1a3a9e 30px, #1a3a9e 40px); font-size:1px; line-height:5px;">&nbsp;</td>
    </tr>
</table>
<table class="footer-wrapper" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color: {{ $cmm->getThemeColor() }}; width:600px; margin:0 auto;">
    <tr>
        <td style="padding: 22px 32px 18px 32px;">
            <!-- Links row -->
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center" style="padding-bottom:12px;">
                        <a href="{{ $cmm->getUnsubscriptionUrl() }}" style="color:#E8C97A; font-size:12px; text-decoration:none; margin:0 12px;">Unsubscribe</a>
                        <span style="color:#1a3a9e; font-size:12px;">|</span>
                        <a href="{{ $cmm->getPrivacyPolicyUrl() }}" style="color:#E8C97A; font-size:12px; text-decoration:none; margin:0 12px;">Privacy Policy</a>
                        <span style="color:#1a3a9e; font-size:12px;">|</span>
                        <a href="{{ $cmm->getHelpCenterUrl() }}" style="color:#E8C97A; font-size:12px; text-decoration:none; margin:0 12px;">Help Center</a>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="color:#8fa8d8; font-size:11px; padding-top:4px;">
                        &copy; {{ date('Y') }} <strong style="color:#E8C97A;">{{ $cmm->getName() }}</strong>. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<!-- Bottom border strip -->
<table align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width:600px; margin:0 auto;">
    <tr>
        <td style="height:10px; background-image: repeating-linear-gradient(90deg, #C9A84C 0px, #C9A84C 14px, #002b80 14px, #002b80 28px, #C9A84C 28px, #C9A84C 42px, #FFFFFF 42px, #FFFFFF 56px, #1a3a9e 56px, #1a3a9e 70px, #C9A84C 70px, #C9A84C 84px, #002b80 84px, #002b80 98px, #E8C97A 98px, #E8C97A 112px); font-size:1px; line-height:10px;">&nbsp;</td>
    </tr>
</table>
