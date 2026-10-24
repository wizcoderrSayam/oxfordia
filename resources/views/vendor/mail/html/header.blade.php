@props(['url'])
@php($instituteSettings = app(App\Services\InstituteSettings::class))
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl = $instituteSettings->logoUrl())
<img src="{{ $logoUrl }}" class="logo" alt="{{ $instituteSettings->get('name') }}">
@endif
{{ $instituteSettings->get('name') }}
</a>
</td>
</tr>
