
	Communication apps examples for hAlarm using an Arduino Leonardo.
	Read out more on https://github.com/jeanmarc77/hAlarm
	--------

	* reqalarm.py get the arduino detectors state and send relays commands using pyserial.

	--------

How to :

- Load your modified _ino file in your Arduino

- Connect your Arduino sensors and connect it via USB ;)

- Install python and pip then install pyserial (pip install pyserial) or pacman -Suy python-pyserial for Arch Linux.

- Put the apps reqalarm.py in your webserver directory (eg in /srv/http/comapps/) 

- Make sure it is executable via (chmod a+x reqalarm.py)

- Allow access the com. port to http user (eg usermod -aG uucp http)

- Edit reqalarm.py to adujst it to your needs. This script will communicate with your arduino using pyserial.

- Test the app :

[root@i3 ~]# reqalarm -stat
{"time":87144630, "I0":"off", "I1":"off", "I2":"off", "I3":"off", "O1":"off", "O2":"off", "O3":"off"}

- Setup hAlarm 

