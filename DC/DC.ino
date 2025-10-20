//DC 모터 제어논리
//L923D에서 활성화와 vcc1 없음
//HCF4016BE 로?
//15A LM2575 DC 모터 드라이버

#define MOTOR_UP_PIN 4
#define MOTOR_DOWN_PIN 2


void setup() {
  Serial.begin(9600);
  pinMode(MOTOR_UP_PIN, INPUT);
  pinMode(MOTOR_DOWN_PIN, INPUT);
}

void loop() {
  int up = digitalRead(MOTOR_UP_PIN);
  int down = digitalRead(MOTOR_DOWN_PIN);
  
  Serial.print(F("MOTOR_UP_PIN : "));
  Serial.println(up);
  Serial.print(F("MOTOR_DOWN_PIN : "));
  Serial.println(down);
  
  delay(50);
}
