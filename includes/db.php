<?php
/**
 * PathFinder - Database Connection
 */

require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('
                <div style="font-family: sans-serif; padding: 40px; text-align: center; background: #fff5f5; color: #9b2c2c; max-width: 600px; margin: 80px auto; border-radius: 12px; border: 1px solid #fed7d7;">
                    <h2 style="margin-top:0;">Database Connection Failed</h2>
                    <p>PathFinder could not connect to MySQL server. Please make sure MySQL is started in XAMPP and <code>database/pathfinder.sql</code> has been imported.</p>
                    <p style="font-size: 13px; color: #742a2a;">' . htmlspecialchars($e->getMessage()) . '</p>
                </div>
            ');
        }
    }

    return $pdo;
}

$pdo = get_db();
