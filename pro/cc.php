<?php

require_once 'app/Mage.php'; $app = Mage::app(); if($app != null) { $cache = $app->getCache(); if($cache != null) { $cache->clean(); } }
