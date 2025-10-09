/*
통신 테스트
*/
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include "CA_authentication.h" //CA(let's encrypt)의 루트 인증서
#include "device_keys.h" //기기 비대칭키

WiFiClientSecure client;
HTTPClient http;

const char* wifi_NM = "U+NetD0E0"; //와이파이 이름(개인설정)
const char* wifi_PW = "GAD4F4#300";  //와이파이 비번(개인설정)
const char* ServerURL = "smartclean.kro.kr";  //서버 주소

const char* Device_ID = "smartclean_2025_10_08_fsdfacd"; //제품 id(제품명/생산일/제품번호)
const char* TestData = 0; //임시로 보낼 데이터

void setup() {
  Serial.begin(9600);
  WiFi.begin(wifi_NM,wifi_PW);

   // Wi-Fi 연결
  while(WiFi.status() != WL_CONNECTED){ //접속 불가시 0.5초마다 시도후 점찍기
    delay(500);
    Serial.println(F("WiFi connection failed"));
  }
  Serial.println(F("wifi connected success."));
  Serial.print(F("IP add : "));
  Serial.println(WiFi.localIP()); // wifi 연결후 dhcp로 ip 받아오면 확인 가능.
  //와이파이연결 확인
  
  // ESP32가  서버 인증서 검증에 공인 CA 인증서를 사용하도록 설정.
  // 이것이 "신뢰의 기준점"이 됩니다.
  client.setCACert(root_ca_cert); // 서버 인증서의 도메인 이름이 일치하는지 검증
  Serial.printf("Connecting to server: %s\n", ServerURL);


  HTTPClient http;
  http.begin(ServerURL);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData = "public_key=";
  postData += public_key_pem; // 헤더에 선언된 공개키

  String id = "device_id=";
  id += Device_ID; // id

  int httpResponseCode = http.POST(postData);

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println(response);
  } else {
    Serial.println(F("Error sending key"));
  }
  http.end();
}

void loop() {}