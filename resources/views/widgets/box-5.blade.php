<?php

$title = isset($title) ? $title : 'Title';
$style = isset($style) ? $style : 'success';
$number = isset($number) ? $number : '0.00';
$sub_title = isset($sub_title) ? $sub_title : 'Sub-titles';
$link = isset($link) ? $link : 'javascript:;';

if (!isset($is_dark)) {
    $is_dark = true;
}
$is_dark = ((bool) $is_dark);

$bg = '';
$text = 'text-primary';
$border = 'border-primary';
$text2 = 'text-dark';
if ($is_dark) {
    $bg = 'bg-primary';
    $text = 'text-white';
    $text2 = 'text-white';
}

if ($style == 'danger') {
    $text = 'text-white';
    $bg = 'bg-danger';
    $text2 = 'text-white';
    $border = 'border-danger';
}
?>
<style>
    .my-card {
        /* border to left side only */
        border: 0;
        border-left: 9px solid transparent;
        border-radius: 0rem;
    }
</style>
<a href="{{ $link }}" class="card {{ $bg }} {{ $border }} mb-3 mb-md-4 my-card">
    <div class="card-body py-0 py-0">
        <p class="h4  text-bold mb-2 mb-md-3 p-0 mt-3 m-0 {{ $text }} ">{{ $title }}</p>

        <p class="  m-0 text-right {{ $text2 }} h3" style="line-height: 3.2rem">{{ $number }}</p>
        <p class="mt-4 {{ $text2 }}">{{ $sub_title }}</p>
    </div>
</a>
{{-- 
#093300
--}}
