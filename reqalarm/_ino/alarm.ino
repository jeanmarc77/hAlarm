/* 30/03/20 alarm  */

// Command input String
String inputString = "";
// Convert
char charVal[8]; // convert float to an array of char
String str_out = "";
String str_out2 = "";
String str_out3 = "";

// Time mngt
unsigned long now = 0;
unsigned long last_timeA = 0; // last alarm
unsigned long last_timeW = 0; // last warning

// Inputs Zone 1 entrée
int val0 = 0; // read value
String inputstate0 = "off"; // status
String str_A0 = ""; // convert
unsigned long last_timeH0 = 0; // last time high and low
bool lock0 = false;
// Inputs Zone 2 salon
int val1 = 0;
String inputstate1 = "off";
String str_A1 = "";
bool lock1 = false;
unsigned long last_timeH1 = 0;
// Inputs Zone 3 buandrie
int val2 = 0;
String inputstate2 = "off";
String str_A2 = "";
unsigned long last_timeH2 = 0;
bool lock2 = false;
// Inputs Zone 4 hall nuit
int val3 = 0;
String inputstate3 = "off";
String str_A3 = "";
unsigned long last_timeH3 = 0;
bool lock3 = false;

// Outputs
bool inalarm = false;
bool inwarn = false;
String r1state = "off"; // relay status
String r2state = "off";
String r3state = "off";
unsigned long althres = 600000; // turn off relays after 10 min
//unsigned long althres = 60000;
unsigned long wthres = 30000; // turn off mini buzz after 30 sec

// Thresholds
unsigned int thres = 600; // Detector analog values are around 500 high and 1000 low
unsigned int lowthres = 400; // Error if lower than this
unsigned int pulse = 500; // min det pulse width (real is around 3000ms)

void setup() {
  pinMode(3, OUTPUT); // Int buzzer
  pinMode(7, OUTPUT); // Ext buzzer + light
  pinMode(8, OUTPUT); // mini buzzer
  digitalWrite(3, HIGH); // beware relay are reversed
  digitalWrite(7, HIGH);
  digitalWrite(8, HIGH);
  Serial.begin(57600); // fast serial
}

void loop() {
  now = millis(); // up to 50days
  // Input reading entrée
  val0 = analogRead(A0);  // read the input pin
  if (!lock0 && val0 < thres && val0 > lowthres) {
    last_timeH0 = now;
    lock0 = true;
  }
  if (val0 < thres && val0 > lowthres && now - last_timeH0 > pulse ) { // avoid false pulse
    inputstate0 = "on" ;
  }
  if (val0 > thres) {
    lock0 = false;
    inputstate0 = "off" ;
  } else if (val0 < lowthres) {
    inputstate0 = "err";
  }

  // Input reading salon
  val1 = analogRead(A1);
  if (!lock1 && val1 < thres && val1 > lowthres) {
    last_timeH1 = now;
    lock1 = true;
  }
  if (val1 < thres && val1 > lowthres && now - last_timeH1 > pulse ) {
    inputstate1 = "on" ;
  }
  if (val1 > thres) {
    lock1 = false;
    inputstate1 = "off" ;
  } else if (val1 < lowthres) {
    inputstate1 = "err";
  }

  // Input reading buandrie
  val2 = analogRead(A2);
  if (!lock2 && val2 < thres && val2 > lowthres) {
    last_timeH2 = now;
    lock2 = true;
  }
  if (val2 < thres && val2 > lowthres && now - last_timeH2 > pulse ) {
    inputstate2 = "on" ;
  }
  if (val2 > thres) {
    lock2 = false;
    inputstate2 = "off" ;
  } else if (val2 < lowthres) {
    inputstate2 = "err";
  }

  // Input reading hall nuit
  val3 = analogRead(A3);
  if (!lock3 && val3 < thres && val3 > lowthres) {
    last_timeH3 = now;
    lock3 = true;
  }
  if (val3 < thres && val3 > lowthres && now - last_timeH3 > pulse ) {
    inputstate3 = "on" ;
  }
  if (val3 > thres) {
    lock3 = false;
    inputstate3 = "off" ;
  } else if (val3 < lowthres) {
    inputstate3 = "err";
  }

  // Auto turn off alarm
  if (inalarm && now > (last_timeA + althres)) {
    digitalWrite(3, HIGH);
    digitalWrite(7, HIGH);
    r1state = "off";
    r2state = "off";
    inalarm = false;
  }
  // Auto turn off mini buzz
  if (inwarn && now > last_timeW + wthres) {
    digitalWrite(8, HIGH);
    r3state = "off";
    inwarn = false;
  }

  // Command reading
  if (Serial.available()) {
    char inChar = (char)Serial.read();
    if (inChar == '\n') {
      if (inputString == "-stat") { // status as JSON
        String str_out = String(now);
        Serial.println("{\"time\":" + str_out + ", \"I0\":\"" +  inputstate0 + "\", \"I1\":\"" +  inputstate1 + "\", \"I2\":\"" +  inputstate2 + "\", \"I3\":\"" +  inputstate3 + "\", \"O1\":\"" + r1state + "\", \"O2\":\"" + r2state + "\", \"O3\":\"" + r3state + "\"}");
      }
      else if (inputString == "-val") { // values
        String str_out = String(now);
        Serial.println("{\"time\":" + str_out + ", \"I0\":" +  val0 + ", \"I1\":" +  val1 + ", \"I2\":" +  val2 + ", \"I3\":" +  val3 + "}");
      }
      else if (inputString == "-r1") { // Relays command int
        digitalWrite(3, LOW);
        r1state = "on";
        inalarm = true;
        last_timeA = now;
      }
      else if (inputString == "-r2") { // Relays ext
        digitalWrite(7, LOW);
        r2state = "on";
        inalarm = true;
        last_timeA = now;
      }
      else if (inputString == "-r3") { // Warning relay mini buzz
        digitalWrite(8, LOW);
        r3state = "on";
        inwarn = true;
        last_timeW = now;
      }
      else if (inputString == "-r3off") {
        digitalWrite(8, HIGH);
        r3state = "off";
        inwarn = false;
      }
      else if (inputString == "-roff") { // Alarm relays off
        digitalWrite(3, HIGH);
        digitalWrite(7, HIGH);
        r1state = "off";
        r2state = "off";
        inalarm = false;
      }
      else {
        Serial.print(inputString);
        Serial.println(" : unknown command");
      }
      inputString = "";
    }
    else {
      if (inputString.length() < 10) { // Command limited to 10 chars
        inputString += inChar;
      }
    }
  }

}
