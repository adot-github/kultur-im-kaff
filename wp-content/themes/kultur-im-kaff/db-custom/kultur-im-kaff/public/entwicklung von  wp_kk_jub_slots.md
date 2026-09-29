Es gibt die Tabelle wp_kk_jub_slots
Diese Tabelle soll mit Datensätzen befüllt werden Im Zeitraum von
Freitag 4.6.2027 bis Samstag 28.8.2027

Es sollen folgende Datensätze eingefügt werden:
jeweils am Freitag und Samstag Datensätze einfügen

Freitags jeweils ein Datensatz, 19.00 Uhr bis 22.30 Uhr fky_slot_type = 2
Samstags jeweils zwei Datensätze, 19.00 Uhr bis 21.00 Uhr und 21.30 Uhr bis 22.30 Uhr fky_slot_type = 1

Diese Termine sollen in der öffentlichen Website angezeigt werden.
Dafür möchte ich folgendes Kalendermodul verwenden.
https://github.com/ThomasDev-de/bs-calendar
Der Kalender zeigt die Termine in der Wochenansicht.
Die Navigation sollte nicht über den gegebenen Zeitraum hinaus möglich sein (Freitag 4.6.2027 bis Samstag 28.2027).
Die Anzeige sollte durchgehend in Deutsch sein.

Das Anzeigefeil ist wie folg:
wp-content\themes\kultur-im-kaff\db-custom\kultur-im-kaff\public\kk-jubilaeum-calendar.php

Was musst du noch wissen, um das umsetzen zu können?

Vorweg: Die Anzeigedatei kk-jubilaeum-calendar.php ist noch leer, und auf die Datenbank konnte ich gerade nicht zugreifen. Deshalb kenne ich die Struktur von wp_kk_jub_slots noch nicht. Diese Punkte brauche ich noch:

Zeitraum und Daten

Enddatum: Bei „Samstag 28.2027“ fehlt der Monat. Meinst du den 28.8.2027? Das ist ein Samstag. Der Start am 4.6.2027 ist ein Freitag und passt.
Spalten der Tabelle: Wie heißen die Spalten für Datum, Beginn und Ende? Gibt es eine DATETIME-Spalte für Start und Ende oder getrennte DATE- und TIME-Spalten? Gibt es weitere Pflichtfelder wie Titel, Status oder Kapazität? Am einfachsten schickst du mir das CREATE TABLE oder die Ausgabe von DESCRIBE wp_kk_jub_slots.
Slot-Typen: Gibt es eine Tabelle für fky_slot_type mit Bezeichnungen? Was bedeuten Typ 1 und Typ 2, und welcher Text soll dafür im Kalender stehen?
Einfügen: Reicht ein einmaliges SQL-Script, das du selbst ausführst? Oder soll ich die Datensätze direkt in die lokale DB schreiben? Ist die Tabelle aktuell leer?
Kalender (bs-calendar)

Inhalt der Termine: Was soll auf einem Termin stehen, zum Beispiel nur die Uhrzeit, ein Titel oder der Slot-Typ? Sollen die beiden Typen verschiedene Farben haben, zum Beispiel in der Akzentfarbe?
Klick auf einen Termin: Soll dabei etwas passieren, etwa ein Link zu einer Anmeldung, ein Popup mit Details oder die Anzeige „belegt/frei“? Oder ist der Kalender nur zum Anschauen?
Ansicht:
Gibt es nur die Wochenansicht ohne Umschalter?
Wochenbeginn wäre Montag. Soll der sichtbare Stundenbereich knapp gehalten werden, etwa 18–23 Uhr?
Auf dem Handy ist eine Wochenansicht sehr eng. Soll dort die Tagesansicht erscheinen?
Startwoche: Soll der Kalender immer mit der Woche vom 4.6.2027 öffnen? Oder mit der aktuellen Woche, wenn das heutige Datum im Zeitraum liegt?
Einbindung der Bibliothek: bs-calendar braucht jQuery, Bootstrap 5 JS und Bootstrap Icons. Soll ich die Bibliothek per CDN (jsDelivr) laden oder eine Kopie im Theme ablegen? Wird die Datei als Shortcode auf einer bestehenden Seite eingebunden, so wie events.php?
Wenn du mir wenigstens 1 bis 4 beantwortest, übernehme ich für den Rest diese Vorgaben:


1. Meinst du den 28.8.2027? ja
2. Spalten der Tabelle: Wie heißen die Spalten für Datum, Beginn und Ende?
dtm_slot_date, dtm_slot_from, dtm_slot_to
3. Slot-Typen: Gibt es eine Tabelle für fky_slot_type mit Bezeichnungen? ja. wp_kk_jub_slot_types
4. Einfügen: Reicht ein einmaliges SQL-Script, das du selbst ausführst?
ja
5. Titel, ysn_slot_booked
6. Klick auf einen Termin: Soll dabei etwas passieren, etwa ein Link zu einer Anmeldung,
ja, ein Formular, um sich für diesen Slot zu registrieren. Die felder findest du in der tabelle wp_kk_jub_slots
7. Gibt es nur die Wochenansicht ohne Umschalter?
Umschalter sollte möglich sein.
8. Startwoche: Soll der Kalender immer mit der Woche vom 4.6.2027 öffnen?
ja
9. Kopie im Theme BS5 wird bereits geladen


wenn ysn_slot_booked = 0:
Anzeige wie jetzt, ein grüner runder Punkt vor dem Text

wenn ysn_slot_booked = 1:
Anzeige: Titel: reserviert , ein dunkel oranger Punkt vor dem Text

wenn ysn_slot_booked = 2:
Anzeige: Titel , ein roter Punkt vor dem Text

Mach zudem im Feld des Events einen Mini Button mit Text "Buchen".
Er funktioniert gleich wie wenn man auf das Event klickt.

Gern den Titel wie vorher komplett zeigen und den Button auf einer eigenen Zeile.