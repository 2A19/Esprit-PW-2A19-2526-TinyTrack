<?php
/**
 * OpenAI config (module Gestion Reclamation).
 * Cle externalisee dans config/secrets.php (gitignored).
 */
require_once __DIR__ . '/secrets.php';
return [
    'api_key' => defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '',
    'model'   => defined('OPENAI_MODEL')   ? OPENAI_MODEL   : 'gpt-4o-mini',
];
