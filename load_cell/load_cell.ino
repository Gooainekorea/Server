#include <HX711.h>

#define LDCELL_DT_PIN 23
#define LDCELL_SCK_PIN 22

HX711 loadcell;
int loadcell_calibration = 400; //g으로 변환
int weight;
int zero = 453; //아무것도 올려놓지 않았을때 0점조절


void setup() {
  Serial.begin(9600);
  loadcell.begin(LDCELL_DT_PIN,LDCELL_SCK_PIN); // 데이터, 클럭핀 지정
  // loadcell.tare();  // 영점 보정
  loadcell.set_scale(loadcell_calibration);    
}

void loop() {
  Serial.print(F("무게: "));
  weight = (loadcell.get_units()-zero); //소수점 아래 버림
  if ( weight < 0){ weight = 0; }
  // weight = (loadcell.get_units()); 
  Serial.print(weight);// 캘리브레이션 적용후 실제 무개로 변환,소수점 2 자리까지 출력
  Serial.println(F(" g"));
  delay(500); //10ms 0.01초에 한번씩 측정.

}
