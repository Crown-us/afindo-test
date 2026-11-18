import Sortable from 'sortablejs';

/**
 * Drag & drop urutan lesson (halaman detail course, admin).
 * Setelah lesson dilepas, urutan id dikirim ke server.
 */
export default (url) => ({
    message: '',

    init() {
        Sortable.create(this.$refs.list, {
            handle: '[data-handle]',
            animation: 150,
            forceFallback: true, // pakai pointer events agar sama di mouse & layar sentuh
            onEnd: () => this.save(),
        });
    },

    async save() {
        const items = [...this.$refs.list.children];
        items.forEach((el, i) => (el.querySelector('[data-position]').textContent = i + 1));

        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ lessons: items.map((el) => el.dataset.id) }),
        });

        if (!response.ok) {
            alert('Gagal menyimpan urutan. Halaman akan dimuat ulang.');
            return location.reload();
        }

        this.message = 'Urutan lesson disimpan.';
    },
});
