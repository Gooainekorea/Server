<?php

/**
 * ====================================================================
 * 데이터베이스 연결 관리 클래스 (싱글턴 패턴, PDO 래퍼)
 * ====================================================================
 * PDO를 사용하여 데이터베이스 연결을 처리하고, 자주 사용되는 DB 작업을 위한
 * 편의 메소드와 트랜잭션 기능을 제공합니다.
 */
class DB {

    private static $instance = null;
    private $pdo; // PDO 연결 객체

    /**
     * 생성자 (private): 외부에서의 직접 생성을 막고 DB 연결을 수행합니다.
     */
    private function __construct() {
        require_once dirname(__DIR__) . '/config/db_config.php';

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // [개선] 스크립트를 바로 중단시키지 않고, 오류 로그를 기록합니다.
            // 실제 운영 환경에서는 이 오류를 잡아서 사용자에게 친화적인 에러 페이지를 보여주어야 합니다.
            error_log('Database Connection Failed: ' . $e->getMessage());
            throw new Exception('데이터베이스에 연결할 수 없습니다. 관리자에게 문의해주세요.');
        }
    }

    /**
     * DB 클래스의 유일한 인스턴스를 반환합니다. (싱글턴 패턴)
     * @return DB
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * [헬퍼] 쿼리를 실행하고 PDOStatement 객체를 반환합니다.
     * @param string $sql SQL 쿼리문
     * @param array $params 바인딩할 파라미터 배열
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query Failed: ' . $e->getMessage() . " (SQL: $sql)");
            throw new Exception('데이터 처리 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * [헬퍼] SELECT 쿼리를 실행하여 한 개의 레코드(row)만 가져옵니다.
     * @param string $sql
     * @param array $params
     * @return array|false 결과가 있으면 연관 배열, 없으면 false 반환
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * [헬퍼] SELECT 쿼리를 실행하여 모든 레코드(row)를 가져옵니다.
     * @param string $sql
     * @param array $params
     * @return array 결과 배열 (결과가 없으면 빈 배열 반환)
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * [헬퍼] INSERT, UPDATE, DELETE 쿼리를 실행하고 영향을 받은 행(row)의 수를 반환합니다.
     * @param string $sql
     * @param array $params
     * @return int 영향을 받은 행의 수
     */
    public function execute($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * [헬퍼] 마지막으로 INSERT된 행의 ID를 반환합니다.
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * [트랜잭션] 트랜잭션을 시작합니다.
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * [트랜잭션] 트랜잭션을 커밋(최종 적용)합니다.
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * [트랜잭션] 트랜잭션을 롤백(모두 취소)합니다.
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /**
     * (기존 방식 유지를 위해) 원본 PDO 객체를 직접 얻고자 할 때 사용합니다.
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    // 싱글턴 유지를 위한 복제 및 unserialize 방지
    private function __clone() {}
    public function __wakeup() {}
}