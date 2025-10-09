<?php

/**
 * ====================================================================
 * 통합 세대/기기 모델 (DeviceModel - 최종 수정 버전)
 * ====================================================================
 * 'House' 테이블(세대 정보 및 기기 정보 통합) 및 'WasteRecord' 테이블과
 * 관련된 모든 데이터베이스 작업을 처리합니다.
 *
 * 이 모델은 웹사이트(DeviceController)와 IoT 기기(ApiController) 양쪽의
 * 모든 요청을 처리할 수 있도록 통합되었습니다.
 */
class DeviceModel {

    /**
     * @var DB DB 래퍼 클래스 인스턴스
     */
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /*
    |--------------------------------------------------------------------------
    | 웹사이트 기능 (DeviceController에서 사용)
    |--------------------------------------------------------------------------
    | 로그인한 웹 사용자의 기기(세대) 목록, 상세 정보, 등록, 삭제 등을 처리합니다.
    | 모든 메소드는 반드시 웹 사용자의 ID (User_ID)를 통해 소유권을 확인합니다.
    */

    /**
     * [웹사이트용] 특정 웹 사용자가 소유한 모든 세대(기기) 목록을 조회합니다.
     * (기존 findByUserId 대체)
     * @param string $webUserId 웹사이트에 로그인한 사용자의 아이디 (users.username)
     * @return array 기기 목록 배열 (결과가 없으면 빈 배열)
     */
    public function findHousesByWebUserId($webUserId) {
        $sql = "SELECT * FROM " . TBL_HOUSE . " 
                WHERE " . COL_HOUSE_USER_ID . " = :user_id 
                ORDER BY " . COL_HOUSE_APT_UNIT . " ASC, " . COL_HOUSE_APT_NUM . " ASC";
        return $this->db->fetchAll($sql, [':user_id' => $webUserId]);
    }

    /**
     * [웹사이트용] 특정 세대 ID와 웹 사용자 ID를 함께 사용하여 세대 정보를 조회합니다.
     * 이를 통해 다른 사용자의 기기 정보를 조회하는 것을 원천적으로 차단합니다.
     * (기존 findByIdAndUserId 대체)
     * @param string $houseId 조회할 세대(기기)의 ID (PK)
     * @param string $webUserId 소유권을 확인할 웹 사용자의 아이디
     * @return array|false 결과가 있으면 연관 배열, 없으면 false
     */
    public function findHouseByIdAndWebUserId($houseId, $webUserId) {
        $sql = "SELECT * FROM " . TBL_HOUSE . " 
                WHERE " . COL_HOUSE_ID . " = :house_id AND " . COL_HOUSE_USER_ID . " = :user_id";
        return $this->db->fetch($sql, [':house_id' => $houseId, ':user_id' => $webUserId]);
    }
    
    /**
     * [웹사이트용] 고유 ID로 세대(기기)가 이미 존재하는지 확인합니다.
     * 기기 등록 시 중복을 방지하기 위해 사용됩니다.
     * @param string $houseId 확인할 세대(기기)의 고유 ID
     * @return array|false
     */
    public function findHouseById($houseId) {
        $sql = "SELECT " . COL_HOUSE_ID . ", " . COL_HOUSE_USER_ID . " 
                FROM " . TBL_HOUSE . " WHERE " . COL_HOUSE_ID . " = :house_id";
        return $this->db->fetch($sql, [':house_id' => $houseId]);
    }

    /**
     * [웹사이트용] 새로운 기기(세대)를 등록합니다.
     * 실제로는 관리자가 미리 등록한 기기를 웹 사용자가 자신의 계정에 연결(소유권 업데이트)하는 방식입니다.
     * (기존 createDevice 대체)
     * @param string $webUserId 소유자 아이디
     * @param string $houseId 등록할 기기의 고유 ID
     * @param string $aptUnit 사용자가 지정한 동
     * @param string $aptNum 사용자가 지정한 호수
     * @return bool 성공 시 true, 실패 시 false
     */
    public function registerHouseToUser($webUserId, $houseId, $aptUnit, $aptNum) {
        $sql = "UPDATE " . TBL_HOUSE . " 
                SET " . COL_HOUSE_USER_ID . " = :user_id, 
                    " . COL_HOUSE_APT_UNIT . " = :apt_unit, 
                    " . COL_HOUSE_APT_NUM . " = :apt_num 
                WHERE " . COL_HOUSE_ID . " = :house_id AND " . COL_HOUSE_USER_ID . " IS NULL";
        
        $params = [
            ':user_id'  => $webUserId,
            ':apt_unit' => $aptUnit,
            ':apt_num'  => $aptNum,
            ':house_id' => $houseId
        ];
        
        return $this->db->execute($sql, $params) > 0;
    }

