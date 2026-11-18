import Sortable from 'sortablejs';

/**
 * Komponen Alpine untuk mengurutkan lesson di halaman detail course (admin).
 * - Drag & drop lewat handle (mouse & sentuh) memakai SortableJS.
 * - Tombol naik/turun sebagai alternatif yang bisa dipakai lewat keyboard.
 * Setiap perubahan langsung disimpan ke server (PATCH, JSON).
 */
export default ({ url }) => ({
    state: 'idle', // idle | saving | saved | error
    message: '',
    snapshot: [],

    init() {
        this.snapshot = this.ids();

        Sortable.create(this.$refs.list, {
            handle: '[data-handle]',
            animation: 150,
            ghostClass: 'opacity-40',
            // Pakai pointer events, bukan HTML5 drag native: perilaku sama di mouse & layar sentuh.
            forceFallback: true,
            fallbackClass: 'shadow-lg',
            onEnd: (event) => {
                if (event.oldIndex !== event.newIndex) this.save();
            },
        });
    },

    ids() {
        return [...this.$refs.list.children].map((el) => Number(el.dataset.id));
    },

    move(button, direction) {
        const item = button.closest('[data-id]');
        const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;
        if (!sibling) return;

        direction < 0 ? sibling.before(item) : sibling.after(item);
        button.focus();
        this.save();
    },

    renumber() {
        [...this.$refs.list.children].forEach((el, index) => {
            el.querySelector('[data-position]').textContent = index + 1;
        });
    },

    restore(ids) {
        const byId = Object.fromEntries([...this.$refs.list.children].map((el) => [el.dataset.id, el]));
        ids.forEach((id) => this.$refs.list.appendChild(byId[id]));
        this.renumber();
    },

    async save() {
        this.renumber();
        this.state = 'saving';
        this.message = 'Menyimpan urutan…';

        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ lessons: this.ids() }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.errors?.lessons?.[0] ?? data.message ?? 'Gagal menyimpan urutan.');

            this.snapshot = this.ids();
            this.state = 'saved';
            this.message = data.message ?? 'Urutan disimpan.';
        } catch (error) {
            // Kembalikan tampilan ke urutan terakhir yang tersimpan agar tidak menyesatkan.
            this.restore(this.snapshot);
            this.state = 'error';
            this.message = error.message;
        }
    },
});
