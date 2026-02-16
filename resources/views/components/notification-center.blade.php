{{-- Notification Center Component --}}
<div class="notification-center">
    <div class="notification-header">
        <h3 class="mb-0">
            <i class="ti ti-bell me-2" style="color: var(--sh-primary);"></i>
            Notifikasi
        </h3>
        @if($unreadCount > 0)
        <span class="badge sh-badge-primary">{{ $unreadCount }}</span>
        @endif
    </div>

    <div class="notification-tabs" role="tablist">
        <button class="notification-tab active" data-tab="all">
            <i class="ti ti-inbox"></i> Semua
        </button>
        <button class="notification-tab" data-tab="status">
            <i class="ti ti-file-check"></i> Status
        </button>
        <button class="notification-tab" data-tab="system">
            <i class="ti ti-alert-circle"></i> Sistem
        </button>
    </div>

    <div class="notification-list" id="notificationList">
        @forelse($notifications as $notification)
            <div class="notification-item {{ !$notification->read_at ? 'unread' : '' }}" data-notification-id="{{ $notification->id }}">
                <div class="notification-icon">
                    @switch($notification->type)
                        @case('leave_request_submitted')
                            <i class="ti ti-file-plus" style="color: var(--sh-primary);"></i>
                            @break
                        @case('leave_request_approved')
                            <i class="ti ti-circle-check" style="color: var(--sh-success);"></i>
                            @break
                        @case('leave_request_rejected')
                            <i class="ti ti-circle-x" style="color: var(--sh-danger);"></i>
                            @break
                        @case('needs_review')
                            <i class="ti ti-user-check" style="color: var(--sh-warning);"></i>
                            @break
                        @case('pending_decision')
                            <i class="ti ti-gavel" style="color: var(--sh-primary);"></i>
                            @break
                        @default
                            <i class="ti ti-bell" style="color: var(--sh-gray-500);"></i>
                    @endswitch
                </div>

                <div class="notification-content">
                    <div class="notification-title">{{ $notification->title }}</div>
                    <div class="notification-message">{{ Str::limit($notification->message, 100) }}</div>
                    <div class="notification-meta">
                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                        @if($notification->data['leave_id'] ?? null)
                        <a href="{{ route('leave.show', $notification->data['leave_id']) }}" class="notification-action">
                            Lihat Detail
                        </a>
                        @endif
                    </div>
                </div>

                <div class="notification-actions">
                    @if(!$notification->read_at)
                    <button class="notification-btn mark-read" data-id="{{ $notification->id }}" title="Tandai sebagai dibaca">
                        <i class="ti ti-circle-check"></i>
                    </button>
                    @endif
                    <button class="notification-btn delete-notification" data-id="{{ $notification->id }}" title="Hapus">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="notification-empty">
                <div class="notification-empty-icon">
                    <i class="ti ti-inbox"></i>
                </div>
                <div class="notification-empty-text">Tidak ada notifikasi</div>
                <div class="notification-empty-desc">Anda sudah menangani semua notifikasi</div>
            </div>
        @endforelse
    </div>

    @if($notifications->count() > 0)
    <div class="notification-footer">
        <button class="notification-footer-btn mark-all-read" id="markAllRead">
            <i class="ti ti-circle-check me-1"></i> Tandai semua sebagai dibaca
        </button>
    </div>
    @endif
</div>

<style>
.notification-center {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    max-height: 600px;
    display: flex;
    flex-direction: column;
}

.notification-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--sh-gray-100);
    background: var(--sh-gray-50);
}

.notification-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
}

.notification-header .badge {
    font-size: 0.75rem;
    padding: 0.3rem 0.6rem;
}

.notification-tabs {
    display: flex;
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid var(--sh-gray-100);
    background: var(--sh-gray-50);
    gap: 0.25rem;
}

