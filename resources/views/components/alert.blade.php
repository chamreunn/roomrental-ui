@if (session('success') || session('error') || $errors->any())
    <div class="alert-wrapper top-0 start-50 translate-middle-x mt-3" style="z-index:1080; position:fixed;">
        @php
            $type = session('success') ? 'success' : (session('error') ? 'danger' : 'danger');
            $message = session('success') ?? session('error') ?? $errors->first();
        @endphp

        <div class="alert-container">
            <div class="alert text-blue-fg bg-{{ $type }} alert-dismissible d-flex align-items-center shadow-lg" role="alert">
                <div class="m-0">
                    <x-icon :name="$type === 'success' ? 'circle-check' : 'circle-x'" />
                </div>
                <div class="mx-0 ms-2">{{ $message }}</div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="close"></button>
            </div>
        </div>
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
        opacity: 0;
        transform: translateY(-20px);
        animation: slideDown 0.6s forwards;
    }

    @keyframes slideDown {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const alert = document.querySelector(".alert-container");
        if(alert) {
            // Auto fade-out after 4s
            setTimeout(() => {
                alert.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                alert.style.opacity = "0";
                alert.style.transform = "translateY(-20px)";
                setTimeout(() => alert.remove(), 600);
            }, 4000);
        }
    });
</script>
