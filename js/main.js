// =====================
// ANDREA'S PORTFOLIO — interactions
// =====================
(function () {
  'use strict';

  var doc = document;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $(sel, ctx) { return (ctx || doc).querySelector(sel); }
  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }
  function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

  // Live updates: regions marked data-live are swapped for fresh copies when the admin changes
  // something (see the LIVE UPDATES section at the end). Keep the server's original HTML of each
  // region before the scripts below decorate it, so later copies can be compared fairly.
  var liveHooks = [];
  $$('[data-live]').forEach(function (el) { el._server = el.innerHTML; });


  // =====================
  // TOAST
  // =====================
  var toastEl, toastTimer;
  function toast(msg, ms) {
    if (!toastEl) {
      toastEl = doc.createElement('div');
      toastEl.className = 'toast';
      toastEl.setAttribute('role', 'status');
      doc.body.appendChild(toastEl);
    }
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, ms || 2400);
  }


  // =====================
  // HAMBURGER NAV TOGGLE
  // =====================
  (function () {
    var toggle = $('#navToggle');
    var drawer = $('#navDrawer');
    if (!toggle || !drawer) return;

    function setOpen(open) {
      drawer.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }

    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      setOpen(!drawer.classList.contains('open'));
    });

    // Close drawer when a link is clicked, when clicking outside, on Esc, or on resize to desktop
    $$('a', drawer).forEach(function (link) {
      link.addEventListener('click', function () { setOpen(false); });
    });
    doc.addEventListener('click', function (e) {
      if (drawer.classList.contains('open') && !drawer.contains(e.target)) setOpen(false);
    });
    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') setOpen(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 768) setOpen(false);
    });
  })();


  // =====================
  // SCROLL: floating navbar, progress bar, back-to-top
  // =====================
  (function () {
    var bar = $('#progress');
    var navbar = $('.navbar');
    var toTop = $('#toTop');
    var ticking = false;

    function update() {
      var max = doc.documentElement.scrollHeight - window.innerHeight;
      var y = window.scrollY;
      if (bar) bar.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
      if (navbar) navbar.classList.toggle('scrolled', y > 24);
      if (toTop) toTop.classList.toggle('show', y > 600);
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    update();

    if (toTop) {
      toTop.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
      });
    }
  })();


  // =====================
  // LAND ON #SECTION LINKS (e.g. "Back to Works")
  // Images above the section shift the layout while loading, so jump again once everything is in.
  // =====================
  (function () {
    var target = location.hash.length > 1 && doc.getElementById(location.hash.slice(1));
    if (!target) return;
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    function jump() {
      doc.documentElement.style.scrollBehavior = 'auto';
      target.scrollIntoView();
      doc.documentElement.style.scrollBehavior = '';
    }
    jump();
    window.addEventListener('load', jump);
  })();


  // =====================
  // REVEAL ON SCROLL + COUNTERS
  // =====================
  function countUp(el) {
    var end = parseInt(el.getAttribute('data-count'), 10) || 0;
    if (reduceMotion) { el.textContent = end; return; }
    var start = null;
    function step(t) {
      if (!start) start = t;
      var p = Math.min((t - start) / 1200, 1);
      el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  var revealTargets = $$('.reveal, .work-card, [data-count], .stats');

  if ('IntersectionObserver' in window) {
    var revealer = new IntersectionObserver(function (entries) {
      var batch = 0;
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        revealer.unobserve(el);

        if (el.hasAttribute('data-count')) { countUp(el); return; }

        // stagger cards that arrive together
        if (el.classList.contains('work-card')) {
          var delay = batch++ * 80;
          el.style.transitionDelay = delay + 'ms';
          setTimeout(function () { el.style.transitionDelay = ''; }, delay + 800);
        }
        el.classList.add('in');
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealTargets.forEach(function (el) { revealer.observe(el); });
  } else {
    revealTargets.forEach(function (el) {
      el.classList.add('in');
      if (el.hasAttribute('data-count')) el.textContent = el.getAttribute('data-count');
    });
  }


  // =====================
  // BISCUITS: Andrea's hand-drawn biscuits behind every biscuit box.
  // Each flavour comes in wide, landscape and tall drawings; each box gets the one closest to its
  // own shape so the bumps stretch as little as possible. (Flavour comes from the box's --fill.)
  // =====================
  var BISCUITS = '.biscuit, .btn, .chip, .nav-cta, .polaroid, .work-card, .stat, ' +
                 '.contact-detail-item, .copy-btn, .folder-go, .surprise, .lb-frame, .scroll-hint, ' +
                 '.nav-toggle, .to-top';

  var BISCUIT_ART = {
    pink: [
      { src: 'images/assets/biscuits/Biscuit_pink_wide.png', ratio: 1061 / 317 },
      { src: 'images/assets/biscuits/Biscuit_pink.png',      ratio: 877 / 699 },
      { src: 'images/assets/biscuits/Biscuit_pink_tall.png', ratio: 1087 / 1273 }
    ],
    choco: [
      { src: 'images/assets/biscuits/Biscuit_choco_wide.png', ratio: 1061 / 317 },
      { src: 'images/assets/Blank_comment_biscuitbox.png',    ratio: 825 / 571 },
      { src: 'images/assets/biscuits/Biscuit_choco_tall.png', ratio: 1087 / 1273 }
    ],
    vanilla: [
      { src: 'images/assets/biscuits/Biscuit_vanilla_wide.png',     ratio: 1061 / 317 },
      { src: 'images/assets/biscuits/Biscuit_vanilla.png',          ratio: 877 / 699 },
      { src: 'images/assets/biscuits/Biscuit_vanilla_tall.png',     ratio: 1087 / 1273 },
      { src: 'images/assets/biscuits/Biscuit_vanilla_polaroid.png', ratio: 1087 / 1567 }
    ]
  };

  function flavourOf(fill) {
    fill = fill.toLowerCase();
    if (fill === '#8a6e61') return 'choco';
    if (fill === '#f8e6cc') return 'vanilla';
    return 'pink';
  }

  function drawBiscuit(el) {
    var w = el.offsetWidth, h = el.offsetHeight;
    if (!w || !h) return;
    var flavour = flavourOf(getComputedStyle(el).getPropertyValue('--fill').trim());
    var ratio = w / h, best = null, bestDiff = Infinity;
    BISCUIT_ART[flavour].forEach(function (art) {
      var diff = Math.abs(Math.log(ratio / art.ratio));
      if (diff < bestDiff) { bestDiff = diff; best = art; }
    });
    if (el._biscuit === best.src) return;
    el._biscuit = best.src;
    el.style.backgroundImage = 'url("' + best.src + '")';
    el.classList.add('bisc-ready');
  }

  var biscuitResize = 'ResizeObserver' in window ? new ResizeObserver(function (entries) {
    entries.forEach(function (e) { drawBiscuit(e.target); });
  }) : null;
  // redraw when a class swaps the flavour (e.g. the active filter chip turns chocolate)
  var biscuitClass = new MutationObserver(function (records) {
    records.forEach(function (rec) { drawBiscuit(rec.target); });
  });

  // set up every biscuit inside root (the whole page at first, then any live-updated region)
  function initBiscuits(root) {
    var els = $$(BISCUITS, root);
    if (root && root.matches && root.matches(BISCUITS)) els.push(root);
    els.forEach(function (el) {
      drawBiscuit(el);
      if (biscuitResize) biscuitResize.observe(el);
      biscuitClass.observe(el, { attributes: true, attributeFilter: ['class'] });
    });
  }
  liveHooks.push(initBiscuits);


  // =====================
  // BISCUIT BUTTONS: marshmallow squish + a little sprinkle burst
  // =====================
  (function () {
    if (reduceMotion) return;
    var colors = ['#eaa0c0', '#bfe6e2', '#ffe7a8', '#9e1430', '#8a6e61', '#fff8ef'];
    var layer = null;

    function burst(x, y) {
      if (!layer) {
        layer = doc.createElement('div');
        layer.className = 'sprinkle-layer';
        layer.setAttribute('aria-hidden', 'true');
        doc.body.appendChild(layer);
      }
      for (var i = 0; i < 14; i++) {
        var s = doc.createElement('span');
        var a = Math.random() * Math.PI * 2;
        var r = 36 + Math.random() * 46;
        s.className = 'sprinkle';
        s.style.left = x + 'px';
        s.style.top = y + 'px';
        s.style.setProperty('--c', pick(colors));
        s.style.setProperty('--dx', Math.cos(a) * r + 'px');
        s.style.setProperty('--dy', Math.sin(a) * r + 20 + 'px');
        s.style.setProperty('--r0', Math.random() * 180 + 'deg');
        s.style.setProperty('--r1', Math.random() * 540 + 'deg');
        layer.appendChild(s);
        setTimeout(s.remove.bind(s), 850);
      }
    }

    doc.addEventListener('pointerdown', function (e) {
      var el = e.target.closest('.btn, .chip, .surprise, .copy-btn, .nav-cta, .folder');
      if (!el) return;
      var target = el.classList.contains('folder') ? $('.folder-go', el) : el;
      target.classList.remove('squish');
      void target.offsetWidth;
      target.classList.add('squish');
      setTimeout(function () { target.classList.remove('squish'); }, 500);
      burst(e.clientX, e.clientY);
    });
  })();


  // =====================
  // TYPEWRITER ROLES
  // =====================
  (function () {
    var el = $('#typed');
    if (!el || reduceMotion) return;
    var words = JSON.parse(el.getAttribute('data-words'));
    var w = 0;
    var i = words[0].length;
    var deleting = true;

    function tick() {
      var word = words[w];
      if (deleting) {
        i--;
        el.textContent = word.slice(0, i);
        if (i === 0) {
          deleting = false;
          w = (w + 1) % words.length;
          return setTimeout(tick, 350);
        }
        return setTimeout(tick, 45);
      }
      word = words[w];
      i++;
      el.textContent = word.slice(0, i);
      if (i === word.length) {
        deleting = true;
        return setTimeout(tick, 1900);
      }
      setTimeout(tick, 80 + Math.random() * 70);
    }

    setTimeout(tick, 2400);
  })();


  // =====================
  // NAV SCROLL-SPY (home page)
  // =====================
  (function () {
    var sections = $$('main section[id]');
    var links = $$('.nav-links a[href^="#"], .nav-drawer a[href^="#"]');
    if (!sections.length || !links.length || !('IntersectionObserver' in window)) return;

    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var id = entry.target.id;
        links.forEach(function (a) {
          a.classList.toggle('active', a.getAttribute('href') === '#' + id);
        });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });

    sections.forEach(function (s) { spy.observe(s); });
  })();


  // =====================
  // GALLERY: LIGHTBOX + FILTERS
  // =====================
  (function () {
    var grids = $$('[data-gallery]');
    if (!grids.length) return;

    var lb = doc.createElement('div');
    lb.className = 'lightbox';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.setAttribute('aria-label', 'Artwork viewer');
    lb.innerHTML =
      '<button class="lb-btn lb-close" type="button" aria-label="Close">✕</button>' +
      '<button class="lb-btn lb-prev" type="button" aria-label="Previous piece">←</button>' +
      '<figure class="lb-figure">' +
        '<div class="lb-frame"><img class="lb-img" alt="" />' +
        '<figcaption class="lb-cap"><span class="lb-title"></span><span class="lb-desc"></span></figcaption></div>' +
        '<p class="lb-count"></p>' +
      '</figure>' +
      '<button class="lb-btn lb-next" type="button" aria-label="Next piece">→</button>';
    doc.body.appendChild(lb);

    var lbImg = $('.lb-img', lb);
    var lbTitle = $('.lb-title', lb);
    var lbDesc = $('.lb-desc', lb);
    var lbCount = $('.lb-count', lb);
    var list = [], idx = 0, lastFocus = null;
    var lbFrame = $('.lb-frame', lb);

    // The biscuit's scallops grow with the frame, so its edge has to grow too, or big pieces
    // cover the bumps. Each edge is ~11% of the picture + caption it wraps (about 9% of the
    // finished frame), measured separately across and down because the drawing stretches both ways.
    function fitFrame() {
      var w = lbImg.offsetWidth;
      var h = lbImg.offsetHeight + $('.lb-cap', lb).offsetHeight;
      if (!w || !h) return;
      var side = Math.round(w * 0.11), topBottom = Math.round(h * 0.11);
      lbFrame.style.borderWidth = topBottom + 'px ' + side + 'px';
    }

    lbImg.addEventListener('load', fitFrame);
    window.addEventListener('resize', function () {
      if (lb.classList.contains('open')) fitFrame();
    });

    function show(i) {
      idx = (i + list.length) % list.length;
      var card = list[idx];
      var src = $('img', card);
      var desc = $('.work-info p', card);
      lbImg.src = src.currentSrc || src.src;
      lbImg.alt = src.alt;
      lbTitle.textContent = $('h3', card).textContent;
      lbDesc.textContent = desc ? desc.textContent : '';
      lbCount.textContent = (idx + 1) + ' of ' + list.length;
      if (lbImg.complete) fitFrame();
    }

    function open(grid, card) {
      list = $$('.work-card', grid).filter(function (c) { return !c.hidden; });
      lastFocus = doc.activeElement;
      lb.classList.toggle('single', list.length < 2);
      show(Math.max(0, list.indexOf(card)));
      lb.classList.add('open');
      doc.body.classList.add('lb-lock');
      $('.lb-close', lb).focus();
    }

    function close() {
      lb.classList.remove('open');
      doc.body.classList.remove('lb-lock');
      if (lastFocus) lastFocus.focus();
    }

    // make each card's picture a keyboard-friendly button (run again for live-updated cards)
    function prepCards(root) {
      $$('.work-card', root).forEach(function (card) {
        var media = $('.work-media', card);
        if (!media || media.hasAttribute('role')) return;
        media.setAttribute('tabindex', '0');
        media.setAttribute('role', 'button');
        media.setAttribute('aria-label', 'View ' + $('h3', card).textContent + ' larger');
      });
    }
    prepCards(doc);
    liveHooks.push(prepCards);

    // listen on the whole gallery, so cards that arrive later open too
    grids.forEach(function (grid) {
      function cardFrom(e) {
        var media = e.target.closest('.work-media');
        return media && grid.contains(media) ? media.closest('.work-card') : null;
      }
      grid.addEventListener('click', function (e) {
        var card = cardFrom(e);
        if (card) open(grid, card);
      });
      grid.addEventListener('keydown', function (e) {
        var card = cardFrom(e);
        if (card && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); open(grid, card); }
      });
    });

    $('.lb-close', lb).addEventListener('click', close);
    $('.lb-prev', lb).addEventListener('click', function () { show(idx - 1); });
    $('.lb-next', lb).addEventListener('click', function () { show(idx + 1); });
    lb.addEventListener('click', function (e) {
      if (e.target === lb || e.target.classList.contains('lb-figure')) close();
    });

    doc.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') show(idx - 1);
      else if (e.key === 'ArrowRight') show(idx + 1);
      else if (e.key === 'Tab') {
        var btns = $$('.lb-btn', lb).filter(function (b) { return b.offsetParent !== null; });
        var first = btns[0], last = btns[btns.length - 1];
        if (e.shiftKey && doc.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && doc.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    // swipe on phones
    var touchX = null;
    lb.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      if (touchX === null) return;
      var dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 50) show(idx + (dx < 0 ? 1 : -1));
      touchX = null;
    });

    // "surprise me" picks a random visible piece
    var surprise = $('[data-surprise]');
    if (surprise) {
      surprise.addEventListener('click', function () {
        var grid = grids[0];
        var visible = $$('.work-card', grid).filter(function (c) { return !c.hidden; });
        open(grid, pick(visible));
      });
    }

    // filter chips
    var bar = $('[data-filters]');
    if (!bar) return;
    var grid = grids[0];
    var chips = $$('.chip', bar);
    var shownCount = $('#shownCount');
    var current = 'all';
    function cards() { return $$('.work-card', grid); }

    function apply(filter) {
      current = filter;
      grid.classList.add('is-leaving');
      setTimeout(function () {
        var n = 0;
        cards().forEach(function (c) {
          var match = filter === 'all' || c.getAttribute('data-cat') === filter;
          c.hidden = !match;
          c.classList.remove('pop');
          if (match) {
            c.classList.add('in');
            c.style.setProperty('--i', n++);
            void c.offsetWidth;
            c.classList.add('pop');
          }
        });
        grid.classList.remove('is-leaving');
        if (shownCount) shownCount.textContent = n;
      }, reduceMotion ? 0 : 200);
    }

    // the number on each chip, worked out from whichever cards are on the page right now
    function countChips() {
      var all = cards();
      chips.forEach(function (chip) {
        var filter = chip.getAttribute('data-filter');
        var n = $('.n', chip);
        if (n) n.textContent = filter === 'all' ? all.length : all.filter(function (c) {
          return c.getAttribute('data-cat') === filter;
        }).length;
      });
    }
    countChips();

    // after a live update: recount, and keep the chosen filter applied to the new cards
    liveHooks.push(function (root) {
      if (root !== grid) return;
      countChips();
      var n = 0;
      cards().forEach(function (c) {
        c.hidden = !(current === 'all' || c.getAttribute('data-cat') === current);
        if (!c.hidden) n++;
      });
      if (shownCount) shownCount.textContent = n;
    });

    chips.forEach(function (chip) {
      var filter = chip.getAttribute('data-filter');
      chip.addEventListener('click', function () {
        if (chip.classList.contains('active')) return;
        chips.forEach(function (c) {
          c.classList.toggle('active', c === chip);
          c.setAttribute('aria-pressed', String(c === chip));
        });
        apply(filter);
      });
    });
  })();


  // =====================
  // CONTACT PAGE
  // =====================
  (function () {
    var msg = $('#message');
    var counter = $('#charCount');
    if (msg && counter) {
      var update = function () {
        var n = msg.value.length;
        counter.textContent = n === 0 ? 'start typing ♡' : n + (n === 1 ? ' character' : ' characters');
      };
      msg.addEventListener('input', update);
      update();
    }

    $$('[data-copy]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-copy');
        if (navigator.clipboard) {
          navigator.clipboard.writeText(text).then(
            function () { toast('email copied! ♡'); },
            function () { toast(text); }
          );
        } else {
          toast(text);
        }
      });
    });

    // Each message is saved to the site's own inbox (api/message.php, read in the admin) and also
    // emailed through FormSubmit. It counts as sent if either works; only if both fail does it
    // fall back to opening the visitor's own email app.
    var form = $('.contact-form');
    if (!form) return;
    var box = form.closest('.contact-form-box');
    var btn = $('.form-btn', form);
    var to = form.getAttribute('data-to');

    // form.elements, because form.name is the form's own name attribute
    var f = form.elements;

    function mailtoFallback() {
      var body = 'From: ' + f['name'].value + ' <' + f['email'].value + '>\n\n' + f['message'].value;
      window.location.href = 'mailto:' + to +
        '?subject=' + encodeURIComponent(f['subject'].value || 'Hello from your portfolio') +
        '&body=' + encodeURIComponent(body);
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (f['_honey'] && f['_honey'].value) return;

      var label = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Sending…';

      function post(url, isFormSubmit) {
        return fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: new FormData(form) })
          .then(function (res) {
            return res.json().then(function (data) {
              var ok = res.ok && (isFormSubmit ? String(data.success) === 'true' : data.ok === true);
              if (!ok) throw new Error(data.message || data.error || 'Send failed');
              return true;
            });
          });
      }

      var saved = post('api/message.php', false).catch(function () { return false; });
      var emailed = post(form.action.replace('formsubmit.co/', 'formsubmit.co/ajax/'), true).catch(function () { return false; });

      Promise.all([saved, emailed])
        .then(function (results) {
          if (!results[0] && !results[1]) throw new Error('Send failed');
          form.reset();
          if (msg && counter) counter.textContent = 'start typing ♡';
          box.classList.add('sent');
          toast('message sent! ♡');
        })
        .catch(function () {
          toast('hmm, that didn\'t go through. opening your email app instead ✉', 3500);
          setTimeout(mailtoFallback, 900);
        })
        .then(function () {
          btn.disabled = false;
          btn.textContent = label;
        });
    });

    var again = $('[data-write-again]');
    if (again) again.addEventListener('click', function () { box.classList.remove('sent'); });
  })();


  // =====================
  // EDIBLE BISCUITS
  // Each click bites a scalloped chunk out of the nearest edge (a CSS mask of circles).
  // The third bite crumbles it away, and a fresh one pops straight back.
  // =====================
  (function () {
    var edibles = $$('.stat, .about-bubble, .about-cake, .treat-biscuit, .treat-cake');
    if (!edibles.length) return;
    var crumbColors = ['#8a6e61', '#4b3a30', '#c19d8c', '#f6c9d6', '#f8e6cc'];
    var layer = null;
    var ease = 'cubic-bezier(.34, 1.56, .64, 1)';

    function play(el, anim) {
      el.style.animation = 'none';
      void el.offsetWidth;
      el.style.animation = anim;
    }

    function pop(x, y, text, cls, n) {
      if (!layer) {
        layer = doc.createElement('div');
        layer.className = 'sprinkle-layer';
        layer.setAttribute('aria-hidden', 'true');
        doc.body.appendChild(layer);
      }
      for (var i = 0; i < n; i++) {
        var s = doc.createElement('span');
        s.className = cls;
        s.style.left = x + 'px';
        s.style.top = y + 'px';
        if (text) s.textContent = text;
        if (cls === 'crumb') {
          var a = Math.random() * Math.PI * 2, r = 20 + Math.random() * 40;
          s.style.setProperty('--c', pick(crumbColors));
          s.style.setProperty('--dx', Math.cos(a) * r + 'px');
          s.style.setProperty('--dy', Math.sin(a) * r + 30 + 'px');
          s.style.setProperty('--r0', Math.random() * 90 + 'deg');
          s.style.setProperty('--r1', Math.random() * 360 + 'deg');
        }
        layer.appendChild(s);
        setTimeout(s.remove.bind(s), 900);
      }
    }

    // one bite = a big circle at the nearest edge, with three small "tooth" circles along its rim
    function biteMask(el) {
      var w = el.offsetWidth, h = el.offsetHeight, R = Math.min(w, h) * 0.34;
      var layers = [];
      el._bites.forEach(function (b) {
        var cx = b.x + b.nx * R * 0.1, cy = b.y + b.ny * R * 0.1;
        var circles = [[cx, cy, R]];
        [-0.7, 0, 0.7].forEach(function (ang) {
          var dx = -b.nx, dy = -b.ny;
          var rx = dx * Math.cos(ang) - dy * Math.sin(ang), ry = dx * Math.sin(ang) + dy * Math.cos(ang);
          circles.push([cx + rx * R * 0.92, cy + ry * R * 0.92, R * 0.3]);
        });
        circles.forEach(function (c) {
          layers.push('radial-gradient(circle ' + c[2].toFixed(1) + 'px at ' + c[0].toFixed(1) + 'px ' +
            c[1].toFixed(1) + 'px, transparent 97%, #000 100%)');
        });
      });
      var mask = layers.join(', ');
      el.style.webkitMaskImage = mask;
      el.style.maskImage = mask;
    }

    function bite(el, e) {
      if (el._eating) return;
      if (el.classList.contains('stat')) el.classList.add('baked');
      var rect = el.getBoundingClientRect();
      var w = el.offsetWidth, h = el.offsetHeight;
      // click point in the element's own (unrotated) size
      var x = (e.clientX - rect.left) * (w / rect.width);
      var y = (e.clientY - rect.top) * (h / rect.height);
      // snap to the nearest edge; n is the outward direction
      var d = [x, w - x, y, h - y], min = Math.min.apply(null, d), b;
      if (min === d[0]) b = { x: 0, y: y, nx: -1, ny: 0 };
      else if (min === d[1]) b = { x: w, y: y, nx: 1, ny: 0 };
      else if (min === d[2]) b = { x: x, y: 0, nx: 0, ny: -1 };
      else b = { x: x, y: h, nx: 0, ny: 1 };

      el._bites = (el._bites || []).concat(b);
      biteMask(el);
      pop(e.clientX, e.clientY, '', 'crumb', 7);
      pop(e.clientX, e.clientY, el._bites.length < 3 ? 'nom!' : 'yum ♡', 'nom', 1);

      if (el._bites.length < 3) {
        play(el, 'chomp .25s ease-out');
        return;
      }

      // third bite: crumble away, then a fresh biscuit
      el._eating = true;
      play(el, 'crumble .35s ease-in forwards');
      setTimeout(function () {
        el._bites = [];
        biteMask(el);
        play(el, 'regrow .55s ' + ease + ' both');
        setTimeout(function () {
          el.style.animation = '';
          el._eating = false;
        }, 600);
      }, 380);
    }

    function makeEdible(el) {
      if (el.classList.contains('edible')) return;
      el.classList.add('edible');
      el.addEventListener('click', function (e) { bite(el, e); });
      // the stat biscuits bake in with a CSS animation; hand over to the bite animations once it's done
      el.addEventListener('animationend', function (e) {
        if (e.animationName === 'bake') el.classList.add('baked');
      });
    }
    edibles.forEach(makeEdible);
    // counter biscuits that arrive with a live update are edible too (and skip the bake-in)
    liveHooks.push(function (root) {
      $$('.stat', root).forEach(function (el) { el.classList.add('baked'); makeEdible(el); });
    });
  })();

  // no native image dragging (Firefox ignores the CSS for this), so the wing cursor stays put
  $$('img').forEach(function (img) { img.draggable = false; });

  // last, so the lightbox frame built above gets a biscuit too
  initBiscuits();


  // =====================
  // LIVE UPDATES
  // Every few seconds (and whenever the tab comes back into view) ask the site whether the admin
  // changed anything. If so, fetch this page again and swap in each data-live region that differs,
  // so new, edited, moved or removed works show up without a refresh.
  // =====================
  (function () {
    var version = doc.body.getAttribute('data-live-version');
    if (!version || !window.fetch || !window.DOMParser) return;
    var busy = false;

    function swapRegions(fresh) {
      $$('[data-live]').forEach(function (el) {
        var next = fresh.querySelector('[data-live="' + el.getAttribute('data-live') + '"]');
        if (!next || next.innerHTML === el._server) return;
        el.innerHTML = next.innerHTML;
        el._server = next.innerHTML;
        // new cards appear straight away, with the little pop-in
        $$('.work-card, .reveal', el).forEach(function (c) { c.classList.add('in', 'pop'); });
        $$('[data-count]', el).forEach(function (n) { n.textContent = n.getAttribute('data-count'); });
        $$('img', el).forEach(function (img) { img.draggable = false; });
        liveHooks.forEach(function (hook) { hook(el); });
      });
    }

    function check() {
      if (busy || doc.hidden) return;
      busy = true;
      fetch('api/version.php', { cache: 'no-store' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (String(data.v) === version) return;
          return fetch(location.pathname + location.search, { cache: 'no-store' })
            .then(function (res) { return res.text(); })
            .then(function (html) {
              swapRegions(new DOMParser().parseFromString(html, 'text/html'));
              version = String(data.v);
            });
        })
        .catch(function () { /* offline for a moment; try again next time */ })
        .then(function () { busy = false; });
    }

    setInterval(check, 4000);
    doc.addEventListener('visibilitychange', check);
    window.addEventListener('focus', check);
  })();
  if (doc.fonts && doc.fonts.ready) doc.fonts.ready.then(function () { $$(BISCUITS).forEach(drawBiscuit); });
})();
