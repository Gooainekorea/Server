<!-- /var/www/views/my_device_detail.php (최종 수정 버전) -->

<div class="device-detail-page">
    
    <!-- 페이지 헤더: 제목과 주요 액션 버튼(목록으로 돌아가기)을 배치 -->
    <div class="page-header-with-action">
        <h1>세대/기기 상세 정보</h1>
        <!-- [링크 수정] /device/my-devices -> /device/list -->
        <a href="/device/list" class="btn btn-secondary">목록으로 돌아가기</a>
    </div>

    <!-- [핵심 수정] 컨트롤러에서 전달받은 $house 변수를 사용하도록 변경하고, 데이터 존재 여부 확인 -->
    <?php if (isset($house) && !empty($house)): ?>

    <!-- 1. 기본 정보 섹션 -->
    <div class="content-section device-info-panel">
        <!-- [수정] 기기 이름을 세대 정보(동/호수)로 변경 -->
        <h2 class="device-title"><?php echo htmlspecialchars($house[COL_HOUSE_APT_UNIT] . '동 ' . $house[COL_HOUSE_APT_NUM] . '호'); ?></h2>
        
        <div class="info-grid">
            <!-- [수정] serial_number -> COL_HOUSE_ID -->
            <div class="info-item">
                <span class="info-label">기기 고유 ID</span>
                <span class="info-value code"><?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?></span>
            </div>
            <!-- [수정] registered_at -> COL_HOUSE_USER_ID (등록일 대신 등록한 사용자 ID 표시) -->
            <div class="info-item">
                <span class="info-label">등록된 사용자 ID</span>
                <span class="info-value"><?php echo htmlspecialchars($house[COL_HOUSE_USER_ID]); ?></span>
            </div>
            <!-- [수정] id -> COL_HOUSE_ID -->
            <div class="info-item">
                <span class="info-label">세대 고유 ID (PK)</span>
                <span class="info-value"><?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?></span>
            </div>
            <!-- [수정] status 컬럼이 없으므로 '정상'으로 고정 표시 -->
            <div class="info-item">
                <span class="info-label">현재 상태</span>
                <span class="info-value">
                    <span class="status-badge status-active">
                        정상
                    </span>
                </span>
            </div>
        </div>
    </div>

    <!-- 2. 실시간 데이터/통계 섹션 (예시) -->
    <div class="content-section">
        <h3>쓰레기 배출 기록 (예시)</h3>
        <p>이곳에 이 세대에서 배출한 쓰레기양, 날짜 등 통계 차트가 표시될 수 있습니다.</p>
        <!-- 이 부분은 나중에 DeviceModel에서 배출 기록을 조회하여 실제로 채울 수 있습니다. -->
        <div class="live-data-placeholder">
            <span>최근 배출량: 150g (어제)</span>
            <span>이번 달 누적: 2.1kg</span>
            <span>평균 배출량: 120g/일</span>
        </div>
    </div>

    <!-- 3. 기기 관리 섹션 (Danger Zone) -->
    <div class="content-section danger-zone">
        <h3>세대/기기 관리</h3>
        <p>이 세대와의 연결을 해제할 수 있습니다. 연결을 해제하면 다른 사용자가 이 기기를 등록할 수 있게 됩니다.</p>
        <div class="action-buttons">
            <!-- [링크 수정] $device['id'] -> $house[COL_HOUSE_ID] -->
            <!-- 참고: 정보 수정 기능은 현재 구현되지 않았으므로 비활성화 처리 또는 링크 제거 가능 -->
            <a href="/device/edit/<?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?>" class="btn btn-secondary disabled" aria-disabled="true">정보 수정 (미구현)</a>
            
            <!-- [폼 수정] action URL 및 버튼 텍스트 변경 -->
            <form action="/device/delete/<?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?>" method="POST" style="display: inline-block;"
                data-confirm-message="정말로 이 세대와의 연결을 해제하시겠습니까?">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button type="submit" class="btn btn-danger">
                    연결 해제하기
                </button>
            </form>
        </div>
    </div>

    <?php else: ?>
        <div class="no-data-message">
            <h2>기기 정보를 불러오는 데 실패했습니다.</h2>
            <p>
                존재하지 않는 기기이거나, 회원님의 계정에 등록된 기기가 아닙니다.
            </p>
            <a href="/device/list" class="btn btn-primary">내 기기 목록으로 돌아가기</a>
        </div>
    <?php endif; ?>
</div>