<!-- /var/www/views/error.php -->

<div class="error-page-container">
    <div class="error-icon">
        <!-- 간단한 느낌표 아이콘 SVG 또는 이미지/폰트 아이콘을 사용할 수 있습니다. -->
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
    </div>
    
    <h1>
        <?php
        // $errorTitle 변수가 있으면 사용하고, 없으면 기본 메시지를 출력합니다.
        echo isset($errorTitle) ? htmlspecialchars($errorTitle) : '요청하신 페이지를 찾을 수 없습니다.';
        ?>
    </h1>
    
    <p class="error-description">
        <?php
        // $errorMessage 변수가 있으면 사용하고, 없으면 기본 메시지를 출력합니다.
        echo isset($errorMessage) ? htmlspecialchars($errorMessage) : '존재하지 않는 주소를 입력하셨거나, 요청하신 페이지의 주소가 변경/삭제되어 찾을 수 없습니다.';
        ?>
    </p>
    
    <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
</div>