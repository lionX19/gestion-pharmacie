let deleteId = null;

        function showDeleteConfirm(id) {
            deleteId = id;
            const dialog = document.getElementById('confirmDialog');
            dialog.classList.remove('hidden');
        }

        function confirmAction() {
            if (deleteId !== null) {
                window.location.href = `delete.php?id=${deleteId}`;
            }
            cancelAction();
        }

        function cancelAction() {
            deleteId = null;
            const dialog = document.getElementById('confirmDialog');
            dialog.classList.add('hidden');
        }

        // Fermer avec la touche Échap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cancelAction();
            }
        });