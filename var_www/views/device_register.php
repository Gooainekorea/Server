<!-- /var/www/views/device_register.php (최종 수정 및 보완 버전) -->

<div class="form-container">
    <h1>새 세대/기기 등록</h1>
    <p class="form-description">
        이미 계정이 있는 사용자가 관리자에게 할당받은 새 기기를 계정에 추가합니다.<br>
        기기 고유 ID는 보통 기기 본체에서 확인할 수 있습니다.
    </p>

    <form action="/device/register" method="POST">
        
        <!-- CSRF 토큰 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <!-- [핵심 수정 1] 누락되었던 'house_id' 입력 필드 추가 -->
        <div class="form-group">
            <label for="house_id">기기 고유 ID</label>
            <input type="text" id="house_id" name="house_id" class="form-control" placeholder="기기에 부착된 고유 ID를 입력하세요" required>
        </div>

        <!-- [수정 2] Placeholder 텍스트를 필드에 맞게 수정 -->
        <div class="form-group">
            <label for="apt_unit">아파트 동</label>
            <input type="text" id="apt_unit" name="apt_unit" class="form-control" placeholder="등록할 세대의 동을 입력하세요 (예: 101)" required>
        </div>

        <!-- [수정 3] Placeholder 수정 및 name 속성의 공백 오타 제거 -->
        <div class="form-group">
            <label for="apt_num">아파트 호수</label>
            <input type="text" id="apt_num" name="apt_num" class="form-control" placeholder="등록할 세대의 호수를 입력하세요 (예: 1004)" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">기기 등록하기</button>
    
    </form>
    
    <!-- [수정 4] 하단 링크의 URL을 올바르게 변경 -->
    <div class="form-footer">
        <a href="/device/list">기기 목록으로 돌아가기</a>
    </div>
</div>