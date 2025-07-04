#!/bin/bash
PWD=$(pwd)
# Define the project directory and the name of the zip file
PROJECT_DIR="$PWD"
ZIP_FILE="laravel.zip"

# Navigate to the project directory
cd "$PROJECT_DIR" || { echo "Project directory not found!"; exit 1; }

# Remove any existing zip file
if [ -f "$ZIP_FILE" ]; then
  rm "$ZIP_FILE"
fi

# Create a zip file excluding unnecessary files and directories
zip -r "$ZIP_FILE" . \
    -x "vendor/*" \
    -x "node_modules/*" \
    -x ".git/*" \
    -x ".env" \
    -x "storage/logs/*" \
    -x ".DS_Store" \
    -x "tests/*" \
    -x "phpunit.xml" \
    -x ".idea/*" \
    -x ".vscode/*" \
    -x "docker-compose.yml" \
    -x "README.md" \
    -x "package-lock.json" \
    -x "webpack.mix.js" \
    -x "_ide_helper.php" \
    -x "_ide_helper_models.php" \
    -x ".phpstorm.meta.php" \
    -x "ide-helper/*" \
    -x "deploy.sh" \
    -x ".lando/*" \
    -x ".lando.yml" \
    -x "pint.yaml" \
    -x ".docker/*" \
    -x "storage/app/public/*.mp4"

# Print success message
echo "Laravel project has been zipped successfully into $ZIP_FILE"

# Upload to sftp server with username and password
SERVER="104.129.11.149"
USER="liveecob1VW"
UPLOAD_PATH="/home/$USER/live.ecomnet.us/public_html"
SFTP_PASS="e3DBm86SlVn20ibIa"
# ssh-copy-id -p 8282 liveecob1VW@$SERVER
sshpass -p "$SFTP_PASS" sftp -oPort=8282 -oBatchMode=no -b - $USER@$SERVER <<EOF
  cd $UPLOAD_PATH
  rm $ZIP_FILE
  put $ZIP_FILE
  exit
EOF

rm $ZIP_FILE

# Unzip the file and install composer dependencies
ssh -p 8282 root@$SERVER <<EOF
  cd $UPLOAD_PATH
  unzip -o $ZIP_FILE
  composer install
  rm $ZIP_FILE
  chown -R $USER:$USER $UPLOAD_PATH
  # rm ./storage/logs/laravel.log
  # php artisan migrate:fresh --seed --force
  # cat ./storage/logs/laravel.log
  sudo supervisorctl restart laravel-worker:*
  exit
EOF

# ftp to the server
SERVER="148.163.101.218"
UPLOAD_PATH="/www/wwwroot/worker.ecomnet.us"
ssh-copy-id -p 23201 root@$SERVER
sftp -oPort=23201 -oBatchMode=no -b - root@$SERVER <<EOF
  cd $UPLOAD_PATH
  put $ZIP_FILE
  exit
EOF

# Unzip the file and install composer dependencies
ssh -p 23201 root@$SERVER <<EOF
  cd $UPLOAD_PATH
  unzip -o $ZIP_FILE
  composer install
  rm $ZIP_FILE
  cp .env.example .env
  php artisan key:generate
  php artisan optimize:clear
  sudo /www/server/panel/pyenv/bin/supervisorctl restart live-stream:*
  exit
EOF
