<?php
/**
 * TinyTrack — Google OAuth (Google Identity Services)
 *
 * GOOGLE_CLIENT_ID est defini dans config/secrets.php (gitignored).
 * Copiez config/secrets.example.php -> config/secrets.php pour le configurer.
 *
 * Comment obtenir votre Client ID :
 *   1. https://console.cloud.google.com -> Credentials -> Create OAuth Client ID
 *   2. Application type : Web application
 *   3. Authorized JavaScript origins : http://localhost
 *   4. Authorized redirect URIs : http://localhost/TinyTrack/View/auth/login.php
 */
require_once __DIR__ . '/secrets.php';

/**
 * Verifies a Google ID token (JWT) by calling Google's tokeninfo endpoint.
 * Google itself validates the signature, expiry and issuer — we just check
 * that the audience matches OUR client ID (prevents tokens issued for other apps).
 *
 * Returns an associative array of claims on success, or false on failure.
 * Relevant claims used : email, email_verified, given_name, family_name, sub.
 */
function verifyGoogleToken($idToken) {
    if (empty($idToken)) return false;

    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

    $ctx = stream_context_create(['http' => ['timeout' => 6, 'ignore_errors' => true]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return false;

    $claims = json_decode($raw, true);
    if (!is_array($claims) || isset($claims['error'])) return false;

    // Audience check : the token must have been issued for OUR app.
    if (($claims['aud'] ?? '') !== GOOGLE_CLIENT_ID) return false;

    // Only trust verified Google emails.
    if (empty($claims['email']) || ($claims['email_verified'] ?? '') !== 'true') return false;

    return $claims;
}
