@php
    /** @var \App\Classes\CustomMailerManager $cmm */
@endphp
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}"  style="text-decoration: none; color: #fff; padding: 12px 32px; background: {{ $cmm->getThemeColor() }}; box-shadow: 0px 14px 14px rgba(0, 0, 0, 0.1); border-radius: 4px;" target="_blank" rel="noopener">{{ $slot }}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
