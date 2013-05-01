<?php // Mine-Panel Configuration

// Database Details
define('KT_DATABASE_HOST','localhost');
define('KT_DATABASE_USERNAME','minepanel');
define('KT_DATABASE_PASSWORD','mcpanel');
define('KT_DATABASE_DB','minepanel'); // THIS CURRENTLY DOES NOT WORK, YOU NEED TO CREATE A DATABASE BY THE NAME OF minepanel

// Server IP Address
define('KT_LOCAL_IP','75.102.38.246');

// Themeing
define('KT_THEME_DIRECTORY','themes/default');

// Prefix for GNU-Screen names (prepended to username)
define('KT_SCREEN_NAME_PREFIX','mp-');

// Path to download server updates from (uses wget)
define('KT_UPDATE_URL_MC','http://s3.amazonaws.com/MinecraftDownload/launcher/minecraft_server.jar');
define('KT_UPDATE_URL_CB','http://dl.bukkit.org/latest-rb/craftbukkit.jar');

// Screen commands (these should never be modified)
define('KT_SCREEN_CMD_START','/usr/bin/screen -dmS %s /usr/bin/java -Xms%sM -Xmx%sM -jar craftbukkit.jar nogui');
define('KT_SCREEN_CMD_EXEC','/usr/bin/screen -S %s -p 0 -X stuff "%s$(printf \\\\r)"');
define('KT_SCREEN_CMD_KILL','/usr/bin/screen -X -S %s quit');
define('KT_SCREEN_CMD_KILLALL','killall /usr/bin/screen');
define('KT_SCREEN_CMD_KILLALL_USER','for session in $(/usr/bin/screen -ls | /bin/grep -o \'[0-9]*\\.%s\'); do /usr/bin/screen -S "${session}" -X quit; done');

// User account creation
// define('KT_SCREEN_USR_FTP_CREATE',''); This is under heavy development and isn't ready yet...

?>
