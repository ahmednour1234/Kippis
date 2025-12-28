@php
    $direction = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
<script>
    document.documentElement.setAttribute('dir', '{{ $direction }}');
    document.documentElement.setAttribute('lang', '{{ app()->getLocale() }}');
</script>

