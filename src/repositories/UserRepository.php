<?php

namespace Src\Repositories;

use PDO;
use Src\Config\Database;

class UserRepository
{
    private PDO $db;

    public function __construct(array $cfg)
    {
        $this->db = Database::conn($cfg);
    }

    public function paginate($page, $per, $search = null, $sortBy = 'id', $sortDir = 'DESC')
    {
        $off = ($page - 1) * $per;

        // Pastikan hanya kolom yang diizinkan yang bisa di-sort
        $allowedSort = ['id', 'name', 'email', 'created_at'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'id';
        }

        // Pastikan arah sort valid
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        // Base query
        $sql = 'FROM users WHERE 1=1';
        $params = [];

        // Jika ada pencarian
        if (!empty($search)) {
            $sql .= ' AND (name LIKE :search OR email LIKE :search)';
            $params[':search'] = "%$search%";
        }

        // Hitung total data
        $countStmt = $this->db->prepare("SELECT COUNT(*) $sql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Ambil data dengan limit dan offset
        $dataSql = "SELECT id, name, email, role, created_at, updated_at 
                    $sql ORDER BY $sortBy $sortDir LIMIT :per OFFSET :off";
        $stmt = $this->db->prepare($dataSql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':per', (int)$per, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$off, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per,
                'last_page' => max(1, (int)ceil($total / $per)),
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir
            ]
        ];
    }

    public function find($id)
    {
        $s = $this->db->prepare('SELECT id, name, email, role, created_at, updated_at 
                                 FROM users WHERE id=?');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create($name, $email, $hash, $role = 'user')
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) 
                                     VALUES (?, ?, ?, ?)');
            $s->execute([$name, $email, $hash, $role]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $this->find($id);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $name, $email, $role)
    {
        $s = $this->db->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
        $s->execute([$name, $email, $role, $id]);
        return $this->find($id);
    }

    public function delete($id)
    {
        $s = $this->db->prepare('DELETE FROM users WHERE id=?');
        $s->execute([$id]);
        return $s->rowCount() > 0;
    }
}
