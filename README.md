## Mine-Panel
A lightweight Minecraft control panel for hosting companies

Mine-Panel can manage an unlimited number of Minecraft control panels, including CraftBukkit, Tekkit, and any other custom build that uses a .jar file.

NOTE: Some features of this project including auto-updating and directory deleting/renaming are currently broken, and have been disabled.

## Requirements

### You need the following to run Mine-Panel
- PHP 5
- Java 7
- MySQL with PDO enabled
- 'Screen' installed

## Installation

### Configuration (until I get install script working)
- The settings KT_DATABASE_HOST and KT_DATABASE_DB are not yet changeable.
- With that being said, create a database name mine-panel
- Upload the database template to the database
- Then set KT_DATABASE_USERNAME and KT_DATABASE_PASSWORD to your database's username and password, respectively.

### How to Install in a Nutshell

- Upload all of the files to a web-accessible directory on your server.
- Edit data/config.php and set KT_LOCAL_IP to your server's public IP address
- Go to install.php in your browser and set up an administrator user.
- Delete install.php
- Add any Minecraft server .jar file to your home directory, and rename it "craftbukkit.jar"

### User setup

- Log in as an administrator user
- Go to Administration
- Use the "Add a New User" form to set up a new account, the home directory SHOULD NOT be web accessible
- Add any Minecraft server .jar file to the user's directory, and rename it "craftbukkit.jar"
- If desired, you can now start the user's server from the Administration page

### Installation Tutorials
- Will be ready soon
