smartclean.kro.ko

## 디렉토리 구조
index.php가 유일한 진입점으로 거기서 특정 경로 요청시 그곳의 페이지가 로드된다.

/var/www/                    # 최상위 루트
├── .htaccess                # 아파치 설정 파일 (보안 및 라우팅)
│
├── app/                     # [비공개] 애플리케이션 핵심 로직
│   ├── config/              #   설정 파일
│   │   └── db_config.php    #     DB 접속 정보
│   │
│   ├── controllers/         #   컨트롤러 (요청 처리 및 지휘)
│   │   ├── BaseController.php #     공통 기능 부모 컨트롤러
│   │   ├── AuthController.php #     인증 (로그인, 회원가입 등) 담당
│   │   ├── UserController.php #     사용자 정보 (마이페이지 등) 담당
│   │   ├── DeviceController.php #   기기 관리 담당
│   │   ├── ApiController.php  #     전용 API 엔드포인트
│   │   └── PageController.php #     정적 페이지 담당
│   │
│   ├── models/              #   모델 (데이터베이스 작업 전담)
│   │   ├── UserModel.php    #     사용자 관련 로직
│   │   └── DeviceModel.php  #     기기 관련 로직
│   │
│   └── lib/                 #   라이브러리
│       └── db.php           #     DB 연결 관리 클래스
│
├── html/                    # [공개] 웹 서버 DocumentRoot
│   ├── index.php            #   프론트 컨트롤러 (모든 요청의 진입점)
│   └── assets/              #   정적 리소스 (CSS, JS, 이미지)
│       ├── css/
│       │   └── style.css    #       메인 스타일시트 (최종 보완 버전)
│       ├── js/
│       │   └── main.js      #       메인 스크립트 (최종 보완 버전)
│       └── images/
│           └── logo.png
│
└── views/                   # [비공개] 템플릿 (HTML/PHP) 파일
    ├── header.php           # 공통 상단 (수정 완료)
    ├── footer.php           # 공통 하단
    ├── home.php             # 메인 랜딩 페이지
    ├── error.php            # 오류 페이지
    │
    ├── login.php            # [인증] 로그인 폼
    ├── agree_terms.php      # [인증] (신규) 이용약관 동의 페이지
    ├── agree_privacy.php    # [인증] (신규) 개인정보처리방침 동의 페이지
    ├── register.php         # [인증] 최종 회원정보 입력 폼 (수정 완료)
    │
    ├── my_page.php          # [사용자] 마이페이지 (수정 완료)
    ├── my_page_edit.php     # [사용자] 내 정보 수정 폼
    ├── password_change.php  # [사용자] 비밀번호 변경 폼
    │
    ├── product_info.php     # [정적] 제품 소개 페이지
    ├── terms.php            # [정적] 이용약관 내용 페이지
    ├── privacy.php          # [정적] 개인정보처리방침 내용 페이지
    │
    ├── my_devices.php       # [기기] 내 기기 목록 (수정 완료)
    ├── my_device_detail.php # [기기] 기기 상세 정보 (수정 완료)
    └── device_register.php  # [기기] 새 기기 등록 폼

