<?php
// Auch im Admin laden: die Slot-Anmeldung läuft über admin-ajax.php
require_once __DIR__ . "/jubilaeum-slots.php";

if (!is_admin()){
    require_once __DIR__ . "/public/functions.php";
}