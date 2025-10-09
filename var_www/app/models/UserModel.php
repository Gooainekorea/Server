<?php

/**
 * ====================================================================
 * 사용자 모델 (UserModel - House 테이블 통합 버전)
 * ====================================================================
 * 'House' 테이블을 사용하여 웹사이트 사용자 인증 및 정보 관리를 처리합니다.
 * 이 모델은 'users' 테이블을 더 이상 사용하지 않습니다.
 */
class UserModel {

    /**
     * @var DB DB 래퍼 클래스 인스턴스
     */
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * 세대(House)의 고유 ID(PK)로 특정 사용자(세대) 정보를 조회합니다.
     * @param string $houseId 조회할 세대의 House_ID
     * @return array|false 결과가 있으면 연관 배열, 없으면 false
     */
    public function findById($houseId) {
        // [보안] 비밀번호(User_PW)는 제외하고 조회합니다.
        $sql = "SELECT " . implode(', ', [
                   COL_HOUSE_ID, COL_HOUSE_USER_ID, COL_HOUSE_USER_NAME,
                   COL_HOUSE_USER_PHONE, COL_HOUSE_APT_UNIT, COL_HOUSE_APT_NUM, COL_HOUSE_ROLE
               ]) . " FROM " . TBL_HOUSE . " WHERE " . COL_HOUSE_ID . " = :house_id";
        return $this->db->fetch($sql, [':house_id' => $houseId]);
    }

    /*
     * [삭제됨] findHouseID() 메소드는 삭제되었습니다.
     * 동/호수로 세대를 찾는 기능은 '세대(기기)'의 정보이므로 DeviceModel의 역할입니다.
     * AuthController는 DeviceModel::findAvailableHouseByAddress()를 사용해야 합니다.
     */

    /**
     * 사용자 아이디(User_ID)로 특정 사용자(세대) 정보를 조회합니다.
     * @param string $userId 조회할 사용자의 User_ID
     * @return array|false 결과가 있으면 연관 배열, 없으면 false
     */
    public function findByUsername($userId) { // [수정] 파라미터 변수명 변경
        // 로그인 인증 시에는 비밀번호까지 포함하여 모든 정보를 조회해야 합니다.
        $sql = "SELECT * FROM " . TBL_HOUSE . " WHERE " . COL_HOUSE_USER_ID . " = :user_id";
        return $this->db->fetch($sql, [':user_id' => $userId]);
    }

    /**
     * 사용자 인증을 처리합니다. (로그인 시 사용)
     * @param string $userId (User_ID)
     * @param string $password (User_PW)
     * @return array|false 인증 성공 시 해당 세대(House) 정보 배열, 실패 시 false
     */
    public function authenticate($userId, $password) { // [수정] 파라미터 변수명 변경
        $house = $this->findByUsername($userId);
        // User_PW 필드에 저장된 해시와 입력된 비밀번호를 비교합니다.
        if ($house && password_verify($password, $house[COL_HOUSE_USER_PW])) {
            return $house;
        }
        return false;
    }

    /**
     * [핵심 수정] 특정 세대(House)에 새로운 사용자 정보를 연결(등록)합니다.
     *
     * 이 메소드는 Controller가 DeviceModel을 통해 '사용 가능'하다고 확인한
     * 특정 'houseId'를 받아, 해당 ID를 가진 행에 사용자 정보를 UPDATE합니다.
     *
     * @param string $houseId 등록할 세대의 고유 ID (Controller가 미리 찾아준 값)
     * @param string $userId  생성할 사용자 아이디
     * @param string $password 비밀번호
     * @param string $name     사용자 이름
     * @param string $phone    연락처
     * @return bool 성공 시 true, 실패 시 false
     */
    public function registerUserToHouse($houseId, $userId, $password, $name, $phone) {
        // [보안] 비밀번호는 반드시 해싱하여 저장합니다.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // [수정] 동/호수를 조건으로 찾는 것이 아니라, 전달받은 houseId로 정확히 한 집을 지정하여
        // 사용자 정보만 UPDATE 합니다.
        $sql = "UPDATE " . TBL_HOUSE . " SET "
             . COL_HOUSE_USER_ID . " = :user_id, "
             . COL_HOUSE_USER_PW . " = :password, "
             . COL_HOUSE_USER_NAME . " = :name, "
             . COL_HOUSE_USER_PHONE . " = :phone "
             . "WHERE " . COL_HOUSE_ID . " = :house_id AND " . COL_HOUSE_USER_ID . " IS NULL"; // 안전장치

        $params = [
            ':user_id'  => $userId,
            ':password' => $hashedPassword,
            ':name'     => $name,
            ':phone'    => $phone,
            ':house_id' => $houseId // [수정] Controller로부터 받은 houseId를 사용
        ];
        
        // execute() 메소드는 영향을 받은 행의 수를 반환하므로, 0보다 크면 성공입니다.
        return $this->db->execute($sql, $params) > 0;
    }

    /**
     * 사용자(세대) 정보를 수정합니다.
     * @param string $houseId 수정할 세대의 House_ID
     * @param array $data 수정할 데이터의 연관 배열 (예: ['User_Name' => '새이름'])
     * @return bool 성공 시 true, 실패 시 false
     */
    public function updateUser($houseId, $data) {
        $setClauses = [];
        $params = [':house_id' => $houseId];
        $allowedFields = [COL_HOUSE_USER_NAME, COL_HOUSE_USER_PHONE]; // 수정 가능한 필드 목록

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $setClauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($setClauses)) return true;

        $sql = "UPDATE " . TBL_HOUSE . " SET " . implode(', ', $setClauses) . " WHERE " . COL_HOUSE_ID . " = :house_id";
        return $this->db->execute($sql, $params) >= 0;
    }

    /**
     * 사용자의 비밀번호를 변경합니다.
     * @param string $houseId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($houseId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE " . TBL_HOUSE . " SET " . COL_HOUSE_USER_PW . " = :password WHERE " . COL_HOUSE_ID . " = :house_id";
        return $this->db->execute($sql, [':password' => $hashedPassword, ':house_id' => $houseId]) > 0;
    }
    
    /**
     * [개념 변경] 특정 사용자를 삭제(회원 탈퇴)합니다.
     * 'House' row를 DELETE하는 대신, 연결된 사용자 정보만 NULL로 초기화합니다.
     * @param string $houseId 탈퇴할 세대의 House_ID
     * @return bool 성공 시 true, 실패 시 false
     */
    public function deleteUser($houseId) {
        $sql = "UPDATE " . TBL_HOUSE . " SET "
             . COL_HOUSE_USER_ID . " = NULL, "
             . COL_HOUSE_USER_PW . " = NULL, "
             . COL_HOUSE_USER_NAME . " = NULL, "
             . COL_HOUSE_USER_PHONE . " = NULL, "
             . COL_HOUSE_DEVICE_PUBLIC_KEY . " = NULL " // 공개키도 초기화
             . "WHERE " . COL_HOUSE_ID . " = :house_id";
        
        return $this->db->execute($sql, [':house_id' => $houseId]) > 0;
    }
}