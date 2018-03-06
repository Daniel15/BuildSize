<?php

require(__DIR__ . '/../vendor/autoload.php');
\VCR\VCR::configure()->enableLibraryHooks(array('curl'));
\VCR\VCR::turnOn();
\VCR\VCR::configure()
  ->setCassettePath(__DIR__ . '/fixtures/http/')
  ->setMode('once');
