<?php
// Core
foreach (glob(__DIR__ . '/core/*.php') as $file) {
    require_once $file;
}

// Custom
foreach (glob(__DIR__ . '/custom/*.php') as $file) {
    require_once $file;
}