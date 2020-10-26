# symfonydemo

This is a Symfony application where creates Micro post add, edit, and follow, Unfollow with notification, Email, User Login, Registration functionality with email verification, and also use Doctrine database with join table mapping.

## Web server setup

### Apache setup

To setup apache, setup a virtual host to point to the public/ directory of the
project and you should be ready to go! It should look something like below:

```apache
<VirtualHost *:80>
    ServerName symfony-app.localhost
    DocumentRoot /path/to/symfony-app/public
    <Directory /path/to/symfony-app/public>
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
        <IfModule mod_authz_core.c>
        Require all granted
        </IfModule>
    </Directory>
</VirtualHost>
```
