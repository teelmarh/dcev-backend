@php
/** @var \App\Classes\CustomMailerManager $cmm */
@endphp
<table class="footer-wrapper" align="center" width="1090" cellpadding="0" cellspacing="0" role="presentation" style=" background-color: {{ $cmm->getThemeColor() }};">
    <tr>
        <td align="center">
            <div class="footer" style="clear: both; text-align: center; width: 1090px; height: 100%; padding: 1rem 0;">
                <table style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; padding: 10px;" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td class="content-block" style="font-family: Poppins, sans-serif; vertical-align: top; padding-bottom: 20px; padding-top: 10px; color: #fff; font-size: 12px; text-align: center;" valign="top" align="center">

                    <span class="apple-link" style="font-size: 16px; font-weight: normal; text-align: center; color: #ffffff;">
                        <span style="opacity: 0.75;">&copy; {{ date('Y') }} </span>
                        <strong>{{ $cmm->getName() }}</strong>.
                        <span style="opacity: 0.75;">All rights reserved.</span>
                    </span>

                    </td>
                </tr>
                <tr>
                    <table style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; margin: 1rem auto; width: 65%;" width="65%">
                        <tr>
                        <td class="content-block powered-by" style="font-family: Poppins, sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; color: #fff; font-size: 16px; text-align: center; border-right: 1px solid #ffffff88; padding: 0;" valign="top" align="center">
                            <a href="{{ $cmm->getUnsubscriptionUrl() }}" style="color: #fff; font-size: 14px; text-align: center; text-decoration: none; font-weight: lighter;" target="_blank">Unsubscribe</a>
                        </td>
                        <td class="content-block powered-by" style="font-family: Poppins, sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; color: #fff; font-size: 16px; text-align: center; border-right: 1px solid #ffffff88; padding: 0;" valign="top" align="center">
                            <a href="{{ $cmm->getPrivacyPolicyUrl() }}" style="color: #fff; font-size: 14px; text-align: center; text-decoration: none; font-weight: lighter;" target="_blank">Privacy Policy</a>
                        </td>
                        <td class="content-block powered-by" style="font-family: Poppins, sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; color: #fff; font-size: 16px; text-align: center; padding: 0;" valign="top" align="center">

                        <a href="{{ $cmm->getHelpCenterUrl() }}" style="color: #fff; font-size: 14px; text-align: center; text-decoration: none; font-weight: lighter;" target="_blank">Help Center</a>
                        </td>
                        </tr>
                    </table>
                </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
