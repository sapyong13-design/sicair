{{-- Empty States & Onboarding Component --}}
<div class="empty-state-container">
    <div class="empty-state-content">
        <div class="empty-state-icon" style="background: {{ $backgroundColor ?? 'var(--sh-gray-50)' }}; color: {{ $iconColor ?? 'var(--sh-gray-400)' }};">
            <i class="ti {{ $icon ?? 'ti-inbox' }}"></i>
        </div>

        <h3 class="empty-state-title">{{ $title ?? 'Tidak ada data' }}</h3>

        @if($description)
        <p class="empty-state-description">{{ $description }}</p>
        @endif

        @if($tips)
        <div class="empty-state-tips">
            <div class="tips-title">💡 Tips:</div>
            <ul class="tips-list">
                @foreach($tips as $tip)
                <li>{{ $tip }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($actionUrl && $actionText)
        <a href="{{ $actionUrl }}" class="btn sh-btn-primary mt-3">
            <i class="{{ $actionIcon ?? 'ti-plus' }} me-1"></i>
            {{ $actionText }}
        </a>
        @endif
    </div>
</div>

<style>
.empty-state-container {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    min-height: 400px;
    background: white;
    border-radius: 12px;
    border: 1px solid var(--sh-gray-200);
}

.empty-state-content {
    text-align: center;
    max-width: 400px;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 1.5rem;
}

.empty-state-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--sh-gray-900);
    margin: 0 0 0.75rem;
}

.empty-state-description {
    font-size: 0.95rem;
    color: var(--sh-gray-600);
    line-height: 1.5;
    margin: 0 0 1.5rem;
}

.empty-state-tips {
    text-align: left;
    background: var(--sh-gray-50);
    border-radius: 8px;
    padding: 1rem;
    margin: 1.5rem 0;
    border-left: 3px solid var(--sh-primary);
}

.tips-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--sh-gray-800);
    margin-bottom: 0.5rem;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    font-size: 0.85rem;
    color: var(--sh-gray-700);
    padding: 0.35rem 0;
    line-height: 1.4;
}

.tips-list li:before {
    content: '✓ ';
    color: var(--sh-success);
    font-weight: 700;
    margin-right: 0.5rem;
}

@media (max-width: 576px) {
    .empty-state-container {
        padding: 2rem 1rem;
        min-height: 300px;
    }

    .empty-state-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }

    .empty-state-title {
        font-size: 1.1rem;
    }

    .empty-state-description {
        font-size: 0.9rem;
    }
}
</style>

