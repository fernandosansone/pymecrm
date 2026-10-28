// resources/js/pymecrm.js

// --- Money input (formato AR: 1.234,56 -> raw: 1234.56)
window.moneyInput = function moneyInput(initialRaw) {
  return {
    display: '',
    raw: (initialRaw || '').toString(),

    init() {
      if (!this.raw) return;

      // Normalizamos posibles formatos: "1234.56" o "1.234,56"
      const cleaned = this.raw.replace(/\./g, '').replace(',', '.'); // "1.234,56" -> "1234.56"
      const parts = cleaned.split('.');
      const int = (parts[0] || '').replace(/[^\d]/g, '');
      const dec = (parts[1] || '').slice(0, 2).replace(/[^\d]/g, '');

      const withDots = int.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      this.display = dec ? `${withDots},${dec}` : withDots;
      this.raw = dec ? `${int}.${dec}` : int;
    },

    format() {
      let v = (this.display || '').toString().replace(/[^\d,]/g, '');

      const parts = v.split(',');
      let int = (parts[0] || '').replace(/[^\d]/g, '');
      let dec = (parts[1] || '').replace(/[^\d]/g, '').slice(0, 2);

      int = int.replace(/^0+(?=\d)/, '');
      const withDots = int.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

      this.display = dec.length ? `${withDots},${dec}` : withDots;
      this.raw = dec.length ? `${int}.${dec}` : int;
    }
  };
};

// --- Quick create contacto dentro de oportunidad
window.contactQuickCreate = function contactQuickCreate() {
  return {
    open: false,
    error: '',
    form: { first_name: '', last_name: '', company_name: '', email_1: '', phone_1: '' },

    async submit() {
      this.error = '';

      try {
        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrf = tokenEl ? tokenEl.getAttribute('content') : null;
        if (!csrf) {
          this.error = 'No se encontró el token CSRF.';
          return;
        }

        const endpoint = window.PYMECRM?.routes?.contactsQuickStore;
        if (!endpoint) {
          this.error = 'No está configurada la ruta de creación rápida.';
          return;
        }

        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.form)
        });

        if (!res.ok) {
          const payload = await res.json().catch(() => ({}));
          this.error = payload?.message ?? 'No se pudo crear el contacto.';
          return;
        }

        const data = await res.json();

        // Esperamos que el backend devuelva: { id, label }
        const opt = document.createElement('option');
        opt.value = data.id;
        opt.textContent = data.label;
        opt.selected = true;

        this.$refs.contactSelect.appendChild(opt);

        this.open = false;
        this.form = { first_name: '', last_name: '', company_name: '', email_1: '', phone_1: '' };
      } catch (e) {
        this.error = 'Error de red al crear el contacto.';
      }
    }
  };
};
