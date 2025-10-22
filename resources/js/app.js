// Tabler Core (includes Bootstrap)
import "@tabler/core/dist/js/tabler.min.js";
import "@tabler/core/dist/css/tabler.min.css";
import "@tabler/core/dist/css/tabler-marketing.min.css";
import "@tabler/core/dist/css/tabler-flags.min.css";
import "@tabler/core/dist/css/tabler-vendors.min.css";
// Optional theme toggler
import "@tabler/core/dist/js/tabler-theme.min.js";
// AOS (Animate On Scroll)
import AOS from "aos";
import "aos/dist/aos.css";

// Initialize AOS
document.addEventListener("DOMContentLoaded", () => {
    AOS.init({
        duration: 600,
        easing: "ease-out",
        once: true, // animate only once
    });
});
