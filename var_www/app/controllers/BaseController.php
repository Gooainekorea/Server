<?php

/**
 * ====================================================================
 * 기본 컨트롤러 (Base Controller - 최종 버전)
 * ====================================================================
 * 모든 컨트롤러가 상속받는 부모 클래스입니다.
 * 이 클래스는 직접 인스턴스화되지 않으며, 자식 컨트롤러들에게
 * 공통 기능(세션, 뷰 렌더링, 리디렉션, 보안, UX)을 제공하는 역할을 합니다.
 */
abstract class BaseController { // 'abstract' 키워드 추가: 직접 객체로 만들 수 없음을 명시

    public function __construct() {
        // [공통 기능 1: 세션 관리]
        // 어떤 컨트롤러가 호출되든, 세션이 시작되지 않은 상태라면 자동으로 시작합니다.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * [공통 기능 2: 인증 확인]
     * 로그인 상태인지 확인합니다.
     * 자식 컨트롤러에서만 사용해야 하므로 'protected'로 선언합니다.
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * [공통 기능 3: 뷰 렌더링]
     * 뷰 파일을 로드하여 페이지를 구성합니다.
     * @param string $viewName 뷰 파일 이름
     * @param array $data 뷰에 전달할 데이터 (예: 사용자 정보, 기기 목록)
     */
    protected function showView($viewName, $data = []) {
        // 모든 뷰에 플래시 메시지를 자동으로 전달하는 로직
        $data['flash_messages'] = $this->getFlashMessages();
        
        // CSRF 토큰을 모든 폼에서 사용할 수 있도록 자동으로 생성하여 뷰에 전달
        $data['csrf_token'] = $this->generateCsrfToken();
        
        extract($data);
        require_once VIEWS_PATH . '/header.php';
        require_once VIEWS_PATH . '/' . $viewName . '.php';
        require_once VIEWS_PATH . '/footer.php';
    }

    /**
     * [공통 기능 4: 페이지 이동]
     * 특정 URL로 리디렉션합니다.
     * @param string $url
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * [공통 기능 5: 보안 - CSRF 토큰 생성]
     * CSRF 공격을 방어하기 위한 토큰을 생성하고 세션에 저장합니다.
     */
    protected function generateCsrfToken() {
        // 토큰이 없거나 비어있을 때만 새로 생성하여 불필요한 생성을 막습니다.
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * [공통 기능 6: 보안 - CSRF 토큰 검증]
     * 폼 제출 시 전달된 토큰이 세션에 저장된 토큰과 일치하는지 검증합니다.
     * @param string $token 사용자가 제출한 토큰
     */
    protected function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            error_log('CSRF token mismatch detected.');
            die('잘못된 요청이 감지되었습니다. 페이지를 새로고침한 후 다시 시도해주세요.');
        }
        // [중요] 한번 사용한 토큰은 즉시 제거하여 재사용 공격(replay attack)을 방지합니다.
        unset($_SESSION['csrf_token']);
    }
    
    /**
     * [공통 기능 7: UX - 플래시 메시지 설정]
     * 다음 페이지 요청에 '한 번만' 보여줄 메시지를 세션에 저장합니다.
     * (예: "성공적으로 등록되었습니다.")
     * @param string $type 메시지 종류 (예: 'success', 'error')
     * @param string $message 표시할 메시지
     */
    protected function setFlashMessage($type, $message) {
        $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
    }
    
    /**
     * [공통 기능 8: UX - 플래시 메시지 가져오기]
     * 세션에 저장된 플래시 메시지를 가져온 후, 바로 삭제하여 중복 표시를 방지합니다.
     * @return array
     */
    private function getFlashMessages() {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * [공통 기능 9: 오류 처리]
     * 404 Not Found 페이지를 일관된 방식으로 보여줍니다.
     */
    protected function handleNotFound() {
        http_response_code(404);
        require_once VIEWS_PATH . '/error.php';
        exit();
    }
}