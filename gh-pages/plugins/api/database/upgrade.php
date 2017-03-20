<?php
function api_upgrade_database() {
    $db = db();
    $db->query("ALTER TABLE  `users` ADD  `gcm_token` TEXT");
}