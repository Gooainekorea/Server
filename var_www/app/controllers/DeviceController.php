<?php

// 의존하는 BaseController 파일을 먼저 불러옵니다.
require_once 'BaseController.php';
// [추가] DeviceModel 경로를 명시적으로 포함해주는 것이 안정적입니다.
require_once dirname(__DIR__) . '/models/DeviceModel.php';


/**
 * 로그인한 사용자의 세대/기기(목록, 상세, 등록, 삭제) 관리를 담당합니다.
 * 수정된 DeviceModel (House 테이블 기반)을 사용하도록 전면 재작성되었습니다.
 */
class DeviceController extends BaseController {

    private $deviceModel;
    private $houseId;    // [수정] 세션에 저장된 세대의 고유 ID (House.House_ID)
    private $username; // [수정] 세션에 저장된 사용자의 로그인 아이디 (House.User_ID)

    public function __construct() {
        parent::__construct(); // 부모 생성자 호출 (세션 시작)

        // [핵심 수정] 이 컨트롤러의 모든 기능은 로그인이 필수입니다.
        // 수정된 isLoggedIn() 메소드를 호출하여 'house_id' 세션 존재 여부를 정확히 확인합니다.
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', '기기를 관리하려면 먼저 로그인해야 합니다.');
            $this->redirect('/auth/login');
        }

        $this->deviceModel = new DeviceModel();
        
        // [핵심 수정] AuthController에서 생성한 세션 변수와 일치시킵니다.
        $this->houseId = $_SESSION['house_id'];   // 세대 테이블의 PK
        $this->username = $_SESSION['username']; // House 테이블의 User_ID와 연결되는 값
    }

    /**
     * 현재 로그인한 사용자의 세대(기기) 목록 페이지를 보여줍니다.
     * URL: /device/list
     */
    public function list() {
        // DeviceModel의 새 메소드를 사용하여 현재 로그인한 사용자의 모든 세대 정보를 가져옵니다.
        // [수정 없음] 이 로직은 이미 username을 사용하고 있어 올바르게 동작합니다.
        $houses = $this->deviceModel->findHousesByWebUserId($this->username);
        $this->showView('my_devices', ['houses' => $houses]);
    }

    /**
     * 특정 세대(기기)의 상세 정보 페이지를 보여줍니다.
     * [보안] 반드시 본인 소유의 기기인지 확인합니다.
     * URL: /device/detail/{houseId}
     */
    public function detail($houseId) {
        // DeviceModel의 새 메소드를 사용하여 소유권을 확인하며 세부 정보를 가져옵니다.
        // [수정 없음] 이 로직은 이미 username을 사용하고 있어 올바르게 동작합니다.
        $house = $this->deviceModel->findHouseByIdAndWebUserId($houseId, $this->username);

        if ($house) {
            // 여기에 추가적으로 해당 세대의 쓰레기 배출 기록 등을 가져오는 로직을 넣을 수 있습니다.
            // $wasteRecords = $this->deviceModel->getWasteRecordsByHouseId($houseId);
            $this->showView('my_device_detail', ['house' => $house]);
        } else {
            // 기기가 존재하지 않거나 내 소유가 아닐 경우, "찾을 수 없음"으로 처리하여 정보 유출을 방지합니다.
            $this->handleNotFound();
        }
    }

    /**
     * 새 기기(세대) 등록 페이지를 보여주거나 등록 요청을 처리합니다.
     * URL: /device/register
     */
    public function register() {
        // POST 요청 처리 (폼 제출 시)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            // [참고] 이 부분은 새로운 회원가입 방식이 아닌,
            // 이미 계정이 있는 사용자가 추가로 기기를 등록하는 로직입니다.
            $houseId = trim($_POST['house_id'] ?? ''); // 기기 고유 ID (시리얼 번호 역할)
            $aptUnit = trim($_POST['apt_unit'] ?? ''); // 동
            $aptNum = trim($_POST['apt_num'] ?? '');   // 호수

            if (empty($houseId) || empty($aptUnit) || empty($aptNum)) {
                $this->setFlashMessage('error', '모든 필드를 정확하게 입력해주세요.');
                $this->redirect('/device/register');
            }

            // 1. DB에 해당 기기(House ID)가 존재하는지 먼저 확인합니다.
            $house = $this->deviceModel->findHouseById($houseId);

            if (!$house) {
                $this->setFlashMessage('error', '존재하지 않는 기기 ID입니다. 관리자에게 문의하세요.');
            } elseif (!empty($house[COL_HOUSE_USER_ID])) {
                // 2. 만약 존재하지만, 이미 다른 사용자에게 등록된 경우
                $this->setFlashMessage('error', '이미 다른 사용자가 등록한 기기입니다.');
            } else {
                // 3. 존재하고, 등록 가능한 상태일 경우 -> 내 계정으로 등록(UPDATE)
                if ($this->deviceModel->registerHouseToUser($this->username, $houseId, $aptUnit, $aptNum)) {
                    $this->setFlashMessage('success', "세대(".htmlspecialchars($aptUnit)."동 ".htmlspecialchars($aptNum)."호)가 성공적으로 등록되었습니다.");
                    $this->redirect('/device/list');
                } else {
                    $this->setFlashMessage('error', '기기 등록 중 서버 오류가 발생했습니다.');
                }
            }
            // 유효성 검사 실패 시, 다시 등록 페이지로 리디렉션
            $this->redirect('/device/register');
        }

        // GET 요청 처리 (페이지 첫 접근 시)
        $this->showView('device_register');
    }
    
    /**
     * 등록된 세대(기기)를 삭제(연결 해제)합니다. (POST 요청으로만 동작)
     * [보안] 반드시 본인 소유의 기기인지 확인 후 처리합니다.
     * URL: /device/delete/{houseId}
     */
    public function delete($houseId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET 방식으로 접근 시 차단
            $this->redirect('/device/list');
        }

        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

        // DeviceModel의 새 메소드는 내부적으로 소유권(username)을 검증합니다.
        // [수정 없음] 이 로직은 이미 username을 사용하고 있어 올바르게 동작합니다.
        if ($this->deviceModel->deleteHouseConnection($houseId, $this->username)) {
            $this->setFlashMessage('success', '기기와의 연결이 성공적으로 해제되었습니다.');
        } else {
            $this->setFlashMessage('error', '기기 연결 해제에 실패했습니다. 해당 기기가 존재하지 않거나 권한이 없습니다.');
        }
        
        $this->redirect('/device/list');
    }

    /**
     * [핵심 추가] 로그인 확인 기준을 'house_id' 세션으로 변경합니다.
     * 부모 클래스(BaseController)의 isLoggedIn() 메소드를 오버라이드(재정의)하여
     * 이 컨트롤러가 올바른 세션 변수를 확인하도록 수정합니다.
     */
    protected function isLoggedIn() {
        return isset($_SESSION['house_id']);
    }
}