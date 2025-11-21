document.addEventListener("DOMContentLoaded", () => {
    const themeConfig = {
        theme: "light",
        "theme-base": "gray",
        "theme-primary": "blue",
        "theme-radius": "1",
        "theme-font": "khmer",
        "theme-font-size": "14px",
        "theme-line-height": "1.2",
        "theme-sidebar-width": "15%", // Fixed, not changeable
    };

    const fontMap = {
        khmer: "khmer",
        "khmer-mef": "khmer-mef",
        battambang: "Battambang",
        "noto-sans-khmer": "Noto Sans Khmer",
        koulen: "Koulen",
        freehand: "Freehand",
    };

    const form = document.getElementById("settings");
    const resetButton = document.getElementById("reset-changes");

    // Elements for showing slider values
    const fontSizeValueEl = document.getElementById("font-size-value");
    const lineHeightValueEl = document.getElementById("line-height-value");

    // Apply font family
    function updateFontVariables(font) {
        const family = (fontMap[font] || "khmer") + ", sans-serif";
        [
            "--tblr-font-sans-serif",
            "--tblr-body-font-family",
            "--tblr-btn-font-family",
            "--tblr-form-font-family",
            "--tblr-table-font-family",
            "--tblr-card-font-family",
            "--tblr-dropdown-font-family",
            "--tblr-nav-font-family",
            "--tblr-breadcrumb-font-family",
        ].forEach((v) => document.documentElement.style.setProperty(v, family));
    }

    // Apply all settings
    function applySettings() {
        for (const key in themeConfig) {
            let value =
                localStorage.getItem("tabler-" + key) || themeConfig[key];

            switch (key) {
                case "theme-font":
                    updateFontVariables(value);
                    break;
                case "theme-font-size":
                    document.documentElement.style.setProperty(
                        "--tblr-font-size",
                        value
                    );
                    break;
                case "theme-line-height":
                    document.documentElement.style.setProperty(
                        "--tblr-line-height",
                        value
                    );
                    break;
                case "theme-sidebar-width":
                    document.documentElement.style.setProperty(
                        "--tblr-sidebar-width",
                        value
                    );
                    break;
            }

            document.documentElement.setAttribute("data-bs-" + key, value);
        }
        updateValueDisplays();
    }

    // Update slider value display
    function updateValueDisplays() {
        const fontSize = parseInt(
            getComputedStyle(document.documentElement).getPropertyValue(
                "--tblr-font-size"
            )
        );
        const lineHeight = parseFloat(
            getComputedStyle(document.documentElement).getPropertyValue(
                "--tblr-line-height"
            )
        );

        if (fontSizeValueEl) fontSizeValueEl.textContent = fontSize + "px";
        if (lineHeightValueEl) lineHeightValueEl.textContent = lineHeight;
    }

    // Initialize slider positions from saved settings
    function checkItems() {
        for (const key in themeConfig) {
            const value =
                localStorage.getItem("tabler-" + key) || themeConfig[key];
            form.querySelectorAll(`[name="${key}"]`).forEach((el) => {
                if (el.type === "radio") el.checked = el.value === value;
                if (el.type === "range") {
                    if (key === "theme-font-size") el.value = parseInt(value);
                    else if (key === "theme-line-height")
                        el.value = parseFloat(value);
                }
            });
        }
    }

    applySettings();
    checkItems();

    // Handle input changes
    form.addEventListener("input", (e) => {
        const name = e.target.name;
        if (!themeConfig.hasOwnProperty(name)) return;

        let value = e.target.value;

        switch (name) {
            case "theme-font":
                updateFontVariables(value);
                break;
            case "theme-font-size":
                value += "px";
                document.documentElement.style.setProperty(
                    "--tblr-font-size",
                    value
                );
                if (fontSizeValueEl) fontSizeValueEl.textContent = value;
                break;
            case "theme-line-height":
                document.documentElement.style.setProperty(
                    "--tblr-line-height",
                    value
                );
                if (lineHeightValueEl) lineHeightValueEl.textContent = value;
                break;
        }

        document.documentElement.setAttribute("data-bs-" + name, value);
        localStorage.setItem("tabler-" + name, value);
    });

    // Reset button
    resetButton.addEventListener("click", () => {
        for (const key in themeConfig) {
            document.documentElement.removeAttribute("data-bs-" + key);
            localStorage.removeItem("tabler-" + key);

            const defaultValue = themeConfig[key];

            switch (key) {
                case "theme-font":
                    updateFontVariables(defaultValue);
                    break;
                case "theme-font-size":
                    document.documentElement.style.setProperty(
                        "--tblr-font-size",
                        defaultValue
                    );
                    break;
                case "theme-line-height":
                    document.documentElement.style.setProperty(
                        "--tblr-line-height",
                        defaultValue
                    );
                    break;
                case "theme-sidebar-width":
                    document.documentElement.style.setProperty(
                        "--tblr-sidebar-width",
                        defaultValue
                    );
                    break;
            }
        }

        checkItems();
        updateValueDisplays();
    });
});
