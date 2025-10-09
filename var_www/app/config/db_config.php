<?php
// 데이터베이스 연결 정보
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'poly');
define('DB_NAME', 'smartclean');
define('DB_CHARSET', 'utf8');

// 데이터베이스 연결 정보
// define('DB_HOST', '0000');
// define('DB_USER', '0000');
// define('DB_PASS', '00000');
// define('DB_NAME', '00000');
// define('DB_CHARSET', 'utf8');

// --- 2. 테이블 및 컬럼 이름 상수 ---

// [ House 테이블: 세대 정보 ]
define('TBL_HOUSE', 'House');
define('COL_HOUSE_ID', 'House_ID'); // Primary Key 자동증가
define('COL_HOUSE_DEVICE_PUBLIC_KEY', 'Device_Public_Key'); // 쓰레기통 공개키
define('COL_HOUSE_DEVICE_ID', 'Device_ID');//쓰레기통 장치 ID
define('COL_HOUSE_APT_UNIT', 'APT_UNIT');//아파트 동
define('COL_HOUSE_APT_NUM', 'APT_NUM');//아파트 호수
define('COL_HOUSE_USER_NAME', 'User_Name');//세대주 이름
define('COL_HOUSE_USER_PHONE', 'User_Phone');//세대주 연락처
define('COL_HOUSE_USER_ID', 'User_ID');//세대주 아이디 (로그인 ID)
define('COL_HOUSE_USER_PW', 'User_PW');//세대주 비밀번호 (해시)
define('COL_HOUSE_MEMBER_NUM', 'Member_NUM');//세대원 수
define('COL_HOUSE_ROLE', 'ROLE');//권한 (admin/user/EMPTY)

// [ WasteRecord 테이블: 배출 기록 ]
define('TBL_WASTE_RECORD', 'WasteRecord');//테이블 이름
define('COL_WASTE_ID', 'Waste_ID'); // Primary Key 자동증가
define('COL_WASTE_HOUSE_ID', 'House_ID'); // Foreign Key
define('COL_WASTE_AMOUNT', 'Amount');//배출량
define('COL_WASTE_RECORD_DATE', 'Record_DATE');//배출 날짜
define('COL_WASTE_RECORD_TIME', 'Record_TIME');//배출 시간
define('COL_WASTE_IP_ADDR', 'IP_addr');//배출 기록을 보낸 기기의 IP 주소
define('COL_WASTE_MAC_ADDR', 'MAC_addr');//배출 기록을 보낸 기기의 MAC 주소 (선택적)

// [ Billing 테이블: 요금/관리비 ]
define('TBL_BILLING', 'Billing');//테이블 이름
define('COL_BILLING_ID', 'Bill_ID');// Primary Key 자동증가
define('COL_BILLING_HOUSE_ID', 'House_ID'); // Foreign Key
define('COL_BILLING_PERIOD', 'Period');//요금 청구 기간 (예: '2024-06')
define('COL_BILLING_TOTAL_BILL', 'Total_Bill');//총 요금
define('COL_BILLING_STATUS', 'Status');//납부 상태 (예: 'paid', 'unpaid')


?>