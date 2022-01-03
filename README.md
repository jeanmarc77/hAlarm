# What hAlarm can do for you ?
hAlarm (stand for Home Alarme) is a small project to make a burglar alarm. It can recover an existing system by taking previous material such as detectors and alarm sirens but it could also be use on a new build installation.

It is web-based and run on top of a webserver using php. The front-end, the communication to read inputs and doing commands are purely software. It mean it may not be as reliable as a pure micro-controller system. But, i can reassure you, it's reliability will be more than enough for a residential.

The project need one or more IP device acting as keypad(s).

<table width="20%" border=1 cellspacing=0 cellpadding=5 align="left">
	<tr><td>Pro</td><td>Con</td></tr>
	<tr><td>Fully IP, access from LAN and WAN</td><td>Requiere technical skill</td>
	<tr><td>Neat interface</td><td>Consume energy for the soft keypad</td>
	<tr><td>Intuitive keypad </td><td>Depend on LAN availability</td>
</table>
  
hAlarm received the inputs information as JSON such as {"I0":"off", "I1":"off", "O1":"off"}
Where I is for inputs and O are outputs status with 'on' or 'off' values.
hAlarm command the outputs (relays for sirens, flash..) by software.

hAlarm rely on communication(s) application(s) to read inputs and to command outputs, which are -not- part of this project.
