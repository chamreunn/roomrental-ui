document.addEventListener("DOMContentLoaded", () => {
    /* =====================================================
     * DEFAULT THEME CONFIG
     * ===================================================== */
    const themeConfig = {
        theme: "light",
        "theme-base": "gray",
        "theme-primary": "blue",
        "theme-radius": "1",
        "theme-font": "khmer",
        "theme-font-size": "14px",
        "theme-line-height": "1.2",
        "theme-sidebar-width": "15%",
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
    const fontSizeValueEl = document.getElementById("font-size-value");
    const lineHeightValueEl = document.getElementById("line-height-value");

    /* =====================================================
     * READ THEME FROM URL (?theme=dark|light)
     * ===================================================== */
    const params = new URLSearchParams(window.location.search);
    const urlTheme = params.get("theme");

    if (urlTheme === "dark" || urlTheme === "light") {
        localStorage.setItem("tabler-theme", urlTheme);
        document.documentElement.setAttribute("data-bs-theme", urlTheme);

        // Clean URL (recommended)
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    }

    /* =====================================================
     * FONT HANDLER
     * ===================================================== */
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
        ].forEach((v) => {
            document.documentElement.style.setProperty(v, family);
        });
    }

    /* =====================================================
     * APPLY SETTINGS
     * ===================================================== */
    function applySettings() {
        for (const key in themeConfig) {
            const value =
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

    /* =====================================================
     * UPDATE SLIDER LABELS
     * ===================================================== */
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

    /* =====================================================
     * INIT FORM VALUES
     * ===================================================== */
    function checkItems() {
        if (!form) return;

        for (const key in themeConfig) {
            const value =
                localStorage.getItem("tabler-" + key) || themeConfig[key];

            form.querySelectorAll(`[name="${key}"]`).forEach((el) => {
                if (el.type === "radio") el.checked = el.value === value;
                if (el.type === "range") {
                    if (key === "theme-font-size") el.value = parseInt(value);
                    if (key === "theme-line-height")
                        el.value = parseFloat(value);
                }
            });
        }
    }

    applySettings();
    checkItems();

    /* =====================================================
     * HANDLE FORM INPUT
     * ===================================================== */
    if (form) {
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
                    if (lineHeightValueEl)
                        lineHeightValueEl.textContent = value;
                    break;
            }

            document.documentElement.setAttribute("data-bs-" + name, value);
            localStorage.setItem("tabler-" + name, value);
        });
    }

    /* =====================================================
     * RESET BUTTON
     * ===================================================== */
    if (resetButton) {
        resetButton.addEventListener("click", () => {
            for (const key in themeConfig) {
                localStorage.removeItem("tabler-" + key);
                document.documentElement.removeAttribute("data-bs-" + key);

                const def = themeConfig[key];

                switch (key) {
                    case "theme-font":
                        updateFontVariables(def);
                        break;

                    case "theme-font-size":
                        document.documentElement.style.setProperty(
                            "--tblr-font-size",
                            def
                        );
                        break;

                    case "theme-line-height":
                        document.documentElement.style.setProperty(
                            "--tblr-line-height",
                            def
                        );
                        break;

                    case "theme-sidebar-width":
                        document.documentElement.style.setProperty(
                            "--tblr-sidebar-width",
                            def
                        );
                        break;
                }
            }

            applySettings();
            checkItems();
        });
    }
});
