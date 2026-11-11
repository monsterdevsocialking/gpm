-- GPM: tabela em falta para guardar mensagens enviadas pelo formulário público.
-- Executar uma única vez depois de importar gpm_mvp_nova.sql.
-- IF NOT EXISTS permite aplicar o patch sem recriar ou apagar dados existentes.

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) DEFAULT NULL,
    subject VARCHAR(180) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_messages_created_at (created_at),
    KEY idx_contact_messages_email (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
