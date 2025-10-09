<!-- /var/www/views/my_page.php (최종 수정 버전) -->

<h1>마이페이지</h1>
<p>회원님의 정보를 확인하고 관리할 수 있습니다.</p>

<!-- [핵심 수정] 컨트롤러에서 전달받은 $house 변수를 사용하도록 변경 -->
<?php if (isset($house) && !empty($house)): ?>

<!-- 1. 내 정보 조회 섹션 -->
<div class="content-section">
    <h2>내 정보</h2>
    <div class="info-list">
        <!-- [수정] $user['username'] -> $house[COL_HOUSE_USER_ID] -->
        <div class="info-item">
            <span class="info-label">아이디</span>
            <span class="info-value"><?php echo htmlspecialchars($house[COL_HOUSE_USER_ID]); ?></span>
        </div>
        <!-- [추가] DB에 있는 '이름' 정보 표시 -->
        <div class="info-item">
            <span class="info-label">이름 (세대주)</span>
            <span class="info-value"><?php echo htmlspecialchars($house[COL_HOUSE_USER_NAME]); ?></span>
        </div>
        <!-- [수정] $user['email'] -> $house[COL_HOUSE_USER_PHONE] (DB에 email이 없으므로 연락처로 변경) -->
        <div class="info-item">
            <span class="info-label">연락처</span>
            <span class="info-value"><?php echo htmlspecialchars($house[COL_HOUSE_USER_PHONE] ?? '등록되지 않음'); ?></span>
        </div>
        <!-- [수정] $user['created_at'] -> 세대 정보 (동/호수)로 변경 -->
        <div class="info-item">
            <span class="info-label">세대 정보</span>
            <span class="info-value"><?php echo htmlspecialchars($house[COL_HOUSE_APT_UNIT] . '동 ' . $house[COL_HOUSE_APT_NUM] . '호'); ?></span>
        </div>
    </div>
</div>

<!-- 2. 계정 관리 링크 섹션 -->
<div class="content-section">
    <h2>계정 관리</h2>
    <div class="action-links">
        <a href="/user/edit" class="btn btn-secondary">내 정보 수정</a>
        <a href="/user/changePassword" class="btn btn-secondary">비밀번호 변경</a>
    </div>
</div>

<!-- 3. 회원 탈퇴 폼 (Danger Zone) -->
<div class="content-section danger-zone">
    <h2>회원 탈퇴</h2>
    <p>
        회원 탈퇴 시 모든 기기 정보와 계정 데이터가 영구적으로 삭제되며, 복구할 수 없습니다.<br>
        탈퇴를 원하시면 현재 비밀번호를 입력 후 아래 버튼을 클릭해주세요.
    </p>
    
    <form action="/user/unregister" method="POST" class="unregister-form" 
          data-confirm-message="정말로 회원 탈퇴를 진행하시겠습니까? 모든 정보가 영구적으로 삭제됩니다.">
        
        <!-- CSRF 토큰은 모든 폼에 필수 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="form-group">
            <label for="password">비밀번호 확인</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="현재 비밀번호를 입력하세요" required>
        </div>
        
        <button type="submit" class="btn btn-danger">회원 탈퇴하기</button>
    </form>
</div>

<?php else: ?>
    <div class="no-data-message">
        <h2>사용자 정보를 불러오는 데 실패했습니다.</h2>
        <p>
            세션이 만료되었거나 오류가 발생했습니다. 다시 로그인해주세요.
        </p>
        <a href="/auth/login" class="btn btn-primary">로그인 페이지로 이동</a>
    </div>
<?php endif; ?>