@if (session('success') || session('error') || $errors->any())
    <div class="alert-wrapper top-0 start-50 translate-middle-x mt-3" style="z-index:1080; position:fixed;">

        {{-- Session success/error messages --}}
        @foreach (['success' => 'success', 'error' => 'danger'] as $type => $color)
            @if (session($type))
                <div class="alert-container d-flex align-items-center mb-2" data-aos="fade-down" data-aos-delay="100">
                    <div class="alert text-blue-fg bg-{{ $color }} alert-dismissible d-flex align-items-center shadow-lg"
                        role="alert">
                        <div class="m-0">
                            <x-icon :name="$color === 'success' ? 'circle-check' : 'circle-x'" />
                        </div>
                        <div class="mx-0 ms-2">{{ session($type) }}</div>
                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"
                            aria-label="close"></button>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Validation errors --}}
        @foreach ($errors->all() as $error)
            <div class="alert-container d-flex align-items-center mb-2" data-aos="fade-down" data-aos-delay="100">
                <div class="alert text-blue-fg bg-danger alert-dismissible d-flex align-items-center shadow-lg"
                    role="alert">
                    <div class="m-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <div class="mx-0 ms-2">{{ $error }}</div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"
                        aria-label="close"></button>
                </div>
            </div>
        @endforeach

    </div>
@endif

<style>
    .alert-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .alert-container {
        width: auto;
        min-width: 300px;
        max-width: 600px;
        text-align: start;
    }
</style>

{{-- AOS JS + CSS (for npm import, skip if you import via JS build) --}}
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize AOS animations
        AOS.init({
            duration: 600,
            easing: 'ease-out',
            once: true
        });

        // Auto fade-out after 4s + stagger
        document.querySelectorAll(".alert-container").forEach((alert, index) => {
            setTimeout(() => {
                alert.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                alert.style.opacity = "0";
                alert.style.transform = "translateY(-20px)";
                setTimeout(() => alert.remove(), 600);
            }, 4000 + index * 500);
        });
    });
</script>
