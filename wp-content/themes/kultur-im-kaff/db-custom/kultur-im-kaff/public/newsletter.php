<form class="kk-form row g-4">
    <div class="col-12 col-md-6">
      <div class="kk-field">
        <input id="f-vorname" type="text">
        <label for="f-vorname">Vorname *</label>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="kk-field">
        <input id="f-nachname" type="text">
        <label for="f-nachname">Nachname *</label>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="kk-field">
        <input id="f-mail" type="email">
        <label for="f-mail">E-Mail *</label>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="kk-field">
        <input id="f-tel" type="tel">
        <label for="f-tel">Telefon</label>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="kk-field always">
        <input id="f-datum" type="date">
        <label for="f-datum">Wunschdatum</label>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="kk-field always">
        <select id="f-art">
          <option>Mitgliedschaft</option>
          <option>Helfereinsatz</option>
          <option>Newsletter</option>
          <option>Saalmiete Spittel</option>
          <option>Anderes</option>
        </select>
        <label for="f-art">Anliegen</label>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="kk-fieldset-title">Mitgliedschaft</div>
      <div class="kk-checks">
        <label class="kk-check"><input type="radio" name="mitglied" checked>Einzelmitglied · Fr. 25.–</label>
        <label class="kk-check"><input type="radio" name="mitglied">Paare / Familien · Fr. 40.–</label>
        <label class="kk-check"><input type="radio" name="mitglied">Noch keine Mitgliedschaft</label>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="kk-fieldset-title">Wo möchtest du mithelfen?</div>
      <div class="kk-checks">
        <label class="kk-check"><input type="checkbox">Bar &amp; Festwirtschaft</label>
        <label class="kk-check"><input type="checkbox">Bühnenbau</label>
        <label class="kk-check"><input type="checkbox">Kasse &amp; Abendkasse</label>
        <label class="kk-check"><input type="checkbox">Technik (Licht &amp; Ton)</label>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <label class="kk-fieldset-title d-block" for="f-beitrag">Freiwilliger Gönnerbeitrag: <span data-range-label>Fr. 40.–</span></label>
      <input class="kk-range" id="f-beitrag" type="range" min="0" max="200" step="10" value="40">
    </div>
    <div class="col-12 col-md-6">
      <label class="kk-fieldset-title d-block" for="f-file">Beilage (z. B. Foto)</label>
      <div class="kk-field kk-field-file"><input id="f-file" type="file"></div>
    </div>

    <div class="col-12">
      <div class="kk-field">
        <textarea id="f-text" rows="5"></textarea>
        <label for="f-text">Nachricht</label>
      </div>
    </div>

    <div class="col-12 kk-checks">
      <label class="kk-check kk-check-top"><input type="checkbox" required>Ich akzeptiere die Statuten des Vereins *</label>
      <label class="kk-check kk-check-top"><input type="checkbox">Newsletter abonnieren (ca. 6 Mails pro Jahr)</label>
    </div>

    <div class="col-12 d-flex flex-wrap align-items-center gap-3">
      <button class="kk-btn kk-btn-lg" type="submit">Absenden</button>
      <button class="kk-btn kk-btn-outline" type="reset">Zurücksetzen</button>
      <span class="kk-success" data-form-success hidden>Danke! Wir melden uns.</span>
    </div>
  </form>