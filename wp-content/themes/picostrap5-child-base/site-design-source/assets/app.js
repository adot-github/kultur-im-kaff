/* Kulturkreis Küttigen-Rombach — Interaktionen
   Setzt ausschliesslich Klassen (.active, .open, .playing) und Textinhalte.
   Sämtliche Gestaltung liegt in assets/theme.css. */
document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Foto-Karussell ---------- */
  document.querySelectorAll('[data-carousel]').forEach(function (car) {
    var slides = car.querySelectorAll('[data-slide]');
    var dots = car.querySelectorAll('[data-dot]');
    var counter = car.querySelector('[data-counter]');
    var i = 0;
    function show(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('active', k === i); });
      dots.forEach(function (d, k) { d.classList.toggle('active', k === i); });
      if (counter) counter.textContent = (i + 1) + ' / ' + slides.length;
    }
    var prev = car.querySelector('[data-car="prev"]');
    var next = car.querySelector('[data-car="next"]');
    if (prev) prev.addEventListener('click', function () { show(i - 1); });
    if (next) next.addEventListener('click', function () { show(i + 1); });
    dots.forEach(function (d, k) { d.addEventListener('click', function () { show(k); }); });
  });

  /* ---------- Archivfilter ---------- */
  var archive = document.querySelector('[data-archive]');
  if (archive) {
    var state = { year: 'Alle', tag: 'Alle' };
    var items = archive.querySelectorAll('[data-year]');
    var count = document.querySelector('[data-archive-count]');
    function activate(selector, el) {
      document.querySelectorAll(selector).forEach(function (b) { b.classList.toggle('active', b === el); });
    }
    function apply() {
      var shown = 0;
      items.forEach(function (el) {
        var ok = (state.year === 'Alle' || el.dataset.year === state.year) &&
                 (state.tag === 'Alle' || el.dataset.tags.split('|').indexOf(state.tag) > -1);
        el.hidden = !ok;
        if (ok) shown++;
      });
      if (count) count.textContent = shown + ' von ' + items.length + ' Anlässen';
    }
    document.querySelectorAll('[data-filter-year]').forEach(function (b) {
      b.addEventListener('click', function () { state.year = b.dataset.filterYear; activate('[data-filter-year]', b); apply(); });
    });
    document.querySelectorAll('[data-filter-tag]').forEach(function (b) {
      b.addEventListener('click', function () { state.tag = b.dataset.filterTag; activate('[data-filter-tag]', b); apply(); });
    });
    apply();
  }

  /* ---------- Player ---------- */
  function setFormat(player, portrait) {
    player.classList.toggle('portrait', portrait);
    var label = portrait ? 'Hochformat 9:16' : 'Querformat 16:9';
    var ph = player.querySelector('[data-ph]');
    if (ph) ph.textContent = 'VIDEO ' + label;
    player.querySelectorAll('[data-fmt]').forEach(function (e) { e.textContent = label; });
  }
  function resetProgress(player) {
    var bar = player.querySelector('[data-bar]');
    var time = player.querySelector('[data-time]');
    if (bar) bar.style.removeProperty('width');
    if (bar) bar.classList.remove('seeked');
    if (time) time.textContent = '0:00 / ' + (player.dataset.duration || '1:30');
    var play = player.querySelector('[data-play]');
    if (play) play.classList.remove('playing');
  }
  document.querySelectorAll('[data-player]').forEach(function (player) {
    var play = player.querySelector('[data-play]');
    var track = player.querySelector('[data-seek]');
    var bar = player.querySelector('[data-bar]');
    var time = player.querySelector('[data-time]');
    if (play) play.addEventListener('click', function () { play.classList.toggle('playing'); });
    if (track) track.addEventListener('click', function (e) {
      var r = track.getBoundingClientRect();
      var p = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
      if (bar) bar.style.width = (p * 100) + '%';
      if (time) time.textContent = '0:' + String(Math.round(p * 60)).padStart(2, '0') + ' / ' + (player.dataset.duration || '1:30');
    });
  });

  /* ---------- Playlist (Videoseite) ---------- */
  var mainPlayer = document.querySelector('[data-main-player]');
  document.querySelectorAll('[data-clip]').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('[data-clip]').forEach(function (x) { x.classList.toggle('active', x === b); });
      if (!mainPlayer) return;
      setFormat(mainPlayer, b.dataset.format === 'portrait');
      mainPlayer.dataset.duration = (b.dataset.meta.split('· ')[1] || '1:30');
      resetProgress(mainPlayer);
      document.querySelectorAll('[data-clip-title]').forEach(function (e) { e.textContent = b.dataset.title; });
      document.querySelectorAll('[data-clip-meta]').forEach(function (e) { e.textContent = b.dataset.meta; });
    });
  });

  /* ---------- Overlays ---------- */
  function closeAll() {
    document.querySelectorAll('[data-overlay]').forEach(function (o) { o.classList.remove('open'); });
    document.body.classList.remove('overflow-hidden');
  }
  function open(o) { o.classList.add('open'); document.body.classList.add('overflow-hidden'); }
  var videoOverlay = document.querySelector('[data-overlay="video"]');
  var imageOverlay = document.querySelector('[data-overlay="image"]');

  document.querySelectorAll('[data-video]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!videoOverlay) return;
      videoOverlay.querySelector('[data-overlay-title]').textContent = b.dataset.title;
      videoOverlay.querySelector('[data-overlay-meta]').textContent = b.dataset.meta;
      var p = videoOverlay.querySelector('[data-player]');
      setFormat(p, b.dataset.format === 'portrait');
      resetProgress(p);
      open(videoOverlay);
    });
  });
  document.querySelectorAll('[data-image]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!imageOverlay) return;
      imageOverlay.querySelector('[data-overlay-title]').textContent = b.dataset.title;
      imageOverlay.querySelector('[data-overlay-meta]').textContent = b.dataset.meta;
      imageOverlay.querySelector('[data-overlay-image]').src = b.dataset.image;
      open(imageOverlay);
    });
  });
  document.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', closeAll); });
  document.querySelectorAll('[data-overlay]').forEach(function (o) {
    o.addEventListener('click', function (e) { if (e.target === o) closeAll(); });
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });

  /* ---------- Formular ---------- */
  document.querySelectorAll('.kk-field').forEach(function (field) {
    var input = field.querySelector('input, select, textarea');
    if (!input) return;
    function update() { field.classList.toggle('filled', !!input.value); }
    input.addEventListener('input', update);
    input.addEventListener('change', update);
    update();
  });
  var form = document.querySelector('form');
  if (form) {
    var success = document.querySelector('[data-form-success]');
    form.addEventListener('submit', function (e) { e.preventDefault(); if (success) success.hidden = false; });
    form.addEventListener('reset', function () {
      if (success) success.hidden = true;
      setTimeout(function () {
        document.querySelectorAll('.kk-field').forEach(function (f) { f.classList.remove('filled'); });
      }, 0);
    });
    var range = form.querySelector('#f-beitrag');
    if (range) range.addEventListener('input', function () {
      document.querySelectorAll('[data-range-label]').forEach(function (e) { e.textContent = 'Fr. ' + range.value + '.–'; });
    });
  }
});