       /**
     * [회원가입용] 동/호수로 등록 가능한(미사용) 세대 정보를 조회합니다.
     *
     * 이 메소드는 사용자가 회원가입 폼에 입력한 '동'과 '호수'가 관리자에 의해
     * 사전에 등록된 세대 정보와 일치하는지 확인합니다.
     * 또한, 해당 세대가 아직 다른 사용자에게 등록되지 않았는지(User_ID가 NULL인지)
     * 확인하여 '사용 가능' 여부를 판단합니다.
     *
     * @param string $aptUnit 사용자가 입력한 아파트 동
     * @param string $aptNum  사용자가 입력한 아파트 호수
     * @return array|false   사용 가능한 세대일 경우 해당 세대의 모든 정보(House_ID 포함)를 배열로 반환하고,
     *                       존재하지 않거나 이미 다른 사용자가 등록한 세대일 경우 false를 반환합니다.
     */
    public function findAvailableHouseByAddress($aptUnit, $aptNum) {
        // SQL 쿼리: 동(APT_UNIT)과 호수(APT_NUM)가 일치하고,
        // 사용자 아이디(User_ID)가 비어있는(IS NULL) 행을 찾습니다.
        $sql = "SELECT * FROM " . TBL_HOUSE . " 
                WHERE " . COL_HOUSE_APT_UNIT . " = :apt_unit 
                  AND " . COL_HOUSE_APT_NUM . " = :apt_num 
                  AND " . COL_HOUSE_USER_ID . " IS NULL"; // [핵심] 아직 등록되지 않은 세대만 조회
        
        // DB 클래스를 통해 쿼리를 실행하고 결과를 반환합니다.
        // 결과가 있으면 연관 배열, 없으면 fetch() 메소드는 false를 반환합니다.
        return $this->db->fetch($sql, [':apt_unit' => $aptUnit, ':apt_num' => $aptNum]);
    }

    

    /**
     * [웹사이트용] 특정 사용자의 기기(세대)를 삭제(연결 해제)합니다.
     * 실제 데이터를 삭제하는 대신, 사용자 연결 정보만 초기화합니다.
     * (기존 deleteDevice 대체)
     * @param string $houseId 삭제할 기기 ID
     * @param string $webUserId 소유자 ID (소유권 확인용)
     * @return bool 성공 시 true, 실패 시 false
     */
    public function deleteHouseConnection($houseId, $webUserId) {
        // 중요: 데이터를 영구 삭제하는 대신, User_ID와 세대 정보만 NULL로 만들어
        // 기기는 남겨두고 다른 사용자가 재등록할 수 있도록 합니다.
        // 관련된 WasteRecord도 삭제하려면 별도 로직이 필요합니다.
        $sql = "UPDATE " . TBL_HOUSE . " 
                SET " . COL_HOUSE_USER_ID . " = NULL, 
                    " . COL_HOUSE_APT_UNIT . " = NULL,
                    " . COL_HOUSE_APT_NUM . " = NULL,
                    " . COL_HOUSE_DEVICE_PUBLIC_KEY . " = NULL
                WHERE " . COL_HOUSE_ID . " = :house_id AND " . COL_HOUSE_USER_ID . " = :user_id";

        return $this->db->execute($sql, [':house_id' => $houseId, ':user_id' => $webUserId]) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | API 기능 (ApiController에서 사용)
    |--------------------------------------------------------------------------
    | IoT 기기(쓰레기통)와의 직접 통신을 위한 메소드들입니다.
    */

    /**
     * [API용 - 최종 수정 버전] 기기의 고유 ID로 세대 정보를 조회합니다.
     * ApiController에서 기기의 인증 및 공개키 조회를 위해 사용합니다.
     * @param string $deviceUniqueId 기기의 고유 식별자 (아두이노에서 보낸 Device_ID)
     * @return array|false 결과가 있으면 세대 ID와 공개키 정보, 없으면 false
     */
    public function findHouseForApiAuth($deviceUniqueId) {
        
        // [핵심 수정] 검색 대상을 새로 추가한 Device_ID 컬럼으로 변경합니다.
        $sql = "SELECT " . COL_HOUSE_ID . ", " . COL_HOUSE_DEVICE_PUBLIC_KEY . " 
                FROM " . TBL_HOUSE . " 
                WHERE " . COL_HOUSE_DEVICE_ID . " = :device_id"; // <-- 이 부분이 바뀝니다.
        
        return $this->db->fetch($sql, [':device_id' => $deviceUniqueId]);
    }

    /**
     * [API용] 특정 세대(기기)의 공개키를 데이터베이스에 저장(또는 업데이트)합니다.
     * @param string $houseId 공개키를 저장할 세대의 ID
     * @param string $publicKey PEM 형식의 공개키 문자열
     * @return bool 성공 시 true, 실패 시 false
     */
    public function storeDevicePublicKey($houseId, $publicKey) {
        $sql = "UPDATE " . TBL_HOUSE . " 
                SET " . COL_HOUSE_DEVICE_PUBLIC_KEY . " = :public_key 
                WHERE " . COL_HOUSE_ID . " = :house_id";
        
        return $this->db->execute($sql, [':public_key' => $publicKey, ':house_id' => $houseId]) > 0;
    }

    /**
     * [API용] 쓰레기 배출량 데이터를 'WasteRecord' 테이블에 기록합니다.
     * @param string $houseId 데이터를 보낸 기기가 속한 세대의 ID
     * @param float $amount 측정된 쓰레기 양 (무게)
     * @param string|null $ipAddr 기기의 IP 주소
     * @return bool 데이터 삽입 성공 시 true, 실패 시 false
     */
    public function saveWasteData($houseId, $amount, $ipAddr = null) {
        $sql = "INSERT INTO " . TBL_WASTE_RECORD . " (
                    " . COL_WASTE_HOUSE_ID . ", 
                    " . COL_WASTE_AMOUNT . ", 
                    " . COL_WASTE_RECORD_DATE . ", 
                    " . COL_WASTE_RECORD_TIME . ", 
                    " . COL_WASTE_IP_ADDR . "
                ) VALUES (:house_id, :amount, CURDATE(), CURTIME(), :ip_addr)";
        
        $params = [
            ':house_id' => $houseId,
            ':amount'   => $amount,
            ':ip_addr'  => $ipAddr
        ];
        
        return $this->db->execute($sql, $params) > 0;
    }
}