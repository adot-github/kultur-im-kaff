<?php
global $wpdb;

// [kk-jubilaeum-calendar]: Slots aus wp_kk_jub_slots im Kalender (bs-calendar),
// Klick auf einen freien Slot öffnet das Anmeldeformular. Logik: ../jubilaeum-slots.php

$kk_jub_vendor = get_stylesheet_directory_uri() . '/js/vendor';
wp_enqueue_style( 'bootstrap-icons', $kk_jub_vendor . '/bootstrap-icons/bootstrap-icons.min.css', array(), '1.13.1' );
wp_enqueue_script( 'bs-calendar', $kk_jub_vendor . '/bs-calendar/bs-calendar.min.js', array( 'jquery' ), '2.4.0', true );

$kk_jub_types = $wpdb->get_results( "SELECT id, str_slot_type_name, str_slot_type_color FROM {$wpdb->prefix}kk_jub_slot_types ORDER BY id ASC", ARRAY_A );

$kk_jub_config = array(
    'from'    => KK_JUB_DATE_FROM,
    'to'      => KK_JUB_DATE_TO,
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'slots'   => kk_jub_get_calendar_slots(),
);
?>
<div class="kk-jub">
  <?php if ( $kk_jub_types ) : ?>
    <ul class="kk-jub-legend">
      <?php foreach ( $kk_jub_types as $kk_jub_type ) :
          $kk_jub_color = ltrim( (string) $kk_jub_type['str_slot_type_color'], '#' ); ?>
        <li><span class="kk-jub-dot" style="background:#<?php echo esc_attr( preg_match( '/^[0-9a-f]{6}$/i', $kk_jub_color ) ? $kk_jub_color : 'ccc' ); ?>"></span><?php echo esc_html( $kk_jub_type['str_slot_type_name'] ); ?></li>
      <?php endforeach; ?>
    </ul>
    <ul class="kk-jub-legend">
      <li><span class="kk-jub-status kk-jub-status-0"></span>frei</li>
      <li><span class="kk-jub-status kk-jub-status-1"></span>reserviert</li>
      <li><span class="kk-jub-status kk-jub-status-2"></span>gebucht</li>
    </ul>
  <?php endif; ?>

  <div id="kkJubCalendar"></div>
  <script type="application/json" id="kkJubData"><?php echo wp_json_encode( $kk_jub_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
</div>

<div class="modal fade kk-jub-modal" id="kkJubModal" tabindex="-1" aria-labelledby="kkJubModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="kk-fieldset-title mb-1" data-jub-type></div>
          <h2 class="modal-title h4" id="kkJubModalTitle" data-jub-when></h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schliessen"></button>
      </div>
      <div class="modal-body">
        <div class="kk-jub-msg kk-jub-msg-error" data-jub-booked hidden></div>

        <form class="kk-form row g-4" data-jub-form novalidate>
          <input type="hidden" name="action" value="kk_jub_register">
          <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'kk_jub_register' ) ); ?>">
          <input type="hidden" name="slot_id" value="">
          <div class="kk-jub-hp" aria-hidden="true">
            <label for="jub-website">Website</label>
            <input id="jub-website" type="text" name="kk_website" tabindex="-1" autocomplete="off">
          </div>

          <div class="col-12">
            <div class="kk-field">
              <input id="jub-title" name="title" type="text" maxlength="255" required>
              <label for="jub-title">Titel des Beitrages *</label>
            </div>
          </div>
          <!-- Nur bei Open Stage (ersetzt die Zeit im Titel): bestimmt beim Speichern die Endzeit -->
          <div class="col-12 col-md-6" data-jub-duration-field hidden>
            <div class="kk-field always">
              <select id="jub-duration" name="duration" disabled required>
                <option value="">Bitte wählen</option>
                <?php foreach ( KK_JUB_DURATIONS as $kk_jub_min ) : ?>
                  <option value="<?php echo (int) $kk_jub_min; ?>"><?php echo (int) $kk_jub_min; ?> Minuten</option>
                <?php endforeach; ?>
              </select>
              <label for="jub-duration">Dauer des Beitrags *</label>
            </div>
          </div>
          <div class="col-12">
            <div class="kk-field">
              <textarea id="jub-description" name="description" rows="4" required></textarea>
              <label for="jub-description">Beschreibung des Beitrages *</label>
            </div>
          </div>
          <div class="col-12">
            <div class="kk-field">
              <input id="jub-club" name="club" type="text" maxlength="255">
              <label for="jub-club">Verein/Gruppe/Künstler:in</label>
            </div>
          </div>
          <div class="col-12">
            <div class="kk-field">
              <textarea id="jub-club-text" name="club_text" rows="4"></textarea>
              <label for="jub-club-text">Beschreibung Verein/Gruppe/Künstler:in</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="kk-field">
              <input id="jub-first" name="first_name" type="text" maxlength="255" autocomplete="given-name" required>
              <label for="jub-first">Vorname *</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="kk-field">
              <input id="jub-last" name="last_name" type="text" maxlength="255" autocomplete="family-name" required>
              <label for="jub-last">Nachname *</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="kk-field">
              <input id="jub-email" name="email" type="email" maxlength="255" autocomplete="email" required>
              <label for="jub-email">E-Mail *</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="kk-field">
              <input id="jub-phone" name="phone" type="tel" maxlength="255" autocomplete="tel" required>
              <label for="jub-phone">Telefon *</label>
            </div>
          </div>
          <div class="col-12">
            <div class="kk-field">
              <input id="jub-url" name="url" type="text" maxlength="255" inputmode="url" autocomplete="url">
              <label for="jub-url">Website/Link</label>
            </div>
          </div>

          <div class="col-12">
            <div class="kk-jub-msg kk-jub-msg-error" data-jub-error hidden></div>
          </div>
          <div class="col-12 d-flex flex-wrap align-items-center gap-3">
            <button class="kk-btn kk-btn-lg" type="submit" data-jub-submit>Slot anmelden</button>
            <button class="kk-btn kk-btn-outline" type="button" data-bs-dismiss="modal">Abbrechen</button>
          </div>
        </form>

        <div class="kk-success" data-jub-success hidden></div>
      </div>
    </div>
  </div>
