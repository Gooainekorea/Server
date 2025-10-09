<?php

// 의존하는 BaseController 파일을 먼저 불러옵니다.
require_once 'BaseController.php';

/**
 * ====================================================================
 * 사용자 정보 컨트롤러 (UserController - House 테이블 통합 버전)
 * ====================================================================
 * 로그인한 사용자의 정보(마이페이지, 수정, 비밀번호 변경, 탈퇴)를 관리합니다.
 * 모든 로직은 세션의 'house_id'를 기준으로 동작하도록 수정되었습니다.
 */
class UserController extends BaseController {

    private $userModel;
    private $houseId;  // [수정] userId -> houseId
    private $username;

    public function __construct() {
        parent::__construct();

        // [수정] 로그인 확인은 이제 'house_id' 세션의 존재 여부로 판단합니다.
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('error', '로그인이 필요한 서비스입니다.');
            $this->redirect('/auth/login');
        }

        $this->userModel = new UserModel();
        // [수정] 세션에서 user_id 대신 house_id를 가져옵니다.
        $this->houseId = $_SESSION['house_id'];
        $this->username = $_SESSION['username'];
    }

    /**
     * 마이페이지를 보여줍니다.
     * URL: /user/myPage
     */
    public function myPage() {
        // [수정] houseId를 사용하여 현재 세대(사용자) 정보를 조회합니다.
        $house = $this->userModel->findById($this->houseId);

        if (!$house) {
            $this->setFlashMessage('error', '사용자 정보를 찾는 데 실패했습니다. 다시 로그인해주세요.');
            $this->redirect('/auth/logout');
        }
        
        // [수정] 뷰로 전달하는 데이터 키를 'user' -> 'house'로 변경하여 명확화합니다.
        $this->showView('my_page', ['house' => $house]);
    }

    /**
     * 사용자 정보(이름, 연락처) 수정 페이지를 보여주거나 요청을 처리합니다.
     * URL: /user/edit
     */
    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            // [수정] email 대신 이름(name)과 연락처(phone)를 받습니다.
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (empty($name)) { // 이름은 필수값으로 가정
                $this->setFlashMessage('error', '이름은 비워둘 수 없습니다.');
            } else {
                $dataToUpdate = [
                    COL_HOUSE_USER_NAME => $name,
                    COL_HOUSE_USER_PHONE => $phone
                ];

                if ($this->userModel->updateUser($this->houseId, $dataToUpdate)) {
                    $this->setFlashMessage('success', '회원 정보가 성공적으로 수정되었습니다.');
                    $this->redirect('/user/myPage');
                } else {
                    $this->setFlashMessage('error', '정보 수정 중 오류가 발생했습니다.');
                }
            }
            $this->redirect('/user/edit');
        }

        // GET 요청 시
        $house = $this->userModel->findById($this->houseId);
        $this->showView('my_page_edit', ['house' => $house]);
    }

    /**
     * 비밀번호 변경 페이지를 보여주거나 요청을 처리합니다.
     * URL: /user/changePassword
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_new_password'] ?? '';

            // [수정] 비밀번호 검증을 위해 House 테이블에서 정보를 조회합니다.
            $house = $this->userModel->findByUsername($this->username);
            
            // [수정] 비밀번호 컬럼 상수를 사용하여 검증합니다.
            if (!$house || !password_verify($currentPassword, $house[COL_HOUSE_USER_PW])) {
                $this->setFlashMessage('error', '현재 비밀번호가 일치하지 않습니다.');
            } elseif (empty($newPassword) || $newPassword !== $confirmPassword) {
                $this->setFlashMessage('error', '새 비밀번호가 일치하지 않거나 비어있습니다.');
            } elseif (strlen($newPassword) < 8) {
                $this->setFlashMessage('error', '새 비밀번호는 8자 이상이어야 합니다.');
            } else {
                // [수정] houseId를 사용하여 비밀번호를 변경합니다.
                if ($this->userModel->changePassword($this->houseId, $newPassword)) {
                    $this->setFlashMessage('success', '비밀번호가 성공적으로 변경되었습니다.');
                    $this->redirect('/user/myPage');
                } else {
                    $this->setFlashMessage('error', '비밀번호 변경 중 오류가 발생했습니다.');
                }
            }
            $this->redirect('/user/changePassword');
        }
        $this->showView('password_change');
    }

    /**
     * 회원 탈퇴(사용자 정보 초기화)를 처리합니다.
     * URL: /user/unregister
     */
    public function unregister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/myPage');
        }
        
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $house = $this->userModel->findByUsername($this->username);

        if (!$house || !password_verify($password, $house[COL_HOUSE_USER_PW])) {
            $this->setFlashMessage('error', '비밀번호가 일치하지 않아 탈퇴할 수 없습니다.');
            $this->redirect('/user/myPage');
        }

        // [수정] houseId를 사용하여 사용자 정보를 초기화합니다.
        if ($this->userModel->deleteUser($this->houseId)) {
            session_destroy();
            session_start();
            $this->setFlashMessage('success', '회원 탈퇴가 완료되었습니다. 이용해주셔서 감사합니다.');
            $this->redirect('/');
        } else {
            $this->setFlashMessage('error', '회원 탈퇴 처리 중 오류가 발생했습니다.');
            $this->redirect('/user/myPage');
        }
    }
    
    /**
     * [상속 오버라이드] 로그인 확인 기준을 'house_id' 세션으로 변경합니다.
     */
    protected function isLoggedIn() {
        return isset($_SESSION['house_id']);
    }
}