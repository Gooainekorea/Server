<!-- /var/www/views/agree_privacy.php -->

<div class="static-content-page registration-step">
    <h1>개인정보 처리방침 동의</h1>
    <p class="step-description">
        회원가입의 마지막 동의 절차입니다.<br>
        회원님의 소중한 개인정보를 어떻게 처리하는지에 대한 방침을 확인 후 동의해주세요.
    </p>

    <!-- 개인정보 처리방침 내용을 표시하는 스크롤 박스 -->
    <div class="terms-box">
        <?php
        // privacy.php 파일의 내용을 그대로 여기에 포함시켜 보여줍니다.
        include __DIR__ . '/privacy.php';
        ?>
    </div>

    <!-- 
      - action: 폼 데이터가 전송될 URL (/auth/register)
      - AuthController의 register() 메소드가 이 요청을 받아서 처리합니다.
    -->
    <form action="/auth/register" method="POST" class="agree-form">
        
        <!-- CSRF 토큰 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <!-- 
          [핵심] 컨트롤러가 어떤 단계의 요청인지 식별할 수 있도록 'step' 값을 전송합니다.
          이 폼은 '개인정보 처리방침 동의' 단계이므로 'agree_privacy' 값을 보냅니다.
        -->
        <input type="hidden" name="step" value="agree_privacy">

        <div class="checkbox-wrapper">
            <input type="checkbox" id="agree_checkbox" required>
            <label for="agree_checkbox">위 개인정보 처리방침의 모든 내용을 확인하였으며, 이에 동의합니다.</label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-large">동의하고 회원가입 계속하기</button>
    </form>
</div>