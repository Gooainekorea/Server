<!-- /var/www/views/home.php (최종 수정 버전) -->

<div class="landing-page">
    <div class="hero-section">
        <h1>나만의 IoT 기기를 스마트하게 관리하세요</h1>
        <p class="subtitle">언제 어디서든 당신의 기기 상태를 확인하고, 손쉽게 제어할 수 있는 최적의 솔루션입니다.</p>

        <div class="cta-buttons">
            <!-- [핵심 수정] 로그인 확인 세션 변수를 user_id -> house_id 로 변경 -->
            <?php if (isset($_SESSION['house_id'])): ?>
                
                <!-- 로그인한 사용자에게 보여줄 내용 -->
                <p class="welcome-message">환영합니다! 지금 바로 당신의 기기를 확인해보세요.</p>
                <!-- [링크 수정] /device/my-devices -> /device/list -->
                <a href="/device/list" class="btn btn-primary btn-large">내 기기 목록 보기</a>

            <?php else: ?>

                <!-- 로그인하지 않은 사용자에게 보여줄 내용 -->
                <p class="welcome-message">먼저 로그인하여 모든 기능을 사용해보세요.</p>
                <a href="/auth/login" class="btn btn-primary btn-large">로그인</a>
                <a href="/auth/register" class="btn btn-secondary btn-large">회원가입</a>

            <?php endif; ?>
        </div>
    </div>

    <div class="features-section">
        <h2>주요 기능</h2>
        <div class="features-grid">
            <div class="feature-item">
                <h3>실시간 모니터링</h3>
                <p>기기의 현재 상태와 데이터를 실시간으로 확인하고 즉각적으로 대응할 수 있습니다.</p>
            </div>
            <div class="feature-item">
                <h3>간편한 기기 등록</h3>
                <p>복잡한 절차 없이, 시리얼 번호만으로 새로운 기기를 손쉽게 추가할 수 있습니다.</p>
            </div>
            <div class="feature-item">
                <h3>안전한 데이터 관리</h3>
                <p>당신의 소중한 데이터는 최신 보안 기술을 통해 안전하게 보호됩니다.</p>
            </div>
        </div>
    </div>
</div>