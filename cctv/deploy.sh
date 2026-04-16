#!/bin/bash
# CCTV 모니터링 시스템 배포 스크립트
# sudo ./deploy.sh 로 실행하세요

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
WEB_ROOT="/var/www/html"
TARGET="$WEB_ROOT/cctv"

echo "=== CCTV 모니터링 배포 ==="

# 1. python3-pymysql 설치
echo "[1/4] python3-pymysql 설치..."
apt-get install -y python3-pymysql -q

# 2. /var/www/html/cctv 디렉토리 생성 및 복사
echo "[2/4] PHP 파일 배포..."
mkdir -p "$TARGET"
cp "$SCRIPT_DIR/index.php" "$TARGET/index.php"
chown -R www-data:www-data "$TARGET"

# 3. Apache 재시작 확인
echo "[3/4] Apache 상태 확인..."
systemctl is-active apache2 || systemctl start apache2

# 4. 완료
echo "[4/4] 완료!"
echo ""
echo "  모니터링 주소: http://localhost/cctv/"
echo ""
echo "  데이터 주입 실행:"
echo "    python3 $SCRIPT_DIR/injector.py"
echo ""