{{-- Theme Switcher & Customization Component --}}
<div class="theme-switcher-menu">
    {{-- Toggle Button --}}
    <button class="theme-toggle-btn" id="themeSwitcher" title="Ubah tema">
        <i class="ti ti-moon" id="themeIcon"></i>
    </button>

    {{-- Theme Menu (Dropdown) --}}
    <div class="theme-menu" id="themeMenu" style="display: none;">
        <div class="theme-menu-header">
            <h6>Preferensi Tema</h6>
            <button class="theme-menu-close" onclick="closeThemeMenu()">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="theme-menu-section">
            <label class="theme-label">Mode Tampilan</label>
            <div class="theme-options">
                <label class="theme-option">
                    <input type="radio" name="theme" value="light" id="lightMode">
                    <div class="theme-option-content">
                        <i class="ti ti-sun"></i>
                        <span>Light</span>
                    </div>
                </label>
                <label class="theme-option">
                    <input type="radio" name="theme" value="dark" id="darkMode">
                    <div class="theme-option-content">
                        <i class="ti ti-moon"></i>
                        <span>Dark</span>
                    </div>
                </label>
                <label class="theme-option">
                    <input type="radio" name="theme" value="auto" id="autoMode">
                    <div class="theme-option-content">
                        <i class="ti ti-brightness-auto"></i>
                        <span>Auto</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="theme-menu-section">
            <label class="theme-label">Palet Warna Utama</label>
            <div class="color-palette">
                <button class="color-option" data-color="primary" style="background: var(--sc-primary);" title="Primary"></button>
                <button class="color-option" data-color="indigo" style="background: #4f46e5;" title="Indigo"></button>
                <button class="color-option" data-color="purple" style="background: #7c3aed;" title="Purple"></button>
                <button class="color-option" data-color="pink" style="background: #ec4899;" title="Pink"></button>
                <button class="color-option" data-color="green" style="background: #10b981;" title="Green"></button>
                <button class="color-option" data-color="blue" style="background: #3b82f6;" title="Blue"></button>
            </div>
        </div>

        <div class="theme-menu-section">
            <label class="theme-label">Aksesibilitas</label>
            <div class="accessibility-options">
                <label class="accessibility-option">
                    <input type="checkbox" id="highContrast">
                    <span>Kontras Tinggi</span>
                </label>
                <label class="accessibility-option">
                    <input type="checkbox" id="largerText">
                    <span>Teks Lebih Besar</span>
                </label>
                <label class="accessibility-option">
                    <input type="checkbox" id="reduceAnimations">
                    <span>Kurangi Animasi</span>
                </label>
            </div>
        </div>

        <div class="theme-menu-footer">
            <button class="btn btn-sm btn-secondary w-100" onclick="resetTheme()">
                <i class="ti ti-refresh me-1"></i> Reset ke Default
            </button>
        </div>
    </div>
</div>

<style>
.theme-switcher-menu {
    position: relative;
}

.theme-toggle-btn {
    width: 40px;
    height: 40px;
    border: 1px solid var(--sc-gray-300);
    border-radius: 8px;
    background: white;
    color: var(--sc-gray-700);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.3s;
}

.theme-toggle-btn:hover {
    background: var(--sc-gray-100);
    border-color: var(--sc-gray-400);
}

.theme-menu {
    position: fixed;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    width: 320px;
    max-height: 90vh;
    background: white;
    border: 1px solid var(--sc-gray-200);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    z-index: 1050;
    overflow-y: auto;
}

.theme-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid var(--sc-gray-200);
}

.theme-menu-header h6 {
    margin: 0;
    font-weight: 700;
    font-size: 1rem;
}

.theme-menu-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--sc-gray-600);
    font-size: 1.2rem;
}

.theme-menu-section {
    padding: 1rem;
    border-bottom: 1px solid var(--sc-gray-100);
}

.theme-menu-section:last-of-type {
    border-bottom: none;
}

.theme-label {
    display: block;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--sc-gray-700);
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.theme-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.theme-option {
    position: relative;
    cursor: pointer;
}

.theme-option input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.theme-option-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 0.75rem;
    border: 2px solid var(--sc-gray-200);
    border-radius: 8px;
    transition: all 0.2s;
    font-size: 0.8rem;
    font-weight: 500;
}

.theme-option input:checked + .theme-option-content {
    border-color: var(--sc-primary);
    background: var(--sc-primary-light);
    color: var(--sc-primary);
}

.color-palette {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.5rem;
}

.color-option {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 8px;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.color-option:hover {
    transform: scale(1.1);
}

.color-option.active {
    border-color: var(--sc-gray-900);
    box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sc-gray-900);
}

.accessibility-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.accessibility-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--sc-gray-700);
}

.accessibility-option input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--sc-primary);
}

.theme-menu-footer {
    padding: 1rem;
    border-top: 1px solid var(--sc-gray-200);
}

/* Dark Mode Styles */
:root.dark-mode {
    --sc-gray-50: #1a1a1a;
    --sc-gray-100: #2d2d2d;
    --sc-gray-200: #3f3f3f;
    --sc-gray-300: #4f4f4f;
    --sc-gray-400: #6b7280;
    --sc-gray-500: #9ca3af;
    --sc-gray-600: #d1d5db;
    --sc-gray-700: #e5e7eb;
    --sc-gray-800: #f3f4f6;
    --sc-gray-900: #ffffff;
}

:root.dark-mode body {
    background: #111;
    color: #e5e7eb;
}

:root.dark-mode .card,
:root.dark-mode .navbar,
:root.dark-mode .sidebar {
    background: #1a1a1a;
    color: #e5e7eb;
    border-color: #3f3f3f;
}

:root.dark-mode .btn-outline-secondary {
    color: #d1d5db;
    border-color: #3f3f3f;
}

:root.dark-mode .btn-outline-secondary:hover {
    background: #2d2d2d;
    border-color: #4f4f4f;
}

:root.dark-mode .form-control,
:root.dark-mode .form-select {
    background: #2d2d2d;
    color: #e5e7eb;
    border-color: #3f3f3f;
}

:root.dark-mode .theme-menu {
    background: #1a1a1a;
    border-color: #3f3f3f;
}

:root.dark-mode .theme-option-content {
    border-color: #3f3f3f;
    color: #d1d5db;
}

/* Larger Text Mode */
:root.larger-text {
    font-size: 18px;
}

:root.larger-text .sc-stat-label {
    font-size: 1rem !important;
}

:root.larger-text h1, :root.larger-text h2, :root.larger-text h3 {
    font-size: 1.4em !important;
}

/* High Contrast Mode */
:root.high-contrast {
    --sc-primary: #000;
    --sc-danger: #000;
    --sc-warning: #000;
    --sc-success: #000;
}

:root.high-contrast body {
    background: white;
    color: black;
}

/* Reduce Animations */
:root.reduce-animations {
    --transition-speed: 0ms;
}

:root.reduce-animations * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
}

@media (max-width: 768px) {
    .theme-menu {
        position: fixed;
        top: 0;
        right: 0;
        transform: none;
        width: 100%;
        max-height: 100vh;
        border-radius: 0;
    }

    .theme-toggle-btn {
        width: 36px;
        height: 36px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeSwitcher = document.getElementById('themeSwitcher');
    const themeMenu = document.getElementById('themeMenu');
    const lightMode = document.getElementById('lightMode');
    const darkMode = document.getElementById('darkMode');
    const autoMode = document.getElementById('autoMode');
    const themeIcon = document.getElementById('themeIcon');

    // Load saved preferences
    const savedTheme = localStorage.getItem('sc-theme') || 'auto';
    const savedColor = localStorage.getItem('sc-color') || 'primary';
    const highContrast = localStorage.getItem('sc-high-contrast') === 'true';
    const largerText = localStorage.getItem('sc-larger-text') === 'true';
    const reduceAnimations = localStorage.getItem('sc-reduce-animations') === 'true';

    // Apply saved settings
    applyTheme(savedTheme);
    applyColor(savedColor);
    if (highContrast) document.getElementById('highContrast').checked = true;
    if (largerText) document.getElementById('largerText').checked = true;
    if (reduceAnimations) document.getElementById('reduceAnimations').checked = true;

    // Update UI
    if (savedTheme === 'light') lightMode.checked = true;
    else if (savedTheme === 'dark') darkMode.checked = true;
    else autoMode.checked = true;

    // Theme switcher toggle
    themeSwitcher.addEventListener('click', function(e) {
        e.stopPropagation();
        themeMenu.style.display = themeMenu.style.display === 'none' ? 'block' : 'none';
    });

    // Theme selection
    [lightMode, darkMode, autoMode].forEach(radio => {
        radio.addEventListener('change', function() {
            applyTheme(this.value);
            localStorage.setItem('sc-theme', this.value);
        });
    });

    // Color selection
    document.querySelectorAll('.color-option').forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.dataset.color;
            applyColor(color);
            localStorage.setItem('sc-color', color);
            document.querySelectorAll('.color-option').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Accessibility options
    document.getElementById('highContrast').addEventListener('change', function() {
        document.documentElement.classList.toggle('high-contrast', this.checked);
        localStorage.setItem('sc-high-contrast', this.checked);
    });

    document.getElementById('largerText').addEventListener('change', function() {
        document.documentElement.classList.toggle('larger-text', this.checked);
        localStorage.setItem('sc-larger-text', this.checked);
    });

    document.getElementById('reduceAnimations').addEventListener('change', function() {
        document.documentElement.classList.toggle('reduce-animations', this.checked);
        localStorage.setItem('sc-reduce-animations', this.checked);
    });

    function applyTheme(theme) {
        const isDark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark-mode', isDark);
        themeIcon.className = isDark ? 'ti ti-sun' : 'ti ti-moon';
    }

    function applyColor(color) {
        // Apply custom color logic here
        console.log('Apply color:', color);
    }

    // Close menu on outside click
    document.addEventListener('click', function(e) {
        if (!themeMenu.contains(e.target) && e.target !== themeSwitcher) {
            themeMenu.style.display = 'none';
        }
    });
});

function closeThemeMenu() {
    document.getElementById('themeMenu').style.display = 'none';
}

function resetTheme() {
    localStorage.clear();
    document.documentElement.className = '';
    location.reload();
}
</script>
