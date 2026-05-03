<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.getElementById('subclientsWrapper');
        const template = document.getElementById('subclientTemplate');
        const addButton = document.getElementById('addSubclientBtn');
        const emptyState = document.getElementById('subclientEmptyState');
        const deletedWrapper = document.getElementById('deletedSubclientsWrapper');

        function syncEmptyState() {
            if (!emptyState || !wrapper) {
                return;
            }

            emptyState.classList.toggle(
                'd-none',
                wrapper.querySelectorAll('.subclient-item').length > 0
            );
        }

        function reindexSubclients() {
            if (!wrapper) {
                return;
            }

            wrapper.querySelectorAll('.subclient-item').forEach(function(item, index) {
                item.querySelectorAll('[data-name]').forEach(function(input) {
                    input.name = `subclients[${index}][${input.dataset.name}]`;
                });

                item.querySelectorAll('[data-name="date_of_birth"]').forEach(function(input) {
                    input.classList.add('datepicker', 'subclient-datepicker');
                    input.setAttribute('autocomplete', 'off');
                });
            });

            syncEmptyState();

            if (window.initDatepickers) {
                window.initDatepickers(wrapper);
            }
        }

        if (addButton && template && wrapper) {
            addButton.addEventListener('click', function() {
                const clone = template.content.cloneNode(true);
                wrapper.appendChild(clone);
                reindexSubclients();
            });
        }

        document.addEventListener('click', function(event) {
            const removeButton = event.target.closest('.remove-subclient');

            if (!removeButton) {
                return;
            }

            const existingId = removeButton.dataset.existingId;

            if (existingId && deletedWrapper) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_subclients[]';
                input.value = existingId;
                deletedWrapper.appendChild(input);
            }

            removeButton.closest('.subclient-item')?.remove();
            reindexSubclients();
        });

        reindexSubclients();
    });
</script>
