const documentInput = document.querySelector('[data-outgoing-letter-document]');

if (documentInput) {
    const previewArea = document.querySelector('[data-outgoing-document-preview-area]');
    const previewContent = document.querySelector('[data-outgoing-document-preview-content]');
    const fileName = document.querySelector('[data-outgoing-document-name]');
    const fileType = document.querySelector('[data-outgoing-document-type]');
    const fileSize = document.querySelector('[data-outgoing-document-size]');
    const errorMessage = document.querySelector('[data-outgoing-document-error]');
    const allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    const maximumSize = 5 * 1024 * 1024;
    let objectUrl = null;

    const revokeObjectUrl = () => {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    };

    const clearPreview = () => {
        revokeObjectUrl();
        previewContent?.replaceChildren();
        if (fileName) fileName.textContent = '-';
        if (fileType) fileType.textContent = '-';
        if (fileSize) fileSize.textContent = '-';
        previewArea?.classList.add('d-none');
    };

    const showError = (message) => {
        clearPreview();

        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('d-none');
        }

        documentInput.value = '';
    };

    const formatSize = (bytes) => {
        if (bytes >= 1024 * 1024) {
            return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
        }

        return `${(bytes / 1024).toFixed(1)} KB`;
    };

    documentInput.addEventListener('change', () => {
        errorMessage?.classList.add('d-none');

        const file = documentInput.files?.[0];

        if (!file) {
            clearPreview();
            return;
        }

        const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

        if (!allowedMimeTypes.includes(file.type) || !allowedExtensions.includes(extension)) {
            showError('Format dokumen tidak valid. Pilih file PDF, JPG, JPEG, atau PNG.');
            return;
        }

        if (file.size > maximumSize) {
            showError('Ukuran dokumen melebihi batas maksimum 5 MB.');
            return;
        }

        revokeObjectUrl();
        objectUrl = URL.createObjectURL(file);

        if (fileName) fileName.textContent = file.name;
        if (fileType) fileType.textContent = file.type;
        if (fileSize) fileSize.textContent = formatSize(file.size);

        if (previewContent) {
            previewContent.replaceChildren();

            if (file.type === 'application/pdf') {
                const frame = document.createElement('iframe');
                frame.className = 'rs-document-frame';
                frame.src = objectUrl;
                frame.title = `Preview ${file.name}`;
                previewContent.append(frame);
            } else {
                const image = document.createElement('img');
                image.className = 'rs-document-image';
                image.src = objectUrl;
                image.alt = `Preview ${file.name}`;
                previewContent.append(image);
            }
        }

        previewArea?.classList.remove('d-none');
    });

    window.addEventListener('pagehide', revokeObjectUrl);
}
