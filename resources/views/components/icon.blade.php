@props(['name' => 'check'])

@php
    // Semantic name → Bootstrap Icons class. Size with a text-* class on the call
    // site (font icons scale by font-size), colour with text-* (inherits currentColor).
    $map = [
        'users'       => 'bi-people',
        'play'        => 'bi-play-circle',
        'film'        => 'bi-collection-play',
        'clock'       => 'bi-clock',
        'chart'       => 'bi-bar-chart',
        'award'       => 'bi-award',
        'trophy'      => 'bi-trophy',
        'lock'        => 'bi-lock',
        'document'    => 'bi-file-text',
        'device'      => 'bi-phone',
        'question'    => 'bi-question-circle',
        'book'        => 'bi-book',
        'check'       => 'bi-check-lg',
        'cart'        => 'bi-cart3',
        'trash'       => 'bi-trash3',
        'tag'         => 'bi-tag',
        'shield'      => 'bi-shield-lock',
        'search'      => 'bi-search',
        'chevron'     => 'bi-chevron-down',
        'card'        => 'bi-credit-card',
        'qr'          => 'bi-qr-code',
        'success'     => 'bi-check-circle-fill',
        'error'       => 'bi-x-circle-fill',
        'warning'     => 'bi-exclamation-triangle',
        'undo'        => 'bi-arrow-counterclockwise',
        'spinner'     => 'bi-arrow-repeat',
        'star'        => 'bi-star-fill',
        'star-empty'  => 'bi-star',
        'arrow-right' => 'bi-arrow-right',
        'arrow-left'  => 'bi-arrow-left',
        'hand-wave'   => 'bi-emoji-smile',
    ];
    $icon = $map[$name] ?? 'bi-square';
@endphp

<i {{ $attributes->merge(['class' => 'bi ' . $icon . ' leading-none']) }} aria-hidden="true"></i>
