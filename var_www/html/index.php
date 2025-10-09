<?php

/**
 * ====================================================================
 * 프론트 컨트롤러 (Front Controller) - 최종 수정 버전
 * ====================================================================
 * 모든 웹 요청에 대한 단일 진입점(Single Point of Entry)입니다.
 * .htaccess 규칙에 의해 모든 요청을 받아, URL을 분석한 후
 * 적절한 컨트롤러의 메소드로 라우팅(Routing)하는 역할을 담당합니다.
 */


// --- 1. 초기 설정: 애플리케이션의 기본 경로 상수 정의 ---
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');


// --- 2. 오토로더(Autoloader): 클래스 자동 로드 ---
spl_autoload_register(function ($className) {
    $filePaths = [
        APP_PATH . '/controllers/' . $className . '.php',
        APP_PATH . '/models/' . $className . '.php',
        APP_PATH . '/lib/' . $className . '.php'
    ];

    foreach ($filePaths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


// --- 3. 라우팅(Routing): 사용자 요청 URL 분석 ---
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$trimmedPath = trim($requestPath, '/');
$pathSegments = $trimmedPath ? explode('/', $trimmedPath) : [];


// --- 4. 디스패치(Dispatch): 컨트롤러와 메소드 결정 및 실행 ---

// URL 경로 세그먼트를 기반으로 컨트롤러, 메소드, 파라미터를 결정합니다.
$controllerSlug = $pathSegments[0] ?? 'page'; // URL이 비어있으면 'page' 컨트롤러를 기본으로 사용
$methodName     = $pathSegments[1] ?? 'index'; // 메소드명이 없으면 'index'를 기본으로 사용
$params         = array_slice($pathSegments, 2);

// [수정됨] URL 슬러그와 실제 컨트롤러 클래스 이름을 매핑하는 라우팅 테이블
// API 컨트롤러가 추가되었습니다.
$controllerMap = [
    'page'   => 'PageController',
    'auth'   => 'AuthController',
    'user'   => 'UserController',
    'device' => 'DeviceController',
    'api'    => 'ApiController',   // '/api/...' 요청을 처리할 컨트롤러
];

// [수정됨] 기본 메소드 이름(index)을 실제 실행할 메소드 이름으로 매핑하는 규칙
// 이 규칙을 통해 코드의 유연성과 가독성이 향상됩니다.
$defaultMethodMap = [
    'page'   => 'home',   // 예: '/' 또는 '/page' 요청 -> PageController@home
    'device' => 'list',   // 예: '/device' 요청 -> DeviceController@list
    'user'   => 'myPage', // 예: '/user' 요청 -> UserController@myPage
];

// 요청된 메소드 이름이 기본값(index)인 경우, 위 매핑 테이블을 참조하여 실제 메소드 이름으로 변경합니다.
if ($methodName === 'index' && isset($defaultMethodMap[$controllerSlug])) {
    $methodName = $defaultMethodMap[$controllerSlug];
}


// --- 5. 컨트롤러 실행 ---
$controllerClass = $controllerMap[$controllerSlug] ?? null;

if ($controllerClass && class_exists($controllerClass)) {
    $controllerInstance = new $controllerClass();

    if (method_exists($controllerInstance, $methodName)) {
        // call_user_func_array: 객체의 메소드를 호출하면서, 파라미터 배열을 인자로 전달합니다.
        // 예: $controllerInstance->detail('123'); 와 동일하게 동작
        call_user_func_array([$controllerInstance, $methodName], $params);
    } else {
        // 요청한 메소드가 컨트롤러에 존재하지 않을 경우 404 처리
        handleNotFound();
    }
} else {
    // 요청한 컨트롤러가 존재하지 않을 경우 404 처리
    handleNotFound();
}


/**
 * 404 Not Found 처리 함수
 * 라우팅 과정에서 유효한 컨트롤러나 메소드를 찾지 못했을 때 호출됩니다.
 */
function handleNotFound() {
    http_response_code(404);
    if (file_exists(VIEWS_PATH . '/error.php')) {
        require_once VIEWS_PATH . '/error.php';
    } else {
        echo "<h1>404 Not Found</h1><p>The page you requested could not be found.</p>";
    }
    exit();
}