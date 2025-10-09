<?php

// 의존하는 파일들을 먼저 불러옵니다.
require_once 'BaseController.php';
// [추가] DeviceModel과 UserModel을 명시적으로 포함해주는 것이 안정적입니다.
require_once dirname(__DIR__) . '/models/DeviceModel.php';
require_once dirname(__DIR__) . '/models/UserModel.php';


class AuthController extends BaseController {

    private $userModel;
    private $deviceModel; // [추가] DeviceModel을 사용하기 위한 속성

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->deviceModel = new DeviceModel(); // [추가] DeviceModel 인스턴스 생성
    }

    // login() 메소드는 변경 없음 ...
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/user/myPage');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            // [수정] authenticate 메소드에 전달할 변수명을 user_id로 변경
            $userId = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $house = $this->userModel->authenticate($userId, $password);

            if ($house) {
                $_SESSION['house_id'] = $house[COL_HOUSE_ID];
                $_SESSION['username'] = $house[COL_HOUSE_USER_ID];
                
                $this->setFlashMessage('success', '로그인 되었습니다. 환영합니다, ' . htmlspecialchars($house[COL_HOUSE_USER_NAME]) . '님!');
                $this->redirect('/user/myPage');
            } else {
                $this->setFlashMessage('error', '아이디 또는 비밀번호가 올바르지 않습니다.');
                $this->redirect('/auth/login');
            }
        }

        $this->showView('login');
    }


    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('/user/myPage');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');
            
            $step = $_POST['step'] ?? '';

            switch ($step) {
                case 'agree_terms':
                    $_SESSION['agreed_terms'] = true;
                    $this->redirect('/auth/register');
                    break;

                case 'agree_privacy':
                    if (!isset($_SESSION['agreed_terms'])) {
                        $this->redirect('/auth/register');
                    }
                    $_SESSION['agreed_privacy'] = true;
                    $this->redirect('/auth/register');
                    break;
                
                // [핵심] final_register 로직 전체 수정
                case 'final_register':
                    if (!isset($_SESSION['agreed_terms']) || !isset($_SESSION['agreed_privacy'])) {
                        $this->setFlashMessage('error', '약관 동의 절차를 먼저 완료해주세요.');
                        $this->redirect('/auth/register');
                        return; // [수정] exit 대신 return 사용
                    }

                    // 1. 사용자 입력 값 받기 (변수명 통일)
                    $aptUnit = trim($_POST['apt_unit'] ?? '');
                    $aptNum = trim($_POST['apt_num'] ?? '');
                    $userId = trim($_POST['user_id'] ?? ''); // [수정] user_id 사용
                    $password = $_POST['password'] ?? '';
                    $confirm_password = $_POST['confirm_password'] ?? '';
                    $name = trim($_POST['name'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    
                    $errors = [];

                    // 2. [핵심 로직 1: 사용 가능한 세대인지 확인]
                    $availableHouse = $this->deviceModel->findAvailableHouseByAddress($aptUnit, $aptNum);
                    
                    if (!$availableHouse) {
                        $errors[] = '입력하신 동/호수는 존재하지 않거나 이미 등록된 세대입니다.';
                    }

                    // 3. [핵심 로직 2: 사용자 정보 유효성 검사]
                    if (empty($userId)) $errors[] = '아이디는 필수입니다.';
                    if (empty($password)) $errors[] = '비밀번호는 필수입니다.';
                    if (empty($name)) $errors[] = '이름은 필수입니다.';

                    if (empty($errors)) {
                        if (!preg_match('/^[a-z0-9]{4,20}$/', $userId)) {
                            $errors[] = '아이디는 4~20자의 영문 소문자와 숫자만 사용할 수 있습니다.';
                        }
                        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
                            $errors[] = '비밀번호는 8자 이상이며, 영문, 숫자, 특수문자를 모두 포함해야 합니다.';
                        }
                        if ($password !== $confirm_password) {
                            $errors[] = '비밀번호가 일치하지 않습니다.';
                        }
                        if (strlen($name) < 2 || strlen($name) > 20) {
                            $errors[] = '이름은 2~20자 사이로 입력해주세요.';
                        }
                        if (!empty($phone) && !preg_match('/^[0-9-]{10,13}$/', $phone)) {
                            $errors[] = '유효하지 않은 연락처 형식입니다.';
                        }
                        if ($this->userModel->findByUsername($userId)) {
                            $errors[] = '이미 사용 중인 아이디입니다. 다른 아이디를 입력해주세요.';
                        }
                    }
                    
                    // 4. 최종 오류 확인
                    if (!empty($errors)) {
                        $this->setFlashMessage('error', $errors[0]);
                        $this->redirect('/auth/register');
                        return;
                    }

                    // 5. [핵심 로직 3: 최종 회원가입 처리]
                    // DeviceModel이 찾아준 세대의 House_ID를 사용
                    $houseIdToRegister = $availableHouse[COL_HOUSE_ID];

                    if ($this->userModel->registerUserToHouse($houseIdToRegister, $userId, $password, $name, $phone)) {
                        unset($_SESSION['agreed_terms'], $_SESSION['agreed_privacy']);
                        $this->setFlashMessage('success', '회원가입이 완료되었습니다. 이제 로그인해주세요.');
                        $this->redirect('/auth/login');
                    } else {
                        // 이 경우는 거의 발생하지 않지만 (예: 동시성 문제), 안전장치로 남겨둠
                        $this->setFlashMessage('error', '회원가입 처리 중 데이터베이스 오류가 발생했습니다.');
                        $this->redirect('/auth/register');
                    }
                    break;
            }
        }

        // GET 요청 처리 (변경 없음)
        if (!isset($_SESSION['agreed_terms'])) {
            $this->showView('agree_terms');
        } elseif (!isset($_SESSION['agreed_privacy'])) {
            $this->showView('agree_privacy');
        } else {
            $this->showView('register');
        }
    }
    
    // logout() 메소드는 변경 없음 ...
    public function logout() {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        
        session_start();
        $this->setFlashMessage('success', '성공적으로 로그아웃되었습니다.');
        $this->redirect('/');
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['house_id']);
    }
}