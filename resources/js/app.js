import AOS from "aos";
import ApexCharts from "apexcharts";
import "@tabler/core/dist/css/tabler-vendors.min.css";
import TomSelect from "@tabler/core/dist/libs/tom-select/dist/js/tom-select.base.min.js";
import Litepicker from "litepicker";
import "litepicker/dist/css/litepicker.css";

// Tabler Core
import "@tabler/core/dist/js/tabler.min.js";
import "@tabler/core/dist/js/tabler-theme.min.js";
import "@tabler/core/dist/css/tabler-flags.min.css";
import "@tabler/core/dist/css/demo.min.css";
import "@tabler/core/dist/libs/litepicker/dist/css/litepicker.css";

import flatpickr from "flatpickr";
import monthSelectPlugin from "flatpickr/dist/plugins/monthSelect/index.js";

import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/plugins/monthSelect/style.css";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/plugins/monthSelect/style.css";
// Optional: Khmer locale
import { Khmer } from "flatpickr/dist/l10n/km.js";

// for flatpickr
document.addEventListener("DOMContentLoaded", function () {
    const locale = window.appLocale || "en";
    const months = window.monthsTranslation[locale] || [];

    // Month Picker
    flatpickr(".monthpicker", {
        plugins: [
            new monthSelectPlugin({
                shorthand: false,
                dateFormat: "Y-m",
                altFormat: "Y-m",
            }),
        ],
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ["អា", "ច", "អ", "ពុ", "ព្រ", "សុ", "ស"],
                longhand: [
                    "អាទិត្យ",
                    "ច័ន្ទ",
                    "អង្គារ",
                    "ពុធ",
                    "ព្រហស្បតិ៍",
                    "សុក្រ",
                    "សៅរ៍",
                ],
            },
            months: {
                shorthand: months,
                longhand: months,
            },
        },
        allowInput: true,
        wrap: false,
    });

    // DOB Picker
    flatpickr(".dobpicker", {
        dateFormat: "d-m-Y",
        altInput: true,
        altFormat: "d M Y",
        locale:
            locale === "km"
                ? {
                      firstDayOfWeek: 1,
                      weekdays: {
                          shorthand: ["អា", "ច", "អ", "ពុ", "ព្រ", "សុ", "ស"],
                          longhand: [
                              "អាទិត្យ",
                              "ច័ន្ទ",
                              "អង្គារ",
                              "ពុធ",
                              "ព្រហស្បតិ៍",
                              "សុក្រ",
                              "សៅរ៍",
                          ],
                      },
                      months: { shorthand: months, longhand: months },
                  }
                : "en",
        maxDate: "today",
        allowInput: true,
        wrap: false,
    });
});

// Initialize AOS
document.addEventListener("DOMContentLoaded", () => {
    AOS.init({
        duration: 600,
        easing: "ease-out",
        once: true,
    });
});

// Initialize TomSelect
document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll(".tom-select");

    selects.forEach(function (el) {
        if (el.tomSelectInstance) return;

        const parentContainer = el.closest(".modal, .offcanvas");
        const dropdownParent = parentContainer ? parentContainer : "body";

        el.tomSelectInstance = new TomSelect(el, {
            copyClassesToDropdown: false,
            dropdownParent: dropdownParent,
            dropdownClass: "dropdown-menu ts-dropdown",
            optionClass: "dropdown-item",
            controlInput: "<input>",
            create: false,
            render: {
                item: (data, escape) => `
                    <div class="d-flex align-items-center">
                        <span class="dropdown-item-indicator">${
                            data.customProperties || ""
                        }</span>
                        <span>${escape(data.text)}</span>
                    </div>
                `,
                option: (data, escape) => `
                    <div class="d-flex align-items-center">
                        <span class="dropdown-item-indicator">${
                            data.customProperties || ""
                        }</span>
                        <span>${escape(data.text)}</span>
                    </div>
                `,
            },
        });
    });
});
