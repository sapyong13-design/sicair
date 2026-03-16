{{-- Smart Leave Calendar Component --}}
<div class="card sc-card sc-calendar-card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="card-title mb-0">
                <i class="ti ti-calendar me-2" style="color: var(--sc-primary);"></i>
                Kalender Cuti {{ date('Y') }}
            </h3>
            <div class="calendar-nav">
                <button class="btn btn-sm btn-outline-secondary" id="prevMonth">
                    <i class="ti ti-chevron-left"></i>
                </button>
                <span id="currentMonth" style="font-weight: 600; min-width: 150px; text-align: center;"></span>
                <button class="btn btn-sm btn-outline-secondary" id="nextMonth">
                    <i class="ti ti-chevron-right"></i>
                </button>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="calendar-grid">
            {{-- Day headers --}}
            <div class="calendar-header">
                <div class="calendar-day-header">Min</div>
                <div class="calendar-day-header">Sen</div>
                <div class="calendar-day-header">Sel</div>
                <div class="calendar-day-header">Rab</div>
                <div class="calendar-day-header">Kam</div>
                <div class="calendar-day-header">Jum</div>
                <div class="calendar-day-header">Sab</div>
            </div>

            {{-- Days --}}
            <div class="calendar-days" id="calendarDays"></div>
        </div>

        {{-- Legend --}}
        <div class="calendar-legend mt-4">
            <div class="legend-item">
                <div class="legend-color" style="background: var(--sc-primary-light);"></div>
                <span>Cuti Tahunan</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--sc-danger-light);"></div>
                <span>Cuti Sakit</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--sc-warning-light);"></div>
                <span>Cuti Lainnya</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--sc-success-light);"></div>
                <span>Hari Libur</span>
            </div>
        </div>
    </div>
</div>

<style>
.sc-calendar-card {
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.sc-calendar-card .card-body {
    padding: 1.5rem;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.calendar-nav .btn {
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
}

.calendar-grid {
    border: 1px solid var(--sc-gray-200);
    border-radius: 10px;
    overflow: hidden;
    background: white;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: var(--sc-gray-50);
    border-bottom: 2px solid var(--sc-gray-200);
}

.calendar-day-header {
    padding: 0.75rem;
    text-align: center;
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--sc-gray-700);
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--sc-gray-100);
    position: relative;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
    font-size: 0.85rem;
}

.calendar-day:hover {
    background: var(--sc-gray-50);
    z-index: 2;
}

.calendar-day.other-month {
    color: var(--sc-gray-300);
    background: var(--sc-gray-50);
}

.calendar-day.weekend {
    background: var(--sc-gray-50);
}

.calendar-day.holiday {
    background: var(--sc-success-light);
    color: var(--sc-success);
    font-weight: 700;
}

.calendar-day.leave {
    position: relative;
    color: white;
    font-weight: 700;
}

.calendar-day.leave.cuti_tahunan {
    background: var(--sc-primary);
}

.calendar-day.leave.cuti_sakit {
    background: var(--sc-danger);
}

.calendar-day.leave.leave-other {
    background: var(--sc-warning);
}

.calendar-day.leave.pending {
    opacity: 0.7;
    background: var(--sc-gray-400);
}

.calendar-day .tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--sc-gray-900);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 10;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
    margin-bottom: 0.5rem;
}

.calendar-day:hover .tooltip {
    opacity: 1;
}

.calendar-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--sc-gray-100);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--sc-gray-700);
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .calendar-nav {
        flex-direction: column;
        width: 100%;
    }

    .calendar-day {
        font-size: 0.75rem;
    }

    .calendar-day-header {
        padding: 0.5rem 0.25rem;
        font-size: 0.75rem;
    }

    .calendar-legend {
        gap: 1rem;
    }

    .legend-item {
        font-size: 0.75rem;
        flex: 0 0 calc(50% - 0.5rem);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date();
    const leaveData = @json($leavesByDate ?? []);
    const holidays = @json($holidays ?? []);

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // Update month display
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

        // Get first day and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        let html = '';

        // Previous month days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            html += `<div class="calendar-day other-month">${day}</div>`;
        }

        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayOfWeek = new Date(year, month, day).getDay();
            let classes = 'calendar-day';
            let content = day;
            let tooltip = '';

            // Check if weekend
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                classes += ' weekend';
            }

            // Check if holiday
            if (holidays[dateStr]) {
                classes += ' holiday';
                tooltip = `${holidays[dateStr]}`;
                content = `<span style="font-size: 0.7rem;">●</span>`;
            }

            // Check if leave
            if (leaveData[dateStr]) {
                const leave = leaveData[dateStr];
                classes += ` leave ${leave.type}`;
                if (leave.status === 'pending') classes += ' pending';
                tooltip = `${leave.type_label} - ${leave.status}`;
            }

            html += `<div class="${classes}" title="${tooltip}">
                      ${content}
                      ${tooltip ? `<div class="tooltip">${tooltip}</div>` : ''}
                    </div>`;
        }

        // Next month days
        const remainingDays = 42 - (firstDay + daysInMonth);
        for (let day = 1; day <= remainingDays; day++) {
            html += `<div class="calendar-day other-month">${day}</div>`;
        }

        document.getElementById('calendarDays').innerHTML = html;
    }

    // Navigation
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    // Initial render
    renderCalendar();
});
</script>
