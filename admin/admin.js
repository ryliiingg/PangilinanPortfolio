// Admin: buttons that work without reloading, live updates, and Andrea's biscuit drawings on
// the buttons, tabs and cards.
(function () {
  'use strict';

  var doc = document;
  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }

  // ---------- toast ----------
  var toastEl, toastTimer;
  function toast(msg, isError) {
    if (!toastEl) {
      toastEl = doc.createElement('div');
      toastEl.className = 'toast';
      toastEl.setAttribute('role', 'status');
      doc.body.appendChild(toastEl);
    }
    toastEl.textContent = msg;
    toastEl.classList.toggle('toast--error', !!isError);
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2600);
  }

  // ---------- biscuits ----------
  // Same idea as the website: each box gets the drawing closest to its shape. Flavour comes from
  // the CSS custom property --flavour (pink, choco or vanilla).
  var BISCUITS = '.btn, .tabs a, .icon, .card, .message, .empty';
  var ART = {
    pink: [
      ['../images/assets/biscuits/Biscuit_pink_wide.png', 1061 / 317],
      ['../images/assets/biscuits/Biscuit_pink.png', 877 / 699],
      ['../images/assets/biscuits/Biscuit_pink_tall.png', 1087 / 1273]
    ],
    choco: [
      ['../images/assets/biscuits/Biscuit_choco_wide.png', 1061 / 317],
      ['../images/assets/Blank_comment_biscuitbox.png', 825 / 571],
      ['../images/assets/biscuits/Biscuit_choco_tall.png', 1087 / 1273]
    ],
    vanilla: [
      ['../images/assets/biscuits/Biscuit_vanilla_wide.png', 1061 / 317],
      ['../images/assets/biscuits/Biscuit_vanilla.png', 877 / 699],
      ['../images/assets/biscuits/Biscuit_vanilla_tall.png', 1087 / 1273],
      ['../images/assets/biscuits/Biscuit_vanilla_polaroid.png', 1087 / 1567]
    ]
  };

  function draw(el) {
    var w = el.offsetWidth, h = el.offsetHeight;
    if (!w || !h) return;
    var flavour = getComputedStyle(el).getPropertyValue('--flavour').trim() || 'pink';
    var art = ART[flavour] || ART.pink, best = art[0], bestDiff = Infinity;
    art.forEach(function (a) {
      var diff = Math.abs(Math.log((w / h) / a[1]));
      if (diff < bestDiff) { bestDiff = diff; best = a; }
    });
    var key = best[0];
    if (el._biscuit === key) return;
    el._biscuit = key;
    el.style.backgroundImage = 'url("' + key + '")';
    el.classList.add('bisc-ready');
  }

  var resize = 'ResizeObserver' in window ? new ResizeObserver(function (entries) {
    entries.forEach(function (e) { draw(e.target); });
  }) : null;
  var classes = new MutationObserver(function (records) {
    records.forEach(function (r) { r.target._biscuit = null; draw(r.target); });
  });

  function biscuits(root) {
    $$(BISCUITS, root).forEach(function (el) {
      draw(el);
      if (resize) resize.observe(el);
      classes.observe(el, { attributes: true, attributeFilter: ['class'] });
    });
  }

  // ---------- live regions ----------
  // Regions marked data-live are swapped for fresh copies after an action, or when something
  // changes elsewhere (a new message, an edit in another tab). Nothing reloads.
  $$('[data-live]').forEach(function (el) { el._server = el.innerHTML; });

  function refresh() {
    return fetch(location.href, { cache: 'no-store' })
      .then(function (res) {
        if (res.redirected && /login\.php/.test(res.url)) { location.href = res.url; throw new Error('logged out'); }
        return res.text();
      })
      .then(function (html) {
        var fresh = new DOMParser().parseFromString(html, 'text/html');
        $$('[data-live]').forEach(function (el) {
          var next = fresh.querySelector('[data-live="' + el.getAttribute('data-live') + '"]');
          if (!next || next.innerHTML === el._server) return;
          el.innerHTML = next.innerHTML;
          el._server = next.innerHTML;
          biscuits(el);
        });
      });
  }

  // ---------- forms ----------
  doc.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.hasAttribute('data-confirm') && !window.confirm(form.getAttribute('data-confirm'))) {
      e.preventDefault();
      return;
    }
    if (!form.hasAttribute('data-ajax') || !window.fetch) return;

    // send it in the background, then pull in the updated page parts
    e.preventDefault();
    var data = new FormData(form);
    if (e.submitter && e.submitter.name) data.append(e.submitter.name, e.submitter.value);
    $$('button', form).forEach(function (b) { b.disabled = true; });

    fetch(form.getAttribute('action') || location.href, {
      method: 'POST',
      body: data,
      headers: { 'X-Requested-With': 'fetch' }
    })
      .then(function (res) { return res.json(); })
      .then(function (r) {
        if (r.message) toast(r.message, !r.ok);
        return refresh();
      })
      .catch(function () {
        toast('That didn\'t work. Please try again.', true);
        $$('button', form).forEach(function (b) { b.disabled = false; });
      });
  });

  // ---------- watch for changes ----------
  if (doc.querySelector('.topbar') && window.fetch) {
    var sig = null;
    var poll = function () {
      if (doc.hidden) return;
      fetch('status.php', { cache: 'no-store' })
        .then(function (res) { return res.json(); })
        .then(function (d) {
          if (!d.ok) return;
          if (sig !== null && d.sig !== sig) refresh();
          sig = d.sig;
        })
        .catch(function () {});
    };
    poll();
    setInterval(poll, 5000);
    doc.addEventListener('visibilitychange', poll);
  }

  // ---------- add / edit form ----------
  var input = doc.getElementById('image');
  var preview = doc.getElementById('preview');
  var dropText = doc.getElementById('drop-text');
  if (input && preview) {
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      preview.src = URL.createObjectURL(file);
      preview.hidden = false;
      if (dropText) dropText.hidden = true;
    });
  }

  // the Type picker only applies to Illustrations
  var section = doc.getElementById('section');
  if (section) {
    var update = function () {
      $$('.category-field').forEach(function (field) {
        var on = field.getAttribute('data-for') === section.value;
        field.hidden = !on;
        field.querySelector('select').disabled = !on;
      });
    };
    section.addEventListener('change', update);
    update();
  }

  biscuits(doc);
  if (doc.fonts && doc.fonts.ready) doc.fonts.ready.then(function () { $$(BISCUITS).forEach(draw); });
})();
