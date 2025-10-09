<!-- /var/www/views/header.php (최종 수정 버전) -->
<?php
// 모든 페이지에서 세션을 사용하기 위해 session_start()를 호출합니다.
// BaseController에서 이미 호출했을 수 있지만, header에서 한 번 더 확인하는 것이 안전합니다.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- $pageTitle 변수가 설정되었다면 해당 값을, 없다면 기본 제목을 사용합니다. -->
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'IoT 기기 관리 시스템'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="page-header">
    <div class="container">
        <div class="logo">
            <a href="/">
                <img src="/assets/images/logo.png" alt="사이트 로고">
                <span>IoT Manager</span>
            </a>
        </div>

        <!-- [추가] 모바일 화면에서 메뉴를 토글할 햄버거 버튼 -->
        <button class="nav-toggle" aria-label="메뉴 열기">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- [변경] nav 태그에 'main-nav' 클래스를 추가하여 JS가 제어할 수 있도록 함 -->
        <nav class="main-nav">
            <ul>
                <li><a href="/">홈</a></li>
                <!-- [링크 수정] /product-info -> /page/productInfo -->
                <li><a href="/page/productInfo">제품소개</a></li>
                
                <!-- [핵심 수정] 로그인 확인 세션 변수를 user_id -> house_id 로 변경 -->
                <?php if (isset($_SESSION['house_id'])): ?>
                    <!-- 로그인 상태일 때 보여줄 메뉴 -->
                    <!-- [링크 수정] /device/my-devices -> /device/list -->
                    <li><a href="/device/list">내 기기 목록</a></li>
                    <!-- [링크 수정] /user/my-page -> /user/myPage -->
                    <li><a href="/user/myPage">마이페이지</a></li>
                    <li>
                        <form action="/auth/logout" method="POST" style="display: inline;">
                            <button type="submit" class="btn-link">로그아웃</button>
                        </form>
                    </li>
                <?php else: ?>
                    <!-- 로그아웃 상태일 때 보여줄 메뉴 -->
                    <li><a href="/auth/login">로그인</a></li>
                    <li><a href="/auth/register">회원가입</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
</header>

<main class="container">
    <?php
    if (isset($flash_messages) && !empty($flash_messages)) {
        echo '<div class="flash-messages">';
        foreach ($flash_messages as $message) {
            $type = htmlspecialchars($message['type']);
            $text = htmlspecialchars($message['message']);
            echo "<div class='alert alert-{$type}'>{$text}</div>";
        }
        echo '</div>';
    }
    ?>
    <!-- 각 페이지의 고유 콘텐츠가 이어서 렌더링됩니다. -->