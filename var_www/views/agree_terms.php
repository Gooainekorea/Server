<!-- /var/www/views/agree_terms.php -->

<div class="static-content-page registration-step">
    <h1>서비스 이용약관 동의</h1>
    <p class="step-description">
        회원가입을 위해 서비스 이용약관을 주의 깊게 읽고 동의해주세요.<br>
        이는 원활한 서비스 제공을 위한 필수 절차입니다.
    </p>

    <!-- 약관 내용을 표시하는 스크롤 박스 -->
    <div class="terms-box">
        <?php
        // terms.php 파일의 내용을 그대로 여기에 포함시켜 보여줍니다.
        // 이렇게 하면 약관 내용이 변경될 때 terms.php만 수정하면 되므로 관리가 용이합니다.
        include __DIR__ . '/terms.php';
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
          이 폼은 '이용약관 동의' 단계이므로 'agree_terms' 값을 보냅니다.
        -->
        <input type="hidden" name="step" value="agree_terms">

        <div class="checkbox-wrapper">
            <input type="checkbox" id="agree_checkbox" required>
            <label for="agree_checkbox">위 서비스 이용약관의 모든 내용을 확인하였으며, 이에 동의합니다.</label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-large">동의하고 다음 단계로</button>
    </form>
</div>