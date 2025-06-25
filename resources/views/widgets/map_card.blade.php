{{-- resources/views/widgets/map_card.blade.php --}}

<div class="card" style="height: 380px; display: flex; flex-direction: column; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #e8e8e8;">
    {{-- Card Header with Title and "View More" Link --}}
    <div class="card-header" style="background-color: #fff; border-bottom: 1px solid #e8e8e8; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 1.1rem; font-weight: 600;">
            <i class="{{ $icon ?? 'icon-map' }}" style="margin-right: 8px; color: #555;"></i>{{ $title ?? 'Map View' }}
        </h3>
        @if(isset($view_more_link) && $view_more_link)
            <a href="{{ $view_more_link }}" class="btn btn-default btn-xs" style="font-weight: 600;">
                View Full Map <i class="icon-arrow-right"></i>
            </a>
        @endif
    </div>

    {{-- The included map content will fill the remaining space --}}
    <div class="card-body" style="flex: 1; padding: 0;">
        @include($map_content_view, ['markers' => $markers])
    </div>
</div>