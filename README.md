# CAA_officer_application
## About
This is a web application that is supposed to make CAA (Compassionate Action for Animals) UMN (University of Minnesota) student chapter officer tasks easier, more efficient, and more thorough. The aim of this application is to be able to automatically create tasks in Basecamp for officers. This will help the student group in a number of areas:
- officer transitions: The task descriptions will be tutorial-like and as such will be a great resource for new officers, thus easing officer transitions.
- officer communication: By making basecamp tasks easier to make, each task given to officers can now be tracked on basecamp so progress on that task can be tracked.
- officer capabilities: The automated task creation will allow many tasks to be made at once and split between officers so that our capability of doing more things will increase.
- student group growth: Creating clear procedures for events and tasks will allow officers to have the time and capability to implement new changes in the group (new event, meeting rituals, etc).

In the future, I hope to also automake some of the tasks themselves through integrations with other services, and possibly a web extension. This would include google calendar, and our google drive.

## Contributing
Thank you for considering contributing to this project, we love all the help we can get! Please let me know if you would like to contribute so I can help you get set up. If you have any questions at all please feel free to reach out and I can explain or try to help. (It can be as simple as explaining code functionality).

Ways to Contribute:
- creating tasks and task descriptions
- creating and solving issues
- and more (I have ideas I can give)

