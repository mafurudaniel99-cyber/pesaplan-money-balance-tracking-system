<?php
/**
 * PesaPlan — Database connection (MySQLi OOP).
 * Singleton wrapper that exposes a shared prepared-statement helper.
 */

declare(strict_types=1);

namespace App\Core;

final class Database
{
    private static ?\mysqli $instance = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function connection(): \mysqli
    {
        if (self::$instance === null) {
            $c = self::$config;
            \mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                $mysqli = new \mysqli(
                    $c['host'],
                    $c['user'],
                    $c['pass'],
                    $c['name'],
                    $c['port'] ?? 3306
                );
            } catch (\mysqli_sql_exception $e) {
                // Never expose credentials to the user.
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('Hitilafu kwenye seva. Tafadhali jaribu tena baadaye.');
            }
            $mysqli->set_charset($c['charset'] ?? 'utf8mb4');
            $tz = $c['timezone'] ?? '+03:00';
            $mysqli->query("SET time_zone = '" . $mysqli->real_escape_string($tz) . "'");
            self::$instance = $mysqli;
        }
        return self::$instance;
    }

    /**
     * Run a prepared statement.
     * Usage: Database::query('SELECT * FROM users WHERE id = ?', [$id])
     * Returns a mysqli_result on success, true on non-SELECT, false on failure.
     */
    public static function query(string $sql, array $params = [])
    {
        $mysqli = self::connection();
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            error_log('SQL prepare failed: ' . $mysqli->error . ' | SQL: ' . $sql);
            throw new \RuntimeException('Hitilafu kwenye utayarishaji wa swali.');
        }
        if (!empty($params)) {
            $types = '';
            $bindings = [];
            foreach ($params as $p) {
                if (is_int($p)) {
                    $types .= 'i';
                } elseif (is_float($p)) {
                    $types .= 'd';
                } elseif (is_string($p)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bindings[] = $p;
            }
            $stmt->bind_param($types, ...$bindings);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false && $stmt->errno === 0) {
            // Query was non-SELECT (INSERT/UPDATE/DELETE)
            $affected = $stmt->affected_rows;
            $insertId = $stmt->insert_id;
            $stmt->close();
            return (object)['affected_rows' => $affected, 'insert_id' => $insertId];
        }
        if ($result === false) {
            error_log('SQL execute failed: ' . $stmt->error);
            $stmt->close();
            throw new \RuntimeException('Hitilafu kwenye utekelezaji wa swali.');
        }
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch a single row as associative array or null.
     */
    public static function one(string $sql, array $params = []): ?array
    {
        $rows = self::query($sql, $params);
        if (!is_array($rows) || empty($rows)) {
            return null;
        }
        return $rows[0];
    }

    /**
     * Fetch a single scalar value (first column of first row) or null.
     */
    public static function scalar(string $sql, array $params = [])
    {
        $row = self::one($sql, $params);
        if (!$row) {
            return null;
        }
        return reset($row) !== false ? reset($row) : null;
    }

    public static function insertId(): int
    {
        return (int) self::connection()->insert_id;
    }

    public static function escape(string $value): string
    {
        return self::connection()->real_escape_string($value);
    }

    /**
     * Run a callable inside a SQL transaction.
     */
    public static function transaction(callable $fn)
    {
        $mysqli = self::connection();
        $mysqli->begin_transaction();
        try {
            $result = $fn();
            $mysqli->commit();
            return $result;
        } catch (\Throwable $e) {
            $mysqli->rollback();
            throw $e;
        }
    }
}
