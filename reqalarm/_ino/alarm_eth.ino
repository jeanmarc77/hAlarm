/* 01/05/20 alarm by ethernet */
#include <SPI.h>
#include <Ethernet.h>

#define ETH_CS    10 // Select the Ethernet Module.
#define SD_CS  4 // De-Select the internal SD Card

// MAC address
byte mac[] = { 0x00, 0xAA, 0xBB, 0xCC, 0xDE, 0x02};
IPAddress ip(192, 168, 0, 16);

// Initialize the Ethernet client library
EthernetServer server(80);

char getAnswer[256]; // array to store the get-answer/
int getAnswerCount = 0; // counter for getAnswer

// Command input String
String inputString = "";

// Convert
String str_out = "";

// Time mngt
unsigned long now = 0;
unsigned long last_timeA = 0; // last alarm
unsigned long last_timeW = 0; // last warning

// Inputs Zone 1 entrée
unsigned int val0 = 0; // read value
String inputstate0 = "off"; // status
unsigned long last_timeH0 = 0; // last time high and low
bool lock0 = false;
// Inputs Zone 2 salon
unsigned int val1 = 0;
String inputstate1 = "off";
bool lock1 = false;
unsigned long last_timeH1 = 0;
// Inputs Zone 3 buandrie
unsigned int val2 = 0;
String inputstate2 = "off";
unsigned long last_timeH2 = 0;
bool lock2 = false;
// Inputs Zone 4 hall nuit
unsigned int val3 = 0;
String inputstate3 = "off";
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

  pinMode(ETH_CS, OUTPUT);
  pinMode(SD_CS, OUTPUT);
  digitalWrite(ETH_CS, LOW); // Select the Ethernet Module.
  digitalWrite(SD_CS, HIGH); // De-Select the internal SD Card

  // Start the Ethernet connection:
  Serial.println("Initialize Ethernet with DHCP:");
  while (Ethernet.begin(mac) == 0) {
    Serial.println("Failed to configure Ethernet using DHCP");
    if (Ethernet.hardwareStatus() == EthernetNoHardware) {
      Serial.println("Ethernet shield was not found");
    } else if (Ethernet.linkStatus() == LinkOFF) {
      Serial.println("Ethernet cable is not connected.");
    }
    delay(3);
  }
  Serial.print("My IP address: ");
  Serial.println(Ethernet.localIP());
} // setup

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

  listenForEthernetClients();
} // end loop

void listenForEthernetClients() {
  EthernetClient client = server.available();
  if (client) {
    // an http request ends with a blank line
    boolean currentLineIsBlank = true;
    String readString = "";
    boolean firstLine = true;
    while (client.connected()) {
      if (client.available()) {
        char c = client.read();
        //Serial.write(c);
        if (firstLine) {
          getAnswer[getAnswerCount] = c;
          ++getAnswerCount;
        }
        // if you've gotten to the end of the line (received a newline character) and the line is blank, the http request has ended, so you can send a reply
        if (c == '\n' && currentLineIsBlank) {
          String str(getAnswer);
          // Serial.println(str);
          String str_out = String(now);
          client.println("HTTP/1.1 200 OK");
          client.println("Content-Type: application/json");
          client.println();
          if (str == "GET /?stat HTTP/1.1\r\n") {
            client.print("{\"time\":" + str_out + ", \"I0\":\"" +  inputstate0 + "\", \"I1\":\"" +  inputstate1 + "\", \"I2\":\"" +  inputstate2 + "\", \"I3\":\"" +  inputstate3 + "\", \"O1\":\"" + r1state + "\", \"O2\":\"" + r2state + "\", \"O3\":\"" + r3state + "\"}");
          } else if (str == "GET /?val HTTP/1.1\r\n") {
            client.print("{\"time\":" + str_out + ", \"I0\":" +  val0 + ", \"I1\":" +  val1 + ", \"I2\":" +  val2 + ", \"I3\":" +  val3 + "}");
          } else if (str == "GET /?r1 HTTP/1.1\r\n") { // Relays command int
            digitalWrite(3, LOW);
            r1state = "on";
            inalarm = true;
            last_timeA = now;
            Serial.println("r1");
            client.print("{\"cmd\":\"r1\"}");
          } else if (str == "GET /?r2 HTTP/1.1\r\n") {  // Relays ext
            digitalWrite(7, LOW);
            r2state = "on";
            inalarm = true;
            last_timeA = now;
            Serial.println("r2");
            client.print("{\"cmd\":\"r2\"}");            
          } else if (str == "GET /?r3 HTTP/1.1\r\n") { 
            digitalWrite(8, LOW);
            r3state = "on";
            inwarn = true;
            last_timeW = now;
            Serial.println("r3");
            client.print("{\"cmd\":\"r3\"}");
          } else if (str == "GET /?r3off HTTP/1.1\r\n") {  // Warning relay mini buzz
            digitalWrite(8, HIGH);
            r3state = "off";
            inwarn = false;
            Serial.println("r3off");
            client.print("{\"cmd\":\"r3off\"}");
          } else if (str == "GET /?roff HTTP/1.1\r\n") {  // Alarm relays off
            digitalWrite(3, HIGH);
            digitalWrite(7, HIGH);
            r1state = "off";
            r2state = "off";
            inalarm = false;
            Serial.println("roff");
            client.print("{\"cmd\":\"roff\"}");
          } else {
            Serial.println("Wrong commands");
            client.print("{\"cmd\":\"nodata\"}");
          }
          break;
        }
        if (c == '\n') { // new line
          currentLineIsBlank = true;
          firstLine = false;
        } else if (c != '\r') { // gotten a character on the current line
          currentLineIsBlank = false;
        }
      }
    }
    client.stop();
    getAnswerCount = 0;
    memset(getAnswer, 0, sizeof(getAnswer)); // empty
  } // client
}