</div>

<style>
  .kk-jub .kk-jub-legend { display: flex; flex-wrap: wrap; gap: .5rem 1.5rem; list-style: none; padding: 0; margin: 0 0 1rem; font-weight: 600; }
  .kk-jub .kk-jub-legend li { display: flex; align-items: center; gap: .5rem; text-indent: 0; }
  /* Gedankenstrich der Seiten-Listen (.kk-pagehead ul li::before) hier nicht anzeigen */
  .kk-jub .kk-jub-legend li::before { content: none; }
  .kk-jub-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; border: 1px solid var(--kk-rule); }

  /* Status-Punkt (ysn_slot_booked 0/1/2) und "Buchen"-Button im Termin */
  .kk-jub-status { display: inline-block; flex: none; width: 10px; height: 10px; border-radius: 50%; box-shadow: 0 0 0 1.5px #fff; }
  .kk-jub-status-0 { background: #1f9d3a; }
  .kk-jub-status-1 { background: #d35400; }
  .kk-jub-status-2 { background: #d7191c; }
  .kk-jub-app { display: block; min-width: 0; width: 100%; padding: 2px 4px; font-size: 11px; line-height: 1.3; }
  .kk-jub-app-line { display: flex; align-items: flex-start; gap: 5px; min-width: 0; }
  .kk-jub-app-line .kk-jub-status { margin-top: 3px; }
  .kk-jub-app-title { flex: 1 1 auto; min-width: 0; white-space: normal; overflow-wrap: anywhere; }
  .kk-jub-app-actions { margin-top: 3px; }
  .kk-jub-app-time { flex: none; opacity: .8; }
  .kk-jub-book { flex: none; border: 0; border-radius: 999px; padding: 2px 10px; background: var(--kk-blue, #562BFF); color: var(--kk-white, #fff); font-size: 10px; font-weight: 700; line-height: 1.4; letter-spacing: .03em; cursor: pointer; }
  .kk-jub-book:hover { background: var(--kk-blue-dark, #4320D6); color: var(--kk-white, #fff); }

  /* Listen-Stil der Seite (.kk-pagehead ul: Gedankenstrich, Einzug) nicht auf die Listen von bs-calendar anwenden */
  #kkJubCalendar ul,
  [data-bs-calendar-mobile-offcanvas] ul { padding-left: 0; }
  #kkJubCalendar ul li,
  [data-bs-calendar-mobile-offcanvas] ul li { text-indent: 0; }
  #kkJubCalendar ul li::before,
  [data-bs-calendar-mobile-offcanvas] ul li::before { content: none; }

  /* "Heute" liegt ausserhalb des Jubiläums – Button ausblenden */
  #kkJubCalendar [data-today] { display: none !important; }
  /* Navigation am Rand des Zeitraums sperren (Klicks blockiert das Script) */
  #kkJubCalendar.kk-jub-at-min [data-prev],
  #kkJubCalendar.kk-jub-at-max [data-next] { opacity: .25; cursor: default; }
  #kkJubCalendar [data-appointment] { cursor: pointer; }

  /* Wochenansicht: nur Donnerstag und Freitag (Slot-Tage). Wochentag-Nummern von bs-calendar: 0 = So … 6 = Sa.
     Kopfzeile: Spalten mit [data-all-day=N]; Raster: .wc-day-week-view[data-week-day=N].
     Montag wird nur auf Breite 0 zusammengeklappt, weil in seiner Spalte die Zeitbeschriftung (18:00 …) steckt. */
  #kkJubCalendar div:has(> .wc-week-view) > div:first-child > div:has(> [data-all-day="1"]),
  #kkJubCalendar div:has(> .wc-week-view) > div:first-child > div:has(> [data-all-day="2"]),
  #kkJubCalendar div:has(> .wc-week-view) > div:first-child > div:has(> [data-all-day="3"]),
  #kkJubCalendar div:has(> .wc-week-view) > div:first-child > div:has(> [data-all-day="6"]),
  #kkJubCalendar div:has(> .wc-week-view) > div:first-child > div:has(> [data-all-day="0"]),
  #kkJubCalendar .wc-week-view > .wc-day-week-view[data-week-day="2"],
  #kkJubCalendar .wc-week-view > .wc-day-week-view[data-week-day="3"],
  #kkJubCalendar .wc-week-view > .wc-day-week-view[data-week-day="6"],
  #kkJubCalendar .wc-week-view > .wc-day-week-view[data-week-day="0"] { display: none !important; }
  #kkJubCalendar .wc-week-view > .wc-day-week-view[data-week-day="1"] { flex: 0 0 0 !important; width: 0; min-width: 0; border: 0 !important; }
  #kkJubCalendar .wc-week-view > .wc-day-week-view[data-week-day="4"] { border-left: 1px solid var(--bs-border-color); }

  /* Monatsansicht ebenso: Tabellenzeile = Wochennummer, Mo, Di, Mi, Do, Fr, Sa, So (Woche beginnt am Montag) */
  #kkJubCalendar tr.wc-calendar-content > td:nth-child(2),
  #kkJubCalendar tr.wc-calendar-content > td:nth-child(3),
  #kkJubCalendar tr.wc-calendar-content > td:nth-child(4),
  #kkJubCalendar tr.wc-calendar-content > td:nth-child(7),
  #kkJubCalendar tr.wc-calendar-content > td:nth-child(8) { display: none; }
  #kkJubCalendar tr.wc-calendar-content > td:nth-child(6) { border-right: var(--bs-border-width) solid var(--bs-border-color); }
  /* bs-calendar setzt die Zellhöhe per JS = Zellbreite (quadratisch). Mit 2 statt 7 Spalten würden die Zellen
     so hoch, dass nur eine Woche Platz hat – feste Höhe für Datum + 2 Slots */
  #kkJubCalendar tr.wc-calendar-content > td { height: 120px !important; }
  /* … und gibt dem Monatsbereich per JS eine feste Höhe, die die Tabelle abschneidet: mit dem Inhalt wachsen lassen */
  #kkJubCalendar .wc-calendar-view-container:has(> table tr.wc-calendar-content) { height: auto !important; }

  .kk-jub-modal .modal-content { background: var(--kk-bg-color); color: var(--kk-ink); border: 0; border-radius: 0; }
  .kk-jub-modal .modal-header,
  .kk-jub-modal .modal-body { padding: 1.5rem; }
  .kk-jub-modal .modal-header { align-items: flex-start; border-bottom: 2px solid var(--kk-ink); }
  .kk-jub-modal .modal-title { font-weight: 700; margin: 0; }
  .kk-jub-modal .kk-form { max-width: none; }
  .kk-jub-msg { padding: 10px 16px; font-weight: 700; font-size: var(--kk-fs-16); }
  .kk-jub-msg-error { background: var(--kk-ink); color: var(--kk-text-on-black); }
  .kk-jub-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var $ = window.jQuery;
  var root = document.getElementById('kkJubCalendar');
  var modalEl = document.getElementById('kkJubModal');
  if (!root || !modalEl || !$ || !$.fn.bsCalendar || !window.bootstrap) return;

  var cfg = JSON.parse(document.getElementById('kkJubData').textContent);
  var MIN = cfg.from, MAX = cfg.to;
  var slotsById = {};
  cfg.slots.forEach(function (s) { slotsById[s.id] = s; });

  var $cal = $(root);
  var range = { from: null, to: null };

  // Termin-Inhalt: Status-Punkt, Text, bei freien Slots "Buchen"-Button.
  // Der Button liegt im [data-appointment]-Element, der Klick landet also im selben Handler wie ein Klick auf den Termin.
  function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
    });
  }
  function renderSlot(a, withTime) {
    var s = slotsById[a.id] || a;
    var state = s.kkState || 0;
    return '<div class="kk-jub-app">' +
      '<div class="kk-jub-app-line">' +
      '<span class="kk-jub-status kk-jub-status-' + state + '"></span>' +
      (withTime ? '<span class="kk-jub-app-time">' + esc(s.start.slice(11, 16)) + '</span>' : '') +
      '<span class="kk-jub-app-title">' + esc(s.title) + '</span>' +
      '</div>' +
      (state === 0 ? '<div class="kk-jub-app-actions"><button type="button" class="kk-jub-book">Buchen</button></div>' : '') +
      '</div>';
  }
  function renderAgenda(a) {
    var s = slotsById[a.id] || a;
    return '<div class="text-body-secondary small flex-shrink-0 text-center" style="width: 92px;">' +
      esc(s.start.slice(11, 16)) + '–' + esc(s.end.slice(11, 16)) + '</div>' +
      '<div class="flex-fill min-w-0">' + renderSlot(a, false) + '</div>';
  }

  $cal.bsCalendar({
    locale: 'de-CH',
    startWeekOnSunday: false,
    startDate: MIN,
    startView: 'month',
    views: ['week', 'month', 'agenda'],
    showAddButton: false,
    showAbout: false,
    showTasks: false,
    search: null,
    navigateOnWheel: false,
    draggable: false,
    hourSlots: { start: 18, end: 23, height: 60 },
    formatter: {
      day: function (a) { return renderSlot(a, false); },
      week: function (a) { return renderSlot(a, false); },
      month: function (a) { return renderSlot(a, true); },
      monthExpanded: function (a) { return renderSlot(a, true); },
      agenda: renderAgenda
    },
    url: function (req) {
      if (!req.fromDate) return Promise.resolve([]);
      // Ausserhalb des Zeitraums gelandet (z. B. über den Mini-Kalender): zurück an den Rand
      if (req.toDate < MIN || req.fromDate > MAX) {
        setTimeout(function () { $cal.bsCalendar('setDate', req.toDate < MIN ? MIN : MAX); });
        return Promise.resolve([]);
      }
      range.from = req.fromDate;
      range.to = req.toDate;
      root.classList.toggle('kk-jub-at-min', range.from <= MIN);
      root.classList.toggle('kk-jub-at-max', range.to >= MAX);
      return Promise.resolve(Object.keys(slotsById).map(function (id) { return slotsById[id]; }).filter(function (s) {
        var d = s.start.slice(0, 10);
        return d >= req.fromDate && d <= req.toDate;
      }).map(function (s) {
        return $.extend({}, s, { editable: false, deleteable: false });
      }));
    }
  });

  // bs-calendar schreibt in der Wochenansicht fest "W26 · Juli 2027" in den Titel – auf "Woche 26" umschreiben
  function localizeWeekTitle(el) {
    var text = el.textContent;
    if (/^W\d/.test(text)) el.textContent = text.replace(/^W(\d+)/, 'Woche $1');
  }
  new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      var el = m.target.nodeType === 1 ? m.target : m.target.parentElement;
      if (el && el.matches('[data-calendar-view-title]')) localizeWeekTitle(el);
    });
  }).observe(document.body, { childList: true, characterData: true, subtree: true });
  document.querySelectorAll('[data-calendar-view-title]').forEach(localizeWeekTitle);

  // Läuft in der Capture-Phase vor den Handlern von bs-calendar:
  // sperrt Vor/Zurück am Rand des Zeitraums und ersetzt das Info-Fenster durch das Anmeldeformular.
  function inCalendar(el) {
    return root.contains(el) || !!el.closest('[data-bs-calendar-mobile-offcanvas]');
  }
  function guard(e) {
    var t = e.target;
    if (!(t instanceof Element)) return;
    var nav = t.closest('[data-prev], [data-next]');
    if (nav && inCalendar(nav)) {
      var blocked = nav.hasAttribute('data-prev') ? (range.from && range.from <= MIN) : (range.to && range.to >= MAX);
      if (blocked) {
        e.preventDefault();
        e.stopPropagation();
      }
      return;
    }
    var app = t.closest('[data-appointment]');
    if (app && inCalendar(app)) {
      e.stopPropagation();
      if (e.type === 'click') {
        e.preventDefault();
        var a = $(app).data('appointment');
        if (a && slotsById[a.id]) openSlot(slotsById[a.id]);
      }
    }
  }
  document.addEventListener('click', guard, true);
  document.addEventListener('touchend', guard, true);

  // ---- Anmelde-Modal ----
  document.body.appendChild(modalEl);
  var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  var form = modalEl.querySelector('[data-jub-form]');
  var errorBox = modalEl.querySelector('[data-jub-error]');
  var successBox = modalEl.querySelector('[data-jub-success]');
  var bookedBox = modalEl.querySelector('[data-jub-booked]');
  var submitBtn = modalEl.querySelector('[data-jub-submit]');
  var durationField = modalEl.querySelector('[data-jub-duration-field]');
  var fmtDate = new Intl.DateTimeFormat('de-CH', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

  function showError(msg) {
    errorBox.textContent = msg;
    errorBox.hidden = !msg;
  }

  function openSlot(s) {
    var date = new Date(s.start.slice(0, 10) + 'T00:00:00');
    modalEl.querySelector('[data-jub-type]').textContent = s.kkType || 'Slot';
    // Open Stage: nur das Datum im Titel, die Zeit ergibt sich aus der gewählten Dauer
    modalEl.querySelector('[data-jub-when]').textContent = s.kkOpenStage
      ? fmtDate.format(date)
      : fmtDate.format(date) + ', ' + s.start.slice(11, 16) + '–' + s.end.slice(11, 16) + ' Uhr';

    form.reset();
    durationField.hidden = !s.kkOpenStage;
    durationField.querySelector('select').disabled = !s.kkOpenStage; // ausgeblendet: nicht senden, nicht prüfen
    form.querySelectorAll('.kk-field.filled').forEach(function (f) { f.classList.remove('filled'); });
    form.elements.slot_id.value = s.id;
    showError('');
    successBox.hidden = true;
    form.hidden = !!s.kkBooked;
    bookedBox.hidden = !s.kkBooked;
    bookedBox.textContent = s.kkState === 2
      ? 'Dieser Slot ist bereits gebucht: ' + s.title + '.'
      : 'Dieser Slot ist bereits reserviert, aber noch nicht final bestätigt.';
    modal.show();
  }

  // Schwebende Labels der .kk-field-Felder
  form.addEventListener('input', function (e) {
    var field = e.target.closest('.kk-field');
    if (field) field.classList.toggle('filled', e.target.value !== '');
  });

  function markBooked(id, appointment) {
    slotsById[id] = $.extend(slotsById[id], appointment || { kkBooked: true, kkState: 1, title: 'reserviert' });
    $cal.bsCalendar('refresh');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    showError('');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var slotId = form.elements.slot_id.value;
    submitBtn.disabled = true;
    fetch(cfg.ajaxUrl, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) {
          markBooked(slotId, res.data.appointment);
          form.hidden = true;
          successBox.textContent = res.data.message;
          successBox.hidden = false;
          return;
        }
        var data = (res && res.data) || {};
        if (data.booked) markBooked(slotId);
        showError(data.message || 'Die Anmeldung konnte nicht gespeichert werden.');
      })
      .catch(function () {
        showError('Die Anmeldung konnte nicht gesendet werden. Bitte versuche es später nochmals.');
      })
      .finally(function () {
        submitBtn.disabled = false;
      });
  });
});
</script>
