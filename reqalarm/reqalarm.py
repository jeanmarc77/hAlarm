#!/usr/bin/env python
# pyserial is needed http://pyserial.sourceforge.net/
# If running as http user, you'll need to add it to your com. group 
# ls -l /dev/ttyACM*
# crw-rw---- 1 root uucp 188, 0 Feb 18 09:02 /dev/ttyACM0
# usermod -aG uucp http

import serial, sys, time, glob

if len(sys.argv) == 1:
    print("Abording: Provide an argument"), sys.exit()
elif len(sys.argv) > 2:
    print("Abording: Too many arguments"), sys.exit()
else:
#PORT = (glob.glob("/dev/ttyACM*")) # Detect the arduino port
#ser = serial.Serial(PORT[0], 921600, timeout=10)
#ser = serial.Serial(PORT[0], 19200, timeout=10)
   ser=serial.Serial("/dev/ttyACM1",921600,timeout=3)
   if ser.isOpen():
      if sys.argv[1] == '-stat': # status
         ser.write("-stat\n".encode("utf-8"))
      elif sys.argv[1] == '-val': # values
         ser.write("-val\n".encode("utf-8"))
      elif sys.argv[1] == '-r1': # relay 1
         ser.write("-r1\n".encode("utf-8"))
      elif sys.argv[1] == '-r2': # r2
         ser.write("-r2\n".encode("utf-8"))
      elif sys.argv[1] == '-r3': # r3
         ser.write("-r3\n".encode("utf-8"))
      elif sys.argv[1] == '-roff': # relays off
         ser.write("-roff\n".encode("utf-8"))
      else:
         print("Abording:",sys.argv[1],"is not a valid argument"), sys.exit()

      line=ser.readline(128).decode("utf-8") # raise the number if json string is longer
      print(line,end="")

   else:
      print("Abording: serial not open")

#print("done")
ser.flushInput()
ser.flushOutput()
ser.close()
quit()
sys.exit()