Please see the [setup guide](https://github.com/r-sauers/CAA_officer_application/edit/main/README.md#setup) for help setting the application up on your device.

You can create a pull request to help contribute.

## Setup
Setting this server up is going to be somewhat rigorous. But let's get started!

### Setting up a LAMP server
I am running this application on a LAMP server (Linux Apache Mysql Php). So the first step is to set up these things. If you want to run it on a different setup, the code will most likely need significant modification, so I recommend using the LAMP server.

**Running Windows and not Linux? How to install wsl Ubuntu**  
I am running wsl2 Ubuntu 20.04.5 LTS. To setup this, refer to Microsoft's [guide](https://learn.microsoft.com/en-us/windows/wsl/install). The default install of wsl should work fine 

**Apache Installation**  
Now that you have Ubuntu, installing apache should be a breeze. Apache should be on the package manager. Run the following:
`apache2 -v` to check if you have apache installed (and what version it is)
`sudo apt update` to make sure your packet manager is up to date
`sudo apt install apache2` to install apache2
(I am running version:  Apache/2.4.41 (Ubuntu))

**PHP Installation**  
The php installation should be the exact same process as the apache installation.
`php -v` to check if you have php installed (and what version it is)
`sudo apt update` to make sure your packet manager is up to date
`sudo apt install php` to install php
(I am running version: 7.4.3-4ubuntu2.18)

**MySQL Installation**  
The MySQL installation should be the exact same process as the apache installation as well.
`mysql --version` to check if you have mysql installed (and what version it is)
`sudo apt update` to make sure your packet manager is up to date
`sudo apt install mysql-server` to install php
(I am running version: 8.0.32-0ubuntu0.20.04.2 for Linux on x86_64)

Congratulations, you have everything you need for a LAMP server!

### Setting up the CAA Officer Application
Now it's time to get the application running.

#### How the LAMP server works
If you have never used a LAMP server, I will help explain the basics.
- Apache is the web server, meaning that it is involved in 'serving' files to users, as such it is the process that you start when you want your server to be on, and the process that you stop when you want your server turned off. 
- MySQL is a database management system. It allows you to store and retrieve data from a database using SQL (Structured Query Language)
- PHP is a scripting language designed for web development that allows you to write back-end code and front-end code in the same file. Thus, when apache serves a php file, it will start running the back-end code and give the resulting front-end code to the client.

To start running the web application, you can start apache by typing `service apache2 start`. Likewise to stop running the server, type `service apache2 stop`.
By default, Apache serves all files in the directory `/var/www/html`. Thus if you have a php script: `/var/www/html/script.php`, a client can retrieve the file by going to: `http://localhost/script.php`.

I have stayed with the default and am serving files from `/var/www/html`. If you wish to change the directory path you are serving from, please note that `initialization.py` refers to `/var/www/html` and must be changed as well.

#### Downloading and Managing Application Files
(Don't clone the repo until I say!)

Start by downloading the files and placing them all in `/var/www/html`. You will have to change the permissions of the files so that while executing server-side code, apache has permission to read and write to the files. To do this you can run: `chown -R www-data:www-data /var/www/html/file` on each file or directory. (I'll give a script to do it on every file in a moment)

The issue quickly arises that you do not have permission to edit the file anymore. For this reason, I decided to create a separate 'development' directory for the files where I can edit them, and I made a script to copy them over to `/var/www/html`. Here is the script (needs root privilege):

```bash
DEVDIR=<type the address of your development directory here>
APPDIR=/var/www/html/

# remove all files in /var/www/html
FILES=$(ls $APPDIR | tr " " "\n");
for MYFILE in $FILES
do
	rm -r $APPDIR$MYFILE;
done

# copy over files from development directory and edit permissions
FILES=$(ls $DEVDIR | tr " " "\n");
for MYFILE in $FILES
do
	cp -r $DEVDIR$MYFILE $APPDIR$MYFILE;
	chown -R www-data:www-data $APPDIR$MYFILE;
done
```
Now you can edit files in the development directory and copy them over to `/var/www/html` when you are ready to run the server. The development directory is where I recommend you store the git repo.

#### Adding Application Files
I have chosen to hide some files from github to make sure no sensitive information is available publicly. These files are:
- `descriptions/*.rtf` A directory to store task descriptions.
- `attachments/*` A directory for attachments to task descriptions. (NOT IMPLEMENTED)
- `event_categories.json` A json file describing event categories.
- `officers.json` A json file describing officers and their roles
- `roles.json` A json file describing roles and the event categories the role is responsible for.  
Please contact me if you need these files or want to contribute to these files. DO NOT push these onto github, (do not delete any lines in .gitignore)

#### Apache Configuration
**Files**  
For the same reason that we don't want the above files publicly available on github, please configure apache so they are not available from an http request to your web server. It is best practice to also configure apache to only serve front-end files, and not library files, so please configure that as well. The configuration file is located by default at: `/etc/apache2/apache2.conf`. To prevent apache from serving a file you can write:
```
# Exclude officers.json from being available to client
<Files "roles.json">
	Order deny,allow
	Deny from all
</Files>

# Exclude description files from being available to client
<Directory "/var/www/html/descriptions">
	Order deny,allow
	Deny from all
</Directory>
```
Wait to write these until you have finished the section.

**Environment Variables**  
In addition to configuring what files can be seen by the client, you must also add two environment variables:
```
SetEnv CAA_APP_BASECAMP_OAUTH_ID "*******"
SetEnv CAA_APP_BASECAMP_OAUTH_SECRET "*******"
```
These are sensitive strings and should not be shared with anyone without permission.

**Creating Configuration Files**
The default configuration file is swamped with stuff, so I recommend including your own config files in that file like:
```
Include /var/www/access.conf
Include /var/www/envvars.conf
```
Where `access.conf` controls the client's access to files, and `envvars.conf` sets the environment variables. Please contact me to get these files.

### Running the Server
To run the server, you must do three things:
- move files into `/var/www/html` as described in [Downloading and Managing Application Files](https://github.com/r-sauers/CAA_officer_application/edit/main/README.md#downloading-and-managing-application-files)
- run `initialization.py`
- run `service apache2 start`

I choose to do all of this in one script:
```bash
#! usr/bin/bash

DEVDIR=<type development directory here>
APPDIR=/var/www/html/

# clean directory that is serving files
FILES=$(ls $APPDIR | tr " " "\n");
for MYFILE in $FILES
do
	rm -r $APPDIR$MYFILE;
done

# move files over and change permissions
FILES=$(ls $DEVDIR | tr " " "\n");
for MYFILE in $FILES
do
	cp -r $DEVDIR$MYFILE $APPDIR$MYFILE;
	chown -R www-data:www-data $APPDIR$MYFILE;
done

# run initialization
python3 /var/www/html/initialize.py

# start apache
service apache2 start
```

You can run the script with:
`sudo bash script.sh`

Congratulations, you now have a working application. Open `http://localhost` to see it in action!
