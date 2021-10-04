

#include <BearSSLHelpers.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266WiFiGratuitous.h>
#include <WiFiServerSecureBearSSL.h>
#include <ESP8266WiFiType.h>
#include <WiFiClientSecure.h>
#include <WiFiUdp.h>
#include <ESP8266WiFiAP.h>
#include <WiFiClientSecureBearSSL.h>
#include <ESP8266WiFi.h>
#include <CertStoreBearSSL.h>
#include <ESP8266WiFiGeneric.h>
#include <WiFiServerSecure.h>
#include <ESP8266WiFiScan.h>
#include <WiFiServer.h>
#include <ArduinoWiFiServer.h>
#include <ESP8266WiFiSTA.h>
#include <ESP8266HTTPClient.h>

#include "MQ131.h"
#include <DHT.h>;


#define DHTPIN 13
#define DHTTYPE DHT22   
DHT dht(DHTPIN, DHTTYPE);
int counter = 0;
float hum;  
float temp; 
const char* ssid = "****";
const char* password = "****";
WiFiClient client;

void setup() {
  Serial.begin(9600);
  WiFi.begin(ssid, password);
 
  while (WiFi.status() != WL_CONNECTED) {
 
    delay(1000);
    Serial.print("Connecting..");
 
  }
  Serial.println("Wifi Connected.");
  
  dht.begin();
  pinMode(14, OUTPUT);
  digitalWrite(14, HIGH);
  

  hum = dht.readHumidity();
  temp= dht.readTemperature();

  MQ131.begin(5,A0, LOW_CONCENTRATION, 1000, (Stream *)&Serial); //10000  
  MQ131.setEnv((int)temp, hum);
  Serial.println("\nCalibration in progress...");
  
 MQ131.calibrate();
  
  Serial.println("Calibration done!");

  Serial.print("R0 = ");
  Serial.print(MQ131.getR0());
  Serial.println(" Ohms");
  Serial.print("Time to heat = ");
  Serial.print(MQ131.getTimeToRead());
  Serial.println(" s");
  digitalWrite(14, LOW);
}

void loop() {
  hum = dht.readHumidity();
  temp= dht.readTemperature();
  MQ131.setEnv((int)temp, hum);
  Serial.println("Sampling...");
  MQ131.sample();

  float o3 = MQ131.getO3(UG_M3);
  Serial.println(o3);
  char o3_data[8];
  char hum_data[8];
  char *humc = dtostrf((double)hum,2,0,hum_data);
  char temp_data[8];
  char *tempc = dtostrf(temp,5,2,temp_data);
  char *o3c = dtostrf(o3,5,2,o3_data);
  const char* host = "http://scicrop.com/o3";
  char urlout[255];
  strcpy(urlout, host);
  strcat(urlout, "/?o3=");
  strcat(urlout, o3c);
  strcat(urlout, "&h=");
  strcat(urlout, humc);
  strcat(urlout, "&t=");
  strcat(urlout, tempc);

  if (WiFi.status() == WL_CONNECTED) { 
    HTTPClient http;  
    http.begin(client, urlout); 
    int httpCode = http.GET();
    Serial.println(urlout);
    Serial.println(httpCode);
    if (httpCode > 0) { 
      String payload = http.getString();  
      Serial.println(payload);            
    }
    http.end();   
 
  }


  while(counter < 100){
    delay(2850);
    digitalWrite(14, HIGH);
    delay(150);
    digitalWrite(14, LOW);
    counter++;
  }
  counter = 0;
}
