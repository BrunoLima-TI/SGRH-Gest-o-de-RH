/* ==========================================================
   SGRH — Interações (vanilla JS, sem dependências)
   ========================================================== */
(function () {
  'use strict';

  const $ = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  /* -------------------- Sidebar -------------------- */
  function initSidebar() {
    const overlay = $('[data-sidebar-overlay]');
    const toggle = $$('[data-sidebar-toggle]');

    const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

    function open() {
      document.body.classList.add('sidebar-open');
      if (overlay) {
        overlay.hidden = false;
        requestAnimationFrame(() => overlay.classList.add('is-visible'));
      }
      toggle.forEach(b => b.setAttribute('aria-expanded', 'true'));
    }

    function close() {
      document.body.classList.remove('sidebar-open');
      if (overlay) {
        overlay.classList.remove('is-visible');
        setTimeout(() => { overlay.hidden = true; }, 200);
      }
      toggle.forEach(b => b.setAttribute('aria-expanded', 'false'));
    }

    toggle.forEach(btn => {
      btn.addEventListener('click', () => {
        if (isMobile()) {
          document.body.classList.contains('sidebar-open') ? close() : open();
        } else {
          document.body.classList.toggle('sidebar-collapsed');
        }
      });
    });

    if (overlay) overlay.addEventListener('click', close);

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) close();
    });

    window.addEventListener('resize', () => {
      if (!isMobile()) close();
    });
  }

  /* -------------------- Dropdowns -------------------- */
  function initDropdowns() {
    $$('[data-dropdown]').forEach(drop => {
      const trigger = $('[data-dropdown-toggle]', drop);
      const menu = $('[data-dropdown-menu]', drop);
      if (!trigger || !menu) return;

      trigger.addEventListener('click', e => {
        e.stopPropagation();
        const aberto = drop.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', aberto ? 'true' : 'false');
      });

      document.addEventListener('click', e => {
        if (!drop.contains(e.target)) {
          drop.classList.remove('is-open');
          trigger.setAttribute('aria-expanded', 'false');
        }
      });

      menu.addEventListener('keydown', e => {
        const itens = $$('a, button', menu);
        const idx = itens.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); itens[(idx + 1) % itens.length]?.focus(); }
        if (e.key === 'ArrowUp') { e.preventDefault(); itens[(idx - 1 + itens.length) % itens.length]?.focus(); }
        if (e.key === 'Escape') { drop.classList.remove('is-open'); trigger.focus(); }
      });
    });
  }

  /* -------------------- Toasts -------------------- */
  const ICONES = {
    success: '<path d="m20 6-11 11-5-5"/>',
    danger: '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
    warning: '<path d="m10.29 3.86-8.47 14.14A2 2 0 0 0 3.53 21h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4M12 17h.01"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>'
  };

  window.sgrhToast = function (mensagem, tipo) {
    const root = $('#toast-root');
    if (!root) return;
    tipo = tipo || 'success';
    const el = document.createElement('div');
    el.className = 'toast';
    el.setAttribute('role', 'status');
    el.innerHTML =
      '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="' +
      (tipo === 'success' ? '#34D399' : tipo === 'danger' ? '#F87171' : tipo === 'warning' ? '#FBBF24' : '#60A5FA') +
      '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + (ICONES[tipo] || ICONES.info) + '</svg>' +
      '<span>' + mensagem + '</span>' +
      '<button class="toast__close" aria-label="Fechar">&times;</button>';
    root.appendChild(el);
    const fechar = () => { el.style.opacity = '0'; setTimeout(() => el.remove(), 200); };
    el.querySelector('.toast__close').addEventListener('click', fechar);
    setTimeout(fechar, 4000);
    while (root.children.length > 3) root.firstChild.remove();
  };

  /* -------------------- Alerts inline -------------------- */
  function initAlerts() {
    document.addEventListener('click', e => {
      const close = e.target.closest('[data-alert-close]');
      if (close) {
        const alert = close.closest('[data-alert]');
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 200);
      }
      const trigger = e.target.closest('[data-toast]');
      if (trigger) {
        window.sgrhToast(trigger.dataset.toast, 'info');
      }
    });
  }

  /* -------------------- Modal -------------------- */
  window.sgrhModal = function (opcoes) {
    opcoes = opcoes || {};
    const root = $('#modal-root');
    if (!root) return;

    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.innerHTML =
      '<div class="modal ' + (opcoes.tamanho || '') + '" role="dialog" aria-modal="true" aria-labelledby="modal-title">' +
        '<div class="modal__head">' +
          '<div><h3 class="card__title" id="modal-title">' + (opcoes.titulo || '') + '</h3>' +
          (opcoes.subtitulo ? '<p class="card__subtitle">' + opcoes.subtitulo + '</p>' : '') + '</div>' +
          '<button class="icon-btn" data-modal-close aria-label="Fechar">&times;</button>' +
        '</div>' +
        '<div class="modal__body">' + (opcoes.conteudo || '') + '</div>' +
        (opcoes.rodape ? '<div class="modal__foot">' + opcoes.rodape + '</div>' : '') +
      '</div>';

    root.appendChild(backdrop);
    document.body.style.overflow = 'hidden';

    const fechar = () => {
      backdrop.remove();
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onTecla);
    };
    const onTecla = e => {
      if (e.key === 'Escape') fechar();
      if (e.key === 'Tab') {
        const focaveis = $$('a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])', backdrop);
        if (!focaveis.length) return;
        const primeiro = focaveis[0], ultimo = focaveis[focaveis.length - 1];
        if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
        else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
      }
    };
    document.addEventListener('keydown', onTecla);
    backdrop.addEventListener('click', e => {
      if (e.target === backdrop || e.target.closest('[data-modal-close]')) fechar();
    });

    const primeiroInput = $('input, select, textarea', backdrop);
    if (primeiroInput) primeiroInput.focus();
    return { elemento: backdrop, fechar: fechar };
  };

  /* -------------------- Tabs -------------------- */
  function initTabs() {
    $$('[data-tabs]').forEach(grupo => {
      $$('.tab', grupo).forEach(tab => {
        tab.addEventListener('click', () => {
          const alvo = tab.dataset.tab;
          $$('.tab', grupo).forEach(t => t.classList.toggle('is-active', t === tab));
          $$('[data-tab-panel]', grupo.parentElement).forEach(p => {
            p.hidden = p.dataset.tabPanel !== alvo;
          });
        });
      });
    });
  }

  /* -------------------- Máscaras -------------------- */
  function mascara(valor, tipo) {
    const v = String(valor).replace(/\D/g, '');
    if (tipo === 'cpf') {
      return v.slice(0, 3).replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    if (tipo === 'telefone') {
      const s = v.slice(0, 11);
      if (s.length <= 10) return s.replace(/(\d{0,2})(\d{0,4})(\d{0,4})/, '($1) $2-$3');
      return s.replace(/(\d{0,2})(\d{0,5})(\d{0,4})/, '($1) $2-$3');
    }
    if (tipo === 'moeda') {
      let n = parseFloat(String(valor).replace(/\./g, '').replace(',', '.')) || 0;
      return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return valor;
  }

  function initMasks() {
    document.addEventListener('input', e => {
      const el = e.target;
      const tipo = el.dataset.mask;
      if (!tipo) return;
      const pos = el.selectionStart;
      const antes = el.value.length;
      el.value = mascara(el.value, tipo);
      const depois = el.value.length;
      if (pos !== null) {
        el.setSelectionRange(pos + (depois - antes), pos + (depois - antes));
      }
    });
  }

  /* -------------------- Validação de formulários -------------------- */
  function initForms() {
    $$('[data-validate]').forEach(form => {
      form.addEventListener('submit', e => {
        let primeiro = null;
        $$('[required]', form).forEach(campo => {
          const wrapper = campo.closest('.form-field') || campo.parentElement;
          const erro = wrapper ? wrapper.querySelector('.error-text') : null;
          const vazio = !String(campo.value || '').trim();
          if (vazio) {
            campo.classList.add('is-invalid');
            campo.setAttribute('aria-invalid', 'true');
            if (erro) erro.hidden = false;
            if (!primeiro) primeiro = campo;
          } else {
            campo.classList.remove('is-invalid');
            campo.removeAttribute('aria-invalid');
            if (erro) erro.hidden = true;
          }
        });
        if (primeiro) {
          e.preventDefault();
          primeiro.focus();
          primeiro.scrollIntoView({ behavior: 'smooth', block: 'center' });
          window.sgrhToast('Verifique os campos destacados.', 'danger');
        }
      });

      form.addEventListener('focusout', e => {
        const campo = e.target;
        if (!campo.hasAttribute('required')) return;
        const wrapper = campo.closest('.form-field') || campo.parentElement;
        const erro = wrapper ? wrapper.querySelector('.error-text') : null;
        const vazio = !String(campo.value || '').trim();
        campo.classList.toggle('is-invalid', vazio);
        if (erro) erro.hidden = !vazio;
      });
    });
  }

  /* -------------------- Confirmação de ações destrutivas -------------------- */
  function initConfirm() {
    document.addEventListener('submit', e => {
      const form = e.target;
      const msg = form.dataset.confirm;
      if (msg && !window.confirm(msg)) {
        e.preventDefault();
      }
    });
  }

  /* -------------------- Modal via <template> -------------------- */
  function initTemplates() {
    document.addEventListener('click', e => {
      const btn = e.target.closest('[data-open-template]');
      if (!btn) return;
      const tpl = document.getElementById(btn.dataset.openTemplate);
      if (!tpl) return;
      const conteudo = tpl.innerHTML;
      const titulo = btn.dataset.title || 'Formulário';
      const modal = window.sgrhModal({
        titulo: titulo,
        conteudo: conteudo,
        rodape:
          '<button class="btn btn--secondary" data-modal-close>Cancelar</button>' +
          '<button class="btn btn--primary" data-modal-submit>Salvar</button>'
      });
      const form = modal.elemento.querySelector('form');
      const submit = modal.elemento.querySelector('[data-modal-submit]');
      if (form && submit) {
        submit.addEventListener('click', () => {
          if (!form.reportValidity()) return;
          form.submit();
        });
      }
    });
  }

  /* -------------------- Contadores animados -------------------- */
  function initCounters() {
    const els = $$('[data-count]');
    if (!els.length) return;
    const obs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const alvo = parseFloat(el.dataset.count) || 0;
        const decimais = parseInt(el.dataset.decimals || '0', 10);
        const dur = 700;
        const t0 = performance.now();
        const passo = now => {
          const p = Math.min(1, (now - t0) / dur);
          const valor = alvo * (1 - Math.pow(1 - p, 3));
          el.textContent = valor.toLocaleString('pt-BR', {
            minimumFractionDigits: decimais,
            maximumFractionDigits: decimais
          });
          if (p < 1) requestAnimationFrame(passo);
        };
        requestAnimationFrame(passo);
        obs.unobserve(el);
      });
    }, { threshold: .3 });
    els.forEach(el => obs.observe(el));
  }

  /* -------------------- Busca global (Ctrl+K) -------------------- */
  function initSearch() {
    const input = $('[data-global-search]');
    if (!input) return;
    document.addEventListener('keydown', e => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        input.focus();
      }
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        window.location.href = input.dataset.action || '#';
      }
    });
  }

  /* -------------------- Inicialização -------------------- */
  document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initDropdowns();
    initAlerts();
    initTabs();
    initMasks();
    initForms();
    initConfirm();
    initCounters();
    initSearch();
    initTemplates();
  });
})();
