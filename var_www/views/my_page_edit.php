<!-- /var/www/views/my_page_edit.php (최종 수정 버전) -->

<div class="form-container">
    <h1>내 정보 수정</h1>
    <p class="form-description">회원님의 정보를 수정할 수 있습니다.</p>
    
    <!-- [핵심 수정] 컨트롤러에서 전달받은 $house 변수를 사용하고, 데이터 존재 여부 확인 -->
    <?php if (isset($house) && !empty($house)): ?>

    <!-- 
      - action: 폼 데이터가 전송될 URL (/user/edit) - 올바름
      - method: HTTP POST 메소드 사용
    -->
    <form action="/user/edit" method="POST">
        
        <!-- CSRF(Cross-Site Request Forgery) 공격 방지를 위한 숨겨진 토큰 필드 -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="username">아이디 (수정 불가)</label>
            <!-- 
              - [수정] value: $user['username'] -> $house[COL_HOUSE_USER_ID]로 변경
            -->
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($house[COL_HOUSE_USER_ID]); ?>" readonly>
        </div>

        <!-- [핵심 수정] email 필드를 삭제하고, name(이름)과 phone(연락처) 필드로 교체 -->
        <div class="form-group">
            <label for="name">이름 (세대주)</label>
            <!-- 
              - name="name" 으로 컨트롤러와 일치
              - value: $house[COL_HOUSE_USER_NAME]로 현재 이름 표시
            -->
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($house[COL_HOUSE_USER_NAME]); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">연락처</label>
            <!-- 
              - name="phone" 으로 컨트롤러와 일치
              - value: $house[COL_HOUSE_USER_PHONE]로 현재 연락처 표시
            -->
            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($house[COL_HOUSE_USER_PHONE]); ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block">정보 수정하기</button>
    
    </form>
    
    <div class="form-footer">
        <!-- [링크 수정] /user/my-page -> /user/myPage -->
        <a href="/user/myPage">마이페이지로 돌아가기</a>
    </div>

    <?php else: ?>
        <div class="no-data-message">
            <h2>사용자 정보를 불러오는 데 실패했습니다.</h2>
            <p>
                수정할 사용자 정보를 찾을 수 없습니다. 다시 로그인 후 시도해주세요.
            </p>
            <a href="/auth/login" class="btn btn-primary">로그인 페이지로 이동</a>