.notification-tab {
    flex: 1;
    padding: 0.5rem;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    color: var(--sh-gray-600);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.notification-tab:hover {
    background: var(--sh-gray-100);
    color: var(--sh-gray-800);
}

.notification-tab.active {
    background: white;
    color: var(--sh-primary);
    font-weight: 600;
    border: 2px solid var(--sh-primary);
}

.notification-list {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--sh-gray-100);
    cursor: pointer;
    transition: background 0.2s;
}

.notification-item:hover {
    background: var(--sh-gray-50);
}

.notification-item.unread {
    background: var(--sh-primary-light);
    border-left: 3px solid var(--sh-primary);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--sh-gray-100);
    flex-shrink: 0;
    font-size: 1.2rem;
}

.notification-item.unread .notification-icon {
    background: var(--sh-primary-light);
    color: var(--sh-primary);
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--sh-gray-900);
    margin-bottom: 0.25rem;
}

.notification-message {
    font-size: 0.85rem;
    color: var(--sh-gray-600);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.notification-time {
    font-size: 0.75rem;
    color: var(--sh-gray-500);
}

.notification-action {
    font-size: 0.75rem;
    color: var(--sh-primary);
    text-decoration: none;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    transition: background 0.2s;
}

.notification-action:hover {
    background: var(--sh-primary-light);
}

.notification-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.notification-item:hover .notification-actions {
    opacity: 1;
}

.notification-btn {
    background: none;
    border: none;
    padding: 0.4rem;
    border-radius: 6px;
    color: var(--sh-gray-500);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-btn:hover {
    background: var(--sh-gray-200);
    color: var(--sh-gray-700);
}

.notification-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: var(--sh-gray-500);
    text-align: center;
}

.notification-empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.notification-empty-text {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--sh-gray-600);
}

.notification-empty-desc {
    font-size: 0.85rem;
    color: var(--sh-gray-500);
}

.notification-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--sh-gray-100);
    background: var(--sh-gray-50);
}

.notification-footer-btn {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid var(--sh-gray-200);
    border-radius: 8px;
    background: white;
    color: var(--sh-gray-700);
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-footer-btn:hover {
    background: var(--sh-gray-100);
    border-color: var(--sh-gray-300);
}

@media (max-width: 576px) {
    .notification-center {
        max-height: 100vh;
        border-radius: 0;
    }

    .notification-item {
        padding: 0.85rem 1rem;
    }

    .notification-actions {
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle notification tabs
    document.querySelectorAll('.notification-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const filterType = this.dataset.tab;
            document.querySelectorAll('.notification-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterNotifications(filterType);
        });
    });

    // Mark as read
    document.querySelectorAll('.mark-read').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const notificationId = this.dataset.id;
            markNotificationAsRead(notificationId);
        });
    });

    // Delete notification
    document.querySelectorAll('.delete-notification').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const notificationId = this.dataset.id;
            deleteNotification(notificationId);
        });
    });

    // Mark all as read
    const markAllBtn = document.getElementById('markAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            markAllNotificationsAsRead();
        });
    }

    // Click notification to view detail
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const leaveLink = this.querySelector('.notification-action');
            if (leaveLink) {
                window.location.href = leaveLink.href;
            }
        });
    });

    function filterNotifications(type) {
        // Implement filtering logic
        console.log('Filter by:', type);
    }

    function markNotificationAsRead(id) {
        fetch(`/notifications/${id}/mark-read`, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(r => r.json())
          .then(data => {
              document.querySelector(`[data-notification-id="${id}"]`).classList.remove('unread');
          });
    }

    function deleteNotification(id) {
        if (confirm('Hapus notifikasi ini?')) {
            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(r => r.json())
              .then(data => {
                  document.querySelector(`[data-notification-id="${id}"]`).remove();
              });
        }
    }

    function markAllNotificationsAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(r => r.json())
          .then(data => {
              document.querySelectorAll('.notification-item.unread').forEach(item => {
                  item.classList.remove('unread');
              });
          });
    }
});
</script>
