@php
    /** @var \App\Classes\CustomMailerManager $cmm */
@endphp
<!-- African border strip -->
<tr>
    <td class="african-border" style="height:10px; background-image: repeating-linear-gradient(90deg, #C9A84C 0px, #C9A84C 14px, #002b80 14px, #002b80 28px, #C9A84C 28px, #C9A84C 42px, #FFFFFF 42px, #FFFFFF 56px, #1a3a9e 56px, #1a3a9e 70px, #C9A84C 70px, #C9A84C 84px, #002b80 84px, #002b80 98px, #E8C97A 98px, #E8C97A 112px); font-size:1px; line-height:10px;">&nbsp;</td>
</tr>
<!-- Header with dark blue background -->
<tr>
    <td class="header" style="background-color: {{ $cmm->getThemeColor() }}; padding: 20px 32px;">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td style="text-align:left;">
                    <a href="{{ $cmm->getLogoHref() }}" style="display:inline-block; text-decoration:none;">
                        <img src="{{ asset('assets/images/logo.png') }}" width="180" alt="{{ $cmm->getName() }}" style="max-width:100%; vertical-align:middle; border:0; filter: brightness(0) invert(1);">
                        <span style="display:block; color:#E8C97A; font-size:13px; font-weight:700; letter-spacing:0.4px; margin-top:4px;">{{ $cmm->getName() }}</span>
                    </a>
                </td>
                <td style="text-align:right; color:#8fa8d8; font-size:12px; vertical-align:middle; padding-left:12px;">
                    NCAA Personnel Licensing
                </td>
            </tr>
        </table>
    </td>
</tr>
<!-- Thin border below header -->
<tr>
    <td style="height:5px; background-image: repeating-linear-gradient(90deg, #C9A84C 0px, #C9A84C 10px, #002b80 10px, #002b80 20px, #E8C97A 20px, #E8C97A 30px, #1a3a9e 30px, #1a3a9e 40px); font-size:1px; line-height:5px;">&nbsp;</td>
</tr>
