<!-- /var/www/views/my_devices.php (최종 수정 버전) -->

<div class="page-header-with-action">
    <h1>내 세대/기기 목록</h1>
    <!-- [수정됨] 버튼의 링크와 텍스트를 새로운 등록 페이지에 맞게 변경 -->
    <a href="/device/register" class="btn btn-primary">새 세대/기기 등록하기</a>
</div>

<p>
    현재 회원님 계정에 등록된 모든 세대(음식물 쓰레기통)의 목록입니다.<br>
    세대 정보를 클릭하여 상세 내역을 확인하거나 관리할 수 있습니다.
</p>

<!-- [수정됨] 확인 변수를 $devices -> $houses 로 변경 -->
<?php if (isset($houses) && !empty($houses)): ?>
    
    <!-- 1. 등록된 세대가 있는 경우: 테이블로 목록 표시 -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>동/호수 (세대 정보)</th>
                    <th>기기 고유 ID</th>
                    <th>상태</th>
                    <th>사용자 ID</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <!-- [수정됨] 루프 변수를 $device -> $house 로 변경 -->
                <?php foreach ($houses as $house): ?>
                    <tr>
                        <td>
                            <!-- [수정됨] 링크와 표시되는 텍스트를 House 테이블 컬럼에 맞게 변경 -->
                            <a href="/device/detail/<?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?>">
                                <strong><?php echo htmlspecialchars($house[COL_HOUSE_APT_UNIT] . '동 ' . $house[COL_HOUSE_APT_NUM] . '호'); ?></strong>
                            </a>
                        </td>
                        <!-- [수정됨] serial_number -> House_ID 로 변경 -->
                        <td><?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?></td>
                        <td>
                            <!-- 
                                참고: 'status' 컬럼은 현재 DB 스키마에 없습니다. 
                                만약 House 테이블에 status 컬럼을 추가한다면 이 코드가 동작합니다.
                                없다면, '정상'과 같은 고정된 텍스트를 표시할 수 있습니다.
                            -->
                            <span class="status-badge status-active">
                                <?php echo isset($house['status']) ? htmlspecialchars($house['status']) : '정상'; ?>
                            </span>
                        </td>
                        <!-- [수정됨] 등록일 대신 사용자 ID를 표시 (또는 다른 필요한 정보) -->
                        <td><?php echo htmlspecialchars($house[COL_HOUSE_USER_ID]); ?></td>
                        <td>
                            <!-- [수정됨] 링크의 ID를 House_ID로 변경 -->
                            <a href="/device/detail/<?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?>" class="btn btn-secondary btn-sm">상세보기</a>
                            
                            <!-- [수정됨] 삭제 폼의 action URL을 House_ID로 변경 -->
                            <form action="/device/delete/<?php echo htmlspecialchars($house[COL_HOUSE_ID]); ?>" method="POST" style="display: inline-block; margin-left: 5px;" 
                                data-confirm-message="정말로 이 세대와의 연결을 해제하시겠습니까? 데이터는 복구할 수 없습니다.">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">연결 해제</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>

    <!-- 2. 등록된 세대가 없는 경우: 안내 메시지 표시 -->
    <div class="no-data-message">
        <h2>등록된 세대 정보가 없습니다.</h2>
        <p>
            '새 세대/기기 등록하기' 버튼을 클릭하여 첫 번째 세대 정보를 추가하고 관리를 시작해보세요.
        </p>
        <a href="/device/register" class="btn btn-primary btn-large">첫 세대 등록하러 가기</a>
    </div>

<?php endif; ?>