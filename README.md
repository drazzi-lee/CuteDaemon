CuteDaemon
==========

A simple daemon service, provide execute scheduled tasks and a monitor daemon process. 

CuteDaemon is as a mother, it would check the clock every seconds. When it's child is the time to wake up, She will wake up them.

- Service restart will reset times runned of the task. For example, a task need to run 10 times, it runned 6 time already. It
will run another 10 times if you restart the service.

- The period of the task is the time of seconds difference between last called and current called.

- This project is incomplete, may cause some unexpected exception like server going down becauseof memory exhausted. Please make
sure you have already test it on your development server before you run it on a production environment.

##How To Use
	1.###Run as service###
		sudo /path/to/cutedaemon -s
		sudo service cutedaemon start
	2.###Run as daemon###
		sudo /path/to/cutedaemon -d
	3.###Get help###
		sudo /path/to/cutedaemon

