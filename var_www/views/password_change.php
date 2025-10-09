<!-- /var/www/views/password_change.php -->

<div class="form-container">
    <h1>비밀번호 변경</h1>
    <p class="form-description">새로운 비밀번호를 설정합니다. 보안을 위해 현재 비밀번호를 함께 입력해주세요.</p>

    <!-- 
      - action: 폼 데이터가 전송될 URL (/user/changePassword)
      - method: HTTP POST 메소드 사용
    -->
    <form action="/user/changePassword" method="POST">
        
        <!-- CSRF(Cross-Site Request Forgery) 공격 방지를 위한 숨겨진 토큰 필드 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="current_password">현재 비밀번호</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="new_password">새 비밀번호</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_new_password">새 비밀번호 확인</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">비밀번호 변경하기</button>
    
    </form>
    
    <div class="form-footer">
        <a href="/user/myPage">마이페이지로 돌아가기</a>
    </div>
</div>