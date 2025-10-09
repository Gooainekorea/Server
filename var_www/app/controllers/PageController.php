<?php

// 의존하는 BaseController 파일을 먼저 불러옵니다.
require_once 'BaseController.php';

/**
 * ====================================================================
 * 정적 페이지 컨트롤러 (PageController)
 * ====================================================================
 * 웹사이트의 정적인 페이지(메인, 제품 소개, 약관 등) 렌더링을 담당합니다.
 * 이 컨트롤러는 일반적으로 별도의 모델(Model)이 필요 없습니다.
 */
class PageController extends BaseController {

    public function __construct() {
        // 부모 클래스의 생성자를 호출하여 세션을 시작합니다.
        parent::__construct();
    }

    /**
     * 웹사이트의 메인 페이지 (홈)를 보여줍니다.
     * URL: /
     * (index.php의 라우팅 규칙에 의해 /page/home으로 매핑됩니다)
     */
    public function home() {
        // $pageTitle 변수를 설정하여 header.php에서 사용할 수 있도록 합니다.
        $data = [
            'pageTitle' => '환영합니다! 스마트한 음식물 처리의 시작'
        ];
        $this->showView('home', $data);
    }

    /**
     * '제품 소개' 페이지를 보여줍니다.
     * URL: /page/productInfo
     */
    public function productInfo() {
        $data = [
            'pageTitle' => '제품 소개 - 혁신적인 기술'
        ];
        $this->showView('product_info', $data);
    }

    /**
     * '이용 약관' 페이지를 보여줍니다.
     * URL: /page/terms
     */
    public function terms() {
        $data = [
            'pageTitle' => '이용약관'
        ];
        $this->showView('terms'); // 약관 페이지 뷰 파일 (예: terms.php)
    }

    /**
     * '개인정보처리방침' 페이지를 보여줍니다.
     * URL: /page/privacy
     */
    public function privacy() {
        $data = [
            'pageTitle' => '개인정보처리방침'
        ];
        $this->showView('privacy'); // 개인정보처리방침 페이지 뷰 파일 (예: privacy.php)
    }
}