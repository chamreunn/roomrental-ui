document.addEventListener("DOMContentLoaded", () => {
    const themeConfig = {
        theme: "light",
        "theme-base": "gray",
        "theme-primary": "blue",
        "theme-radius": "1",
        "theme-font": "khmer"
    };

    const form = document.getElementById("settings");
    const resetButton = document.getElementById("reset-changes");

    // Apply saved theme on load
    for (const key in themeConfig) {
        const value = localStorage.getItem("tabler-" + key) || themeConfig[key];
        document.documentElement.setAttribute("data-bs-" + key, value);
    }

    function checkItems() {
        for (const key in themeConfig) {
            const value = localStorage.getItem("tabler-" + key) || themeConfig[key];
            form.querySelectorAll(`[name="${key}"]`).forEach(r => r.checked = r.value === value);
        }
    }
    checkItems();

    // Change handler
    form.addEventListener("change", e => {
        const target = e.target;
        const name = target.name;
        const value = target.value;

        if (themeConfig.hasOwnProperty(name)) {
            document.documentElement.setAttribute("data-bs-" + name, value);
            localStorage.setItem("tabler-" + name, value);
        }
    });

    // Reset button
    resetButton.addEventListener("click", () => {
        for (const key in themeConfig) {
            document.documentElement.removeAttribute("data-bs-" + key);
            localStorage.removeItem("tabler-" + key);
        }
        checkItems();
    });
});
