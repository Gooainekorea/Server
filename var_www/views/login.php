<!-- /var/www/views/login.php -->

<div class="form-container">
    <h1>로그인</h1>
    <p class="form-description">서비스를 이용하시려면 로그인해주세요.</p>

    <!-- 
      - action: 폼 데이터가 전송될 URL (/auth/login)
      - method: HTTP POST 메소드 사용
    -->
    <form action="/auth/login" method="POST">
        
        <!-- CSRF(Cross-Site Request Forgery) 공격 방지를 위한 숨겨진 토큰 필드 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="username">아이디</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">로그인</button>
    
    </form>
    
    <div class="form-footer">
        <p>아직 회원이 아니신가요? <a href="/auth/register">회원가입</a></p>
    </div>
</div>