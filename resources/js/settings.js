const form = document.getElementById("settings") || document.getElementById("offcanvasSettings");

const defaultTheme = {
    theme: "light",
    "theme-base": "slate",
    "theme-font": "khmer", // Default font
    "theme-primary": "blue",
    "theme-radius": "1",
};

// Load theme from localStorage or defaults
function loadTheme() {
    for (const key in defaultTheme) {
        const value = localStorage.getItem("tabler-" + key) || defaultTheme[key];
        document.documentElement.setAttribute("data-bs-" + key, value);
    }
    applyThemeVariables();
}

// Apply CSS variables dynamically
function applyThemeVariables() {
    const theme = localStorage.getItem("tabler-theme") || defaultTheme.theme;
    const base = localStorage.getItem("tabler-theme-base") || defaultTheme["theme-base"];
    const primary = localStorage.getItem("tabler-theme-primary") || defaultTheme["theme-primary"];
    const radius = localStorage.getItem("tabler-theme-radius") || defaultTheme["theme-radius"];
    const font = localStorage.getItem("tabler-theme-font") || defaultTheme["theme-font"];

    // Update data attributes (used by Tabler CSS)
    document.documentElement.setAttribute("data-bs-theme", theme);
    document.documentElement.setAttribute("data-bs-theme-base", base);
    document.documentElement.setAttribute("data-bs-theme-primary", primary);
    document.documentElement.setAttribute("data-bs-theme-radius", radius);

    // Apply radius to all components via CSS variables
    const radiusRem = radius + "rem";
    const radiusVars = [
        "--tblr-border-radius",
        "--tblr-card-radius",
        "--tblr-btn-radius",
        "--tblr-input-radius",
        "--tblr-dropdown-radius",
        "--tblr-modal-radius",
        "--tblr-badge-radius",
    ];
    radiusVars.forEach(v => document.documentElement.style.setProperty(v, radiusRem));

    // Force font
    document.body.style.fontFamily = getFontFamily(font);

    // Apply primary color dynamically
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue(`--tblr-${primary}`);
    document.documentElement.style.setProperty("--tblr-primary", primaryColor);

    // Sync form radios with stored or default values
    form.querySelectorAll("[name]").forEach(input => {
        const stored = localStorage.getItem("tabler-" + input.name) || defaultTheme[input.name];
        input.checked = stored === input.value;
    });
}

// Listen for form changes
form?.addEventListener("change", e => {
    const { name, value } = e.target;
    if (!defaultTheme[name]) return;

    localStorage.setItem("tabler-" + name, value);
    document.documentElement.setAttribute("data-bs-" + name, value);
    applyThemeVariables();
});

// Reset button restores defaults
document.getElementById("reset-changes")?.addEventListener("click", () => {
    for (const key in defaultTheme) {
        localStorage.removeItem("tabler-" + key);
        document.documentElement.setAttribute("data-bs-" + key, defaultTheme[key]);
    }
    applyThemeVariables();
});

// Helper: map font keys to actual CSS font-family
function getFontFamily(fontKey) {
    const fonts = {
        khmer: "'Khmer OS Siemreap', sans-serif",
        "khmer-mef": "'Khmer MEF', sans-serif",
        battambang: "'Battambang', cursive",
        "noto-sans-khmer": "'Noto Sans Khmer', sans-serif",
        koulen: "'Koulen', cursive",
        freehand: "'Freehand', cursive",
        "sans-serif": "sans-serif",
    };
    return fonts[fontKey] || "sans-serif";
}

// Initialize theme
loadTheme();
