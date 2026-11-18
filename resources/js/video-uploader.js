/**
 * Upload video lesson lewat XHR (bukan fetch) karena XHR punya event progress upload.
 * Setelah berhasil, halaman dimuat ulang agar status & preview video terbaru tampil.
 */
export default (url, maxSizeKb) => ({
    file: null,
    progress: 0,
    uploading: false,
    error: '',

    pick(event) {
        this.error = '';
        this.file = event.target.files[0] ?? null;

        if (this.file && this.file.size > maxSizeKb * 1024) {
            this.error = `Ukuran file melebihi batas ${Math.floor(maxSizeKb / 1024)} MB.`;
            this.file = null;
            event.target.value = '';
        }
    },

    upload() {
        const data = new FormData();
        data.append('video', this.file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.responseType = 'json';
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) this.progress = Math.round((e.loaded / e.total) * 100);
        };
        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) return location.reload();

            if (xhr.status === 413) return this.fail('File terlalu besar untuk server.');

            const body = xhr.response ?? {};
            this.fail(body.errors?.video?.[0] ?? body.message);
        };
        xhr.onerror = () => this.fail('Koneksi terputus saat mengunggah.');

        this.uploading = true;
        this.progress = 0;
        this.error = '';
        xhr.send(data);
    },

    fail(message) {
        this.uploading = false;
        this.error = message || 'Upload gagal. Silakan coba lagi.';
    },
});
