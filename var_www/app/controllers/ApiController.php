<?php

/**
 * ====================================================================
 * API 컨트롤러 (ApiController)
 * ====================================================================
 * 아두이노 등 IoT 기기와의 통신(API 요청)을 전담하여 처리합니다.
 * 모든 응답은 JSON 형식으로 반환됩니다.
 */

// 의존하는 파일들을 불러옵니다.
require_once 'BaseController.php';
require_once dirname(__DIR__) . '/models/DeviceModel.php';

class ApiController extends BaseController {

    private $deviceModel;

    public function __construct() {
        // BaseController의 생성자를 호출하지만, 세션은 API 통신에 직접 사용되지 않습니다.
        parent::__construct();
        
        // 이 컨트롤러는 DeviceModel과 상호작용합니다.
        $this->deviceModel = new DeviceModel();
        
        // [중요] 이 컨트롤러의 모든 응답은 JSON 형식임을 클라이언트에게 알립니다.
        header('Content-Type: application/json');
    }

    /**
     * [엔드포인트] 기기의 공개키를 등록(저장)합니다.
     * URL: /api/register-key
     * 메소드: POST
     * 파라미터: device_id, public_key
     */
    public function registerKey() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(405, 'error', 'Method Not Allowed: 이 엔드포인트는 POST 요청만 허용합니다.');
        }

        $deviceUniqueId = $_POST['device_id'] ?? null;
        $publicKey = $_POST['public_key'] ?? null;

        if (!$deviceUniqueId || !$publicKey) {
            $this->sendJsonResponse(400, 'error', 'Bad Request: device_id 또는 public_key 파라미터가 누락되었습니다.');
        }

        // DB에 해당 ID를 가진 세대(기기)가 등록되어 있는지 먼저 확인합니다.
        $house = $this->deviceModel->findHouseForApiAuth($deviceUniqueId);
        if (!$house) {
            $this->sendJsonResponse(403, 'Forbidden: 등록되지 않은 기기입니다. 관리자에게 문의하여 먼저 세대를 등록해주세요.');
        }

        // 공개키를 DB에 저장합니다.
        if ($this->deviceModel->storeDevicePublicKey($house[COL_HOUSE_ID], $publicKey)) {
            $this->sendJsonResponse(200, 'success', '공개키가 성공적으로 등록되었습니다.');
        } else {
            $this->sendJsonResponse(500, 'error', 'Internal Server Error: 공개키를 저장하는 중 서버 오류가 발생했습니다.');
        }
    }

    /**
     * [엔드포인트] 기기로부터 받은 쓰레기 배출량 데이터를 처리합니다.
     * URL: /api/data
     * 메소드: POST
     * 파라미터: device_id, amount, signature
     */
    public function handleData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(405, 'error', 'Method Not Allowed: 이 엔드포인트는 POST 요청만 허용합니다.');
        }

        // 1. 아두이노로부터 데이터 및 서명 수신
        $deviceUniqueId = $_POST['device_id'] ?? null;
        $amount = $_POST['amount'] ?? null;
        $timestamp = $_POST['timestamp'] ?? null; // [추가] api 재전송 공격 방어
        $signature = $_POST['signature'] ?? null;

        // // 2. 필수 데이터 유효성 검사
        // if (!$deviceUniqueId || $amount === null || !$signature) {
        //     $this->sendJsonResponse(400, 'error', 'Bad Request: 파라미터가 모두 필요합니다.');
        // }

        // [수정] timestamp가 누락되었는지 확인하는 로직 추가
        if (!$deviceUniqueId || $amount === null || !$timestamp || !$signature) {
            $this->sendJsonResponse(400, 'error', 'Bad Request: device_id, amount, timestamp, signature 파라미터가 모두 필요합니다.');
        }

        // [신규 추가] 타임스탬프 유효성 검사 (재전송 공격 방어)
        $serverTime = time(); // 현재 서버의 UNIX 타임스탬프
        $requestTime = (int)$timestamp;
        $timeDifference = abs($serverTime - $requestTime);
        // 예: 요청 시간이 서버 시간과 3분(180초) 이상 차이 나면 유효하지 않은 요청으로 간주
        if ($timeDifference > 180) {
            $this->sendJsonResponse(408, 'error', 'Request Timeout: 요청 시간이 만료되었습니다.');
        }
        // 3. 기기 인증: DB에서 기기 정보(공개키 포함) 조회
        $house = $this->deviceModel->findHouseForApiAuth($deviceUniqueId);
        if (!$house) {
            $this->sendJsonResponse(403, 'Forbidden: 등록되지 않은 기기입니다.');
        }



        // 4. 서명 검증 (무결성 및 발신자 확인)
        $publicKey = $house[COL_HOUSE_DEVICE_PUBLIC_KEY];
        if (empty($publicKey)) {
            $this->sendJsonResponse(401, 'Unauthorized: 공개키가 등록되지 않았습니다. 기기를 재부팅하여 키를 먼저 등록해주세요.');
        }

        // 아두이노에서 서명했던 원본 데이터 문자열을 서버에서 똑같이 재구성합니다.
        // [중요] 아두이노에서 보낸 순서와 이름(키)이 정확히 일치해야 합니다!
        // $dataToVerify = COL_WASTE_HOUSE_ID . "=" . $deviceUniqueId . "&" . COL_WASTE_AMOUNT . "=" . $amount;
        // [수정] 아두이노에서 서명했던 원본 데이터 문자열을 서버에서 똑같이 재구성
        // [중요] 아두이노에서 보낸 순서와 이름이 정확히 일치해야 합니다! timestamp 추가!
        $dataToVerify = COL_WASTE_HOUSE_ID . "=" . $deviceUniqueId . "&" . 
                        COL_WASTE_AMOUNT . "=" . $amount . "&" .
                        "timestamp=" . $timestamp; // [추가] 타임스탬프를 서명 검증 대상에 포함

        // 아두이노에서 Base64로 인코딩된 서명을 디코딩합니다.
        $decodedSignature = base64_decode($signature);
        if ($decodedSignature === false) {
            $this->sendJsonResponse(400, 'error', 'Bad Request: signature가 유효한 Base64 형식이 아닙니다.');
        }

        // OpenSSL을 사용하여 서명을 검증합니다.
        $isVerified = openssl_verify($dataToVerify, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($isVerified !== 1) {
            // isVerified가 1이 아니면 (0: 검증 실패, -1: 오류) 실패 처리
            $this->sendJsonResponse(401, 'Unauthorized: 서명이 유효하지 않습니다. 데이터가 위변조되었거나 발신자가 다릅니다.');
        }

        // 5. 모든 검증 통과! 데이터를 DB에 최종 기록
        $ipAddr = $_SERVER['REMOTE_ADDR'] ?? null; // 요청을 보낸 기기의 IP 주소
        
        if ($this->deviceModel->saveWasteData($house[COL_HOUSE_ID], $amount, $ipAddr)) {
            $this->sendJsonResponse(200, 'success', '데이터가 성공적으로 저장되었습니다.');
        } else {
            $this->sendJsonResponse(500, 'error', 'Internal Server Error: 데이터를 저장하는 중 서버 오류가 발생했습니다.');
        }
    }

    /**
     * JSON 형식의 응답을 생성하고 전송한 뒤, 스크립트 실행을 종료하는 헬퍼 함수
     * @param int $statusCode HTTP 상태 코드 (예: 200, 400, 404, 500)
     * @param string $status 응답 상태 ('success' 또는 'error')
     * @param string $message 응답 메시지
     * @param array $data 함께 보낼 추가 데이터 (선택 사항)
     */
    private function sendJsonResponse($statusCode, $status, $message, $data = []) {
        http_response_code($statusCode);
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
}
