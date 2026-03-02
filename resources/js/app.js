import "./settings";

// ======================
// CSS IMPORTS (REQUIRED)
// ======================
import "flatpickr/dist/flatpickr.css";
import "aos/dist/aos.css";

// ======================
// JS LIBRARIES
// ======================
import AOS from "aos";
import ApexCharts from "apexcharts";
import TomSelect from "@tabler/core/dist/libs/tom-select/dist/js/tom-select.complete.js";
import flatpickr from "flatpickr";
import Litepicker from "@tabler/core/dist/libs/litepicker/dist/js/main.js";
import "@tabler/core/dist/libs/litepicker/dist/css/litepicker.css";

// Tabler Core
import "@tabler/core/dist/js/tabler.js";

// Expose globals if needed
window.AOS = AOS;
window.ApexCharts = ApexCharts;

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
        altFormat: "d-M-Y",
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

    // date Picker
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "Y-m-d",
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
        allowInput: false,
        wrap: false,
    });
});

// Initialize AOS
document.addEventListener("DOMContentLoaded", () => {
    AOS.init({
        duration: 600,
        easing: "ease-out",
        once: false,
        offset: 120,
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
