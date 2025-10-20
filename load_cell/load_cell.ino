#include "HX711.h"

HX711 scale;

#define LC_D_PIN 2 // 데이터(DOUT) 핀 번호
#define LC_CK_PIN  3 // 클럭(SCK) 핀 번호

// 센서 및 보정 관련 변수

float calibration_factor = 0.0;    // 보정계수(ADC값을 g 단위로 변환) //내가 쟀을때는 450쯤 됬음
float currentWeight = 0; //변동직후측정값
float previousWeight = 0; //변동후 지속적 측정


// 상태 및 시간 관련 변수
unsigned long stable_time;  // 값이 안정적으로 유지된 시간(ms)
bool zero_point_applied = true;           // 영점조절 적용 여부
bool offset_applied = false;           // 적재 여부 여부
bool isStable = false;      // 상태 - 불안정
bool newStable = false;     // 새로운 측정 시작

// 샘플링 관련 상수정의
#define AVG_SAMPLES 20                 // 판독 평균에 사용할 샘플 수
#define NOISE_SAMPLES 10               // 노이즈 필터링에 사용할 샘플 수
#define NOISE_THRESHOLD 20               // 오차범위
#define STABLE_TIME_MS 3000          // 안정적으로 유지되어야 하는 시간 (ms)

// 최대 무게
#define MAX_LOAD 95         // 95g일경우 MAX 

float avg_samples[AVG_SAMPLES];   // 다중 측정값 저장 배열
float noise_samples[NOISE_SAMPLES]; // 노이즈 필터링용 샘플 배열
//===============================================================
/**
0. 0점설정 잠깐만.... 이거 보정계수 자동으로 구할 수 있을꺼 같은데
Serial.begin(9600);  
float calibration_factor = 450;
scale.set_scale(calibration_factor);
scale.tare(20);

보정계수 구하기
float calibration_factor(){
  float zero_offset = scale.get_offset(20)
  
}

1. 무개 변동 감지  offset_applied = true
float currentWeight; = scale.get_units();
if (currentWeight; !=0){ 
  unsigned long 변동감지한 시간 = millis();
  while(값이 안정적으로 유지된시간 - 변동 감지한 시간 < 안정적으로 유지되어야 하는시간){
    currentWeight;2 = scale.get_units();
    값이 안정적으로 유지된 시간 = millis()
    if 이전 값으로부터 차이가 크게 없다면 currentWeight; - v2 < 20
      그대로 출력
    else if 이전 값으로부터 차이가 크다면
      변동감지한 시간 = millis();
  }
  3. 해당 값 출력후 0점조절
  Serial.println(currentWeight;);
  tare = 0;
}


**/


//===============================================================

void setup() {
  Serial.begin(9600);
  Serial.println("HX711 스케일 초기화 시작");
  // HX711 스케일 초기화
  scale.begin(LC_D_PIN, LC_CK_PIN);

  scale.set_scale();
  
  // 영점 조절 (20회 평균값으로)
  // scale.tare(20);

  Serial.println("초기화 완료. 측정을 시작합니다.");
}

void loop() {
  // 무게 감지
  currentWeight = scale.get_units(30);

  if(currentWeight < 0) { currentWeight = 0;}
  Serial.print("무게: ");
  Serial.print(currentWeight, 2);
  Serial.println(" g");
  // if(currentWeight != 0){
  //   unsigned long set_time = millis(); //처음 변동된 시간 측정
  //   if (currentWeight >= MAX_LOAD){ // 무게가 초과
  //     Serial.println("경고: 최대 측정 무게를 초과했습니다!");
  //   }
  //   else { // 초과가 아닐때 
  //     while(stable_time-set_time < STABLE_TIME_MS){ // 3초간 측정
  //       float v2 = scale.get_units(30);
  //       stable_time = millis();

  //       if ((currentWeight-v2)<20 && (currentWeight-v2)>-20){ //오차 범위가 +20 -20 이내면
  //         stable_time = millis();
  //         v2 = scale.get_units(30);
  //       }else{
  //         set_time = millis(); //처음 변동된 시간 측정
  //       }
  //     } // 3초측정
      
  //     Serial.print("측정 무게: ");
  //     Serial.print(v2, 2);
  //     Serial.println(" g");

  //     previousWeight = currentWeight;

  //     scale.tare(20);
  //     delay(100); // 루프 지연
      
  //   } //포과가 아ㅣㄹ때
  //}// 값변동

// 1. 무게에 의미 있는 변화가 생겼는지 확인
  if (abs(currentWeight - previousWeight) > NOISE_THRESHOLD) {
    stable_time = millis(); // 안정화 타이머 리셋
    isStable = false;      // 상태 - 불안정
    newStable = false;     // 새로운 측정 시작
  }
  // 2. 무게 변화가 없고, 아직 측정이 완료되지 않았다면
  else if (!newStable) {
    // 3. 안정화 시간이 3초를 지났다면
    if (millis() - stable_time > STABLE_TIME_MS) {
      isStable = true; // 안정된 상태로 변경

      // 최대 무게 초과 확인
      if (currentWeight >= MAX_LOAD) {
        Serial.println("경고: 최대 측정 무게를 초과했습니다!");
      } 
      // 0g에 가까운 값이 아니라면 유효한 측정으로 간주
      else{
        Serial.print("측정 무게: ");
        Serial.print(currentWeight, 2);
        Serial.println(" g");

        // 의도하신 동작: 측정 완료 후 현재 무게를 0점으로 설정
        Serial.println("현재 무게를 0점으로 설정합니다.");
        scale.tare(20);
      }
      
      newStable = true; // 측정 완료 처리
    }
  }

  // 다음 루프에서 비교하기 위해 현재 무게를 저장
  previousWeight = currentWeight;

  delay(100); // 시스템 부하 감소를 위한 짧은 지연

}//loop

































