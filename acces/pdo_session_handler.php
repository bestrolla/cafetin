<?php
require_once __DIR__ . '/../BBDD/BBDD.php';

class PdoSessionHandler implements SessionHandlerInterface {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            data LONGBLOB NOT NULL,
            expires INT NOT NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $this->pdo->exec($sql);
    }

    public function open(string $savePath, string $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string {
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = :id AND expires > :now');
        $stmt->execute([':id' => $id, ':now' => time()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string) $row['data'] : '';
    }

    public function write(string $id, string $data): bool {
        $expires = time() + (int) ini_get('session.gc_maxlifetime');
        $stmt = $this->pdo->prepare('REPLACE INTO sessions (id, data, expires) VALUES (:id, :data, :expires)');
        return $stmt->execute([':id' => $id, ':data' => $data, ':expires' => $expires]);
    }

    public function destroy(string $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function gc(int $maxlifetime): int|false {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE expires < :now');
        return $stmt->execute([':now' => time()]) ? 1 : false;
    }
}
