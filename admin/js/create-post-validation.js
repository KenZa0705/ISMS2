document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn'); 

    const isUpdate = form.dataset.action === 'update';

    if (isUpdate) {
        submitBtn.textContent = 'Update Announcement';
        submitBtn.setAttribute('data-action', 'update');
    } else {
        submitBtn.textContent = 'Post Announcement';
        submitBtn.setAttribute('data-action', 'post');
    }

    // Error Modal Function
    function showErrorModal(message) {
        const modalHtml = `
        <div class="modal error-modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content text-center">
                    <div class="modal-body p-4">
                        <div class="mb-2">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="modal-title mb-1" id="errorModalLabel">Error</h6>
                        <p class="small mb-3">${message}</p>
                        <button type="button" class="btn btn-danger btn-sm w-100" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>`;
        // Remove existing modal if any
        const existingModal = document.getElementById('errorModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }

    function showSuccessModal(isUpdate) {
        const redirectUrl = isUpdate ? '../admin.php?status=update' : '../admin.php?status=success';
        window.location.href = redirectUrl;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form elements
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const imageInput = document.getElementById('image');
        const tagsSelected = validateTags();

        // Validate title
        if (!title) {
            showErrorModal('Please enter a title for the announcement');
            return;
        }

        // Validate description
        if (!description) {
            showErrorModal('Please enter a description for the announcement');
            return;
        }

        // Validate image only if one is selected
        if (imageInput.files && imageInput.files[0]) {
            // Validate image type
            const file = imageInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showErrorModal('Please select a valid image file (JPG, PNG, or GIF)');
                return;
            }

            // Validate image size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                showErrorModal('Image size should not exceed 5MB');
                return;
            }
        }

        // Validate tags
        if (!tagsSelected) {
            showErrorModal('Please select at least one tag for the announcement');
            return;
        }

        // If all validations pass
        showLoadingState();
        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
    
            if (response.ok) {
                const isUpdate = this.dataset.action === 'update';
                showSuccessModal(isUpdate);
            } else {
                showErrorModal('An error occurred while posting the announcement');
            }
        } catch (error) {
            showErrorModal('An error occurred while posting the announcement');
        }
    });

    function validateTags() {
        const yearLevels = document.querySelectorAll('input[name="year_level[]"]:checked');
        const departments = document.querySelectorAll('input[name="department[]"]:checked');
        const courses = document.querySelectorAll('input[name="course[]"]:checked');

        return yearLevels.length > 0 || departments.length > 0 || courses.length > 0;
    }

    function showLoadingState() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Posting...';
    }
});