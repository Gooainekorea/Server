/**
 * ====================================================================
 * IoT 기기 관리 시스템 - 메인 스크립트 (main.js)
 * ====================================================================
 *
 * 목차:
 * 1.  초기화 함수 (DOMContentLoaded)
 * 2.  삭제 확인 대화상자 (안전한 삭제 처리)
 * 3.  실시간 비밀번호 일치 검사 (회원가입 UX 향상)
 * 4.  알림 메시지 닫기 기능 (플래시 메시지 UX 향상)
 * 5.  (보너스) 모바일 네비게이션 토글 기능
 */

// DOM이 완전히 로드된 후에 모든 스크립트가 실행되도록 보장합니다.
document.addEventListener('DOMContentLoaded', function() {

    // 각 기능별 초기화 함수를 호출합니다.
    initConfirmDeleteForms();
    initPasswordMatchValidation();
    initDismissibleAlerts();
    initMobileNav();

});

/**
 * 2. 삭제 확인 대화상자 (안전한 삭제 처리)
 * --------------------------------------------------------------------
 * 'data-confirm-message' 속성을 가진 모든 폼에 제출 확인창을 추가합니다.
 * PHP 파일의 인라인 onclick 속성을 제거하고 이 스크립트로 대체합니다.
 */
function initConfirmDeleteForms() {
    const confirmForms = document.querySelectorAll('form[data-confirm-message]');
    
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const message = form.getAttribute('data-confirm-message');
            // confirm()에서 '취소'를 누르면 false를 반환하여 폼 제출을 막습니다.
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/**
 * 3. 실시간 비밀번호 일치 검사 (회원가입 UX 향상)
 * --------------------------------------------------------------------
 * register.php 페이지에 있는 비밀번호와 비밀번호 확인 필드의 일치 여부를
 * 사용자가 입력하는 동안 실시간으로 알려줍니다.
 */
function initPasswordMatchValidation() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const statusMessage = document.getElementById('password-match-status');

    // 해당 요소들이 페이지에 존재할 때만 스크립트를 실행합니다.
    if (passwordInput && confirmPasswordInput && statusMessage) {
        const validatePassword = function () {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password.length > 0 || confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    statusMessage.textContent = '비밀번호가 일치합니다.';
                    statusMessage.style.color = 'green';
                } else {
                    statusMessage.textContent = '비밀번호가 일치하지 않습니다.';
                    statusMessage.style.color = 'red';
                }
            } else {
                statusMessage.textContent = '';
            }
        };

        passwordInput.addEventListener('keyup', validatePassword);
        confirmPasswordInput.addEventListener('keyup', validatePassword);
    }
}

/**
 * 4. 알림 메시지 닫기 기능 (플래시 메시지 UX 향상)
 * --------------------------------------------------------------------
 * .alert 클래스를 가진 플래시 메시지 오른쪽에 닫기(X) 버튼을 추가하고,
 * 클릭 시 해당 메시지를 부드럽게 사라지게 합니다.
 */
function initDismissibleAlerts() {
    const alerts = document.querySelectorAll('.alert');

    alerts.forEach(alert => {
        // 닫기 버튼을 생성합니다.
        const closeButton = document.createElement('button');
        closeButton.className = 'btn-close';
        closeButton.innerHTML = '&times;'; // 'X' 모양의 HTML 엔티티
        alert.appendChild(closeButton);

        // 닫기 버튼에 클릭 이벤트를 추가합니다.
        closeButton.addEventListener('click', function() {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300); // 0.3초 후 DOM에서 제거
        });
    });
}

/**
 * 5. (보너스) 모바일 네비게이션 토글 기능
 * --------------------------------------------------------------------
 * 화면 폭이 좁을 때 나타나는 햄버거 메뉴를 클릭하면 네비게이션 메뉴를
 * 보여주거나 숨깁니다.
 */
function initMobileNav() {
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            mainNav.classList.toggle('nav-visible');
        });
    }
}