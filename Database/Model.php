<?php

namespace Support\Database;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public function save(): void
    {
        $pdo = Database::getConnection();
        $props = get_object_vars($this);
        $primaryKey = static::$primaryKey;
        $table = static::$table;

        if (isset($this->$primaryKey)) {
            // Update
            $columns = array_keys($props);
            $columns = array_filter($columns, fn($col) => $col !== $primaryKey);
            $assignments = implode(', ', array_map(fn($col) => "$col = :$col", $columns));
            $sql = "UPDATE $table SET $assignments WHERE $primaryKey = :$primaryKey";
        } else {
            // Insert
            $columns = array_keys($props);
            $placeholders = implode(', ', array_map(fn($col) => ":$col", $columns));
            $columnList = implode(', ', $columns);
            $sql = "INSERT INTO $table ($columnList) VALUES ($placeholders)";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($props);

        if (!isset($this->$primaryKey)) {
            $this->$primaryKey = $pdo->lastInsertId();
        }
    }

    public static function find($id): ?static
    {
        $pdo = Database::getConnection();
        $table = static::$table;
        $primaryKey = static::$primaryKey;

        $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKey = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        $model = new static();
        foreach ($data as $key => $value) {
            if (property_exists($model, $key)) {
                $model->$key = $value;
            }
        }

        return $model;
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $stmt = $pdo->query("SELECT * FROM $table");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($data) {
            $model = new static();
            foreach ($data as $key => $value) {
                if (property_exists($model, $key)) {
                    $model->$key = $value;
                }
            }
            return $model;
        }, $rows);
    }

    public function delete(): bool
    {
        $pdo = Database::getConnection();
        $table = static::$table;
        $primaryKey = static::$primaryKey;

        if (!isset($this->$primaryKey)) {
            return false;
        }

        $stmt = $pdo->prepare("DELETE FROM $table WHERE $primaryKey = ?");
        return $stmt->execute([$this->$primaryKey]);
    }
}
