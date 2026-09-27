# Kulturkreis Küttigen-Rombach — Website (Vorschau)

Statisches HTML-Paket, direkt publizierbar: den gesamten Ordnerinhalt auf einen
Webserver kopieren, `index.html` ist die Startseite. Kein Build-Schritt.

## Seiten
| Datei | Inhalt |
|---|---|
| index.html | Programm 2026/27 (Startseite, Hero) |
| spielorte.html | Spielorte mit Foto-Karussell |
| vorstand.html | Vorstand |
| vorstand-verspielt.html | Vorstand ✳ (Karten mit Stickern, Laufband) |
| archiv.html | Archiv mit Jahres- und Kategoriefiltern |
| video.html | Mediathek, Player 16:9 und 9:16 |
| formular.html | Formular mit allen Elementen |
| seite.html | Universelle Inhaltsseite (Vorlage) |

## Gestaltung — alles an einem Ort

`assets/theme.css` enthält die komplette Gestaltung:

1. **Variablen** unter `:root` — Markenfarben, Bootstrap-Overrides
   (`--bs-body-bg`, `--bs-body-font-family`, `--bs-link-color`, …),
   Typografie-Raster (16/18/20/23/26 px), Abstände, Bildverhältnisse.
2. **Klassen** mit dem Präfix `kk-` — Typo (`kk-h1`, `kk-lead`, `kk-text`,
   `kk-tag`), Navigation, Buttons, Programmzeile (`kk-row`, `kk-rail`,
   `kk-body`, `kk-media`), Hero, Karussell, Player, Overlay, Formular,
   Karten und Footer.

Die HTML-Seiten enthalten **keine Inline-Formatierungen** — nur `class`-Attribute
(Bootstrap-Grid + `kk-`-Klassen) und `data-`Attribute für das Verhalten.
Farbe, Schrift oder Abstand ändern heisst: nur `theme.css` anfassen.

## Verhalten

`assets/app.js` setzt ausschliesslich Klassen (`.active`, `.open`, `.playing`)
und Textinhalte — Karussell, Archivfilter, Videoplayer, Video- und Bild-Overlay,
schwebende Formularlabels, Erfolgsmeldung.

## Assets
- `assets/events/` — Bilder der Anlässe
- `assets/board/` — Porträts
- `assets/hero-spittel.png` — Hero-Bild (in `theme.css` als `--kk-hero-image`)

Inter und Bootstrap 5.3.3 werden per CDN geladen.
Alle Bilder und Videos sind Platzhalter aus dem Programmheft.
