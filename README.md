# GdSharer
Create permalinks easily by browsing your Google Drive files and folders.

## Installation:
```bash 
git clone https://github.com/fiand1996/gdsharer.git myproject 
cd myproject
composer install
```
- create database &amp; import myproject/sql/googlesharer.sql
- edit database config in myproject/app/config/database.config.php
- put your Google Client Id, Google Client Secret, and Google API Key to myproject/app/config/common.config.php
- edit myproject/public/index.php change ENVIRONMENT to development