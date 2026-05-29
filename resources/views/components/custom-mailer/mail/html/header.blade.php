@php
    /** @var \App\Classes\CustomMailerManager $cmm */
@endphp
<tr>
    <td class="header">
        <a href="{{ $cmm->getLogoHref() }}" style="display: inline-block;">
            <img src="{{ asset('assets/images/logo.png') }}" width="300" alt="{{ ucfirst($cmm->getName()) }}" style="max-width: 100%; vertical-align: middle; line-height: 100%; border: 0;">
        </a>
    </td>
</tr>
<!-- <tr>
    <td class="subheader">
        <span>
            <img src="{{ asset('assets/images/custom-mailer-envelope.png') }}" width="60" height="53" alt="{{ ucfirst($cmm->getName()) }}">
        </span>
    </td>
</tr> -->
