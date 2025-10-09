<!-- /var/www/views/register.php (최종 수정 및 보완 버전) -->

<div class="form-container">
    <h1>회원정보 입력</h1>
    <p class="form-description">
        모든 약관에 동의하셨습니다. 이제 마지막으로 서비스 이용에 필요한 정보를 입력해주세요.
    </p>

    <form action="/auth/register" method="POST" id="registrationForm">
        
        <!-- CSRF 토큰 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <!-- '최종 회원가입 처리' 단계임을 알리는 hidden input -->
        <input type="hidden" name="step" value="final_register">

        <!-- [핵심 수정 1] house_id 입력을 삭제하고, apt_unit(동)과 apt_num(호수) 입력으로 변경 -->
        <div class="form-group">
            <label for="apt_unit">아파트 동</label>
            <input type="text" id="apt_unit" name="apt_unit" class="form-control" placeholder="예: 101" required>
        </div>

        <div class="form-group">
            <label for="apt_num">아파트 호수</label>
            <input type="text" id="apt_num" name="apt_num" class="form-control" placeholder="예: 1004" required>
        </div>

        <!-- [핵심 수정 2] 필드명을 username -> user_id 로 변경 -->
        <div class="form-group">
            <label for="user_id">아이디</label>
            <input type="text" id="user_id" name="user_id" class="form-control" placeholder="4~20자의 영문 소문자, 숫자" required>
        </div>
        
        <div class="form-group">
            <label for="name">이름 (세대주)</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="세대주 성함을 입력하세요" required>
        </div>
        
        <div class="form-group">
            <label for="phone">연락처</label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="연락처를 입력하세요 (선택)">
        </div>

        <div class="form-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="영문, 숫자, 특수문자 포함 8자 이상" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">비밀번호 확인</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="비밀번호를 한 번 더 입력하세요" required>
            <!-- [UX 보완] 비밀번호 일치 여부를 실시간으로 보여줄 영역 -->
            <div id="password-match-status" class="form-text"></div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">가입 완료하기</button>
    
    </form>
    
    <div class="form-footer">
        <p>이미 계정이 있으신가요? <a href="/auth/login">로그인</a></p>
    </div>
</div>

<!-- [UX 보완] 실시간 비밀번호 일치 확인을 위한 JavaScript 코드 추가 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const statusDiv = document.getElementById('password-match-status');

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password || confirmPassword) { // 둘 중 하나라도 입력값이 있을 때만 메시지 표시
            if (password === confirmPassword) {
                statusDiv.textContent = '비밀번호가 일치합니다.';
                statusDiv.style.color = 'green';
            } else {
                statusDiv.textContent = '비밀번호가 일치하지 않습니다.';
                statusDiv.style.color = 'red';
            }
        } else {
            statusDiv.textContent = ''; // 둘 다 비어있으면 메시지 삭제
        }
    }

    // 키를 입력할 때마다 함수를 호출하여 실시간으로 확인
    passwordInput.addEventListener('keyup', checkPasswordMatch);
    confirmPasswordInput.addEventListener('keyup', checkPasswordMatch);
});
</script>