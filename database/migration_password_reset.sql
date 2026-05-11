-- ================================================
-- TinyTrack — Migration : Mot de passe oublié
-- Module : Authentification
-- A executer dans phpMyAdmin sur la base tinytrack
-- ================================================

USE tinytrack;

-- Table : password_reset
--   token_hash  : SHA-256 du token envoye a l'utilisateur
--                 (on ne stocke JAMAIS le token en clair)
--   expires_at  : validite = 30 min a partir de la creation
--   used        : 1 apres consommation (non reutilisable)
CREATE TABLE IF NOT EXISTS password_reset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
