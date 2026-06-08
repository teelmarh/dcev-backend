<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 620px) {
    .inner-body { width: 100% !important; }
    .footer-wrapper { width: 100% !important; }
    .footer { width: 100% !important; }
    .content-cell { padding: 20px !important; }
}
@media only screen and (max-width: 500px) {
    .action a { width: 100% !important; display: block; text-align: center; }
}
</style>
    <style>
        @php
            $cssFile = resource_path('views/components/custom-mailer/mail/html/themes/default.css');
            include(str_replace('/', DIRECTORY_SEPARATOR, $cssFile));
        @endphp
    </style>
</head>
<body style="background-color:#F3EFE6; margin:0; padding:20px 0;">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#F3EFE6;">
    <tr>
        <td align="center">
            <table align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width:600px; background-color:#ffffff; border-left:4px solid #C9A84C; border-right:4px solid #C9A84C; box-shadow:0 4px 24px rgba(27,67,50,0.10);">

                {{ $header ?? '' }}

                <!-- Body -->
                <tr>
                    <td class="content-cell" style="padding:28px 36px 24px 36px;">
                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                    </td>
                </tr>

            </table>

            {{ $footer ?? '' }}

        </td>
    </tr>
</table>

</body>
</html>
