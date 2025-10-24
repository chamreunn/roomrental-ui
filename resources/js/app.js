import AOS from "aos";
import TomSelect from "@tabler/core/dist/libs/tom-select/dist/js/tom-select.base.min.js";
import Litepicker from "litepicker";
import "litepicker/dist/css/litepicker.css";

// Tabler Core
import "@tabler/core/dist/js/tabler.min.js";
import "@tabler/core/dist/js/tabler-theme.min.js";
import "@tabler/core/dist/css/tabler-flags.min.css";
import "@tabler/core/dist/css/demo.min.css";
import "@tabler/core/dist/libs/litepicker/dist/css/litepicker.css";
import "@tabler/core/dist/css/tabler-vendors.min.css";

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

// ✅ Initialize Litepicker correctly
document.addEventListener("DOMContentLoaded", function () {
    const leaveDateInputs = document.querySelectorAll(".leave-picker");
    leaveDateInputs.forEach((input) => {
        new Litepicker({
            element: input,
            singleMode: true,
            format: "DD-MM-YYYY",
            lang: "kh", // correct for Litepicker
            minDate: new Date(), // Disables past dates by setting the min date to today
            buttonText: {
                previousMonth: `<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="15 6 9 12 15 18" /></svg>`,
                nextMonth: `<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 6 15 12 9 18" /></svg>`,
            },
        });
    });

    // Initialize Flatpickr for elements with the 'time-picker' class
    const timeInputs = document.querySelectorAll(".time-picker");
    timeInputs.forEach((timeInput) => {
        flatpickr(timeInput, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: false,
            defaultHour: 12,
            defaultMinute: 0,
            locale: flatpickr.l10ns.km,
            allowInput: true, // Allow users to type directly into the input field
            minuteIncrement: 1, // Set minute intervals to 1 (instead of 5)
        });
    });

    // Check if elements with class 'date-picker' exist before initializing Litepicker
    const dobInput = document.querySelectorAll(".datepicker");

    dobInput.forEach((input) => {
        new Litepicker({
            element: input,
            singleMode: true,
            format: "DD-MM-YYYY",
            lang: "kh",
            numberOfMonths: 1,
            numberOfColumns: 1,
            dropdowns: {
                minYear: 1950,
                maxYear: new Date().getFullYear(),
                months: true,
                years: true,
            },
            autoApply: true,
            mobileFriendly: true,
            maxDate: new Date(),
            buttonText: {
                previousMonth: `<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 6 9 12 15 18"/></svg>`,
                nextMonth: `<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>`,
            },
            setup: (picker) => {
                picker.on("selected", (date) => {
                    console.log("Selected DOB:", date.format("DD-MM-YYYY"));
                });
            },
        });
    });
});
