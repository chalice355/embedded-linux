# 도서 대출 관리 시스템

PHP + MySQL + Apache(LAMP) 기반 도서 대출 관리 웹 애플리케이션

## 환경 요구사항
- Linux (Zorin OS)
- Apache 2.4+
- MySQL 5.7+ / MariaDB 10+
- PHP 7.4+

---

## 설치 방법

### 1. 패키지 설치 확인
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql libapache2-mod-php -y
```

### 2. MySQL 데이터베이스 초기화
```bash
sudo mysql -u root -p < /var/www/html/library/config/schema.sql
```
> MySQL 비밀번호가 없는 경우: `sudo mysql < config/schema.sql`

### 3. 프로젝트 파일 배포
```bash
sudo cp -r library/ /var/www/html/
sudo chown -R www-data:www-data /var/www/html/library/
```

### 4. DB 연결 설정 수정
`config/db.php` 에서 DB 접속 정보를 환경에 맞게 수정:
```php
define('DB_USER', 'root');   // MySQL 사용자명
define('DB_PASS', '');       // MySQL 비밀번호
```

### 5. Apache 재시작
```bash
sudo systemctl restart apache2
```

### 6. 브라우저 접속
```
http://localhost/library/
```

---

## 디렉토리 구조
```
library/
├── index.php              # 메인 라우터
├── config/
│   ├── db.php             # DB 연결 설정
│   └── schema.sql         # DB 스키마 및 샘플 데이터
├── models/
│   ├── Book.php
│   ├── Member.php
│   └── Loan.php
├── controllers/
│   ├── BookController.php
│   ├── MemberController.php
│   └── LoanController.php
├── views/
│   ├── layout.php         # 공통 레이아웃
│   ├── dashboard.php      # 대시보드
│   ├── books/
│   ├── members/
│   └── loans/
└── assets/
    └── css/style.css
```

---

## 주요 기능
| 기능 | URL |
|------|-----|
| 대시보드 | `/library/` |
| 도서 목록 | `/library/?page=books` |
| 도서 등록 | `/library/?page=books&action=create` |
| 회원 목록 | `/library/?page=members` |
| 대출 현황 | `/library/?page=loans` |
| 대출 처리 | `/library/?page=loans&action=create` |
| 대출 이력 | `/library/?page=loans&action=history` |
| 연체 도서 | `/library/?page=loans&action=overdue` |

---

## 에러 로그

| 날짜 | 에러 내용 | 원인 | 해결 방법 |
|------|----------|------|----------|
| 2026-03-12 | ERROR 2002 (HY000): Can't connect to local MySQL server through socket '/var/run/mysqld/mysqld.sock' (2) | MySQL 서비스가 실행되지 않음 | `sudo systemctl start mysql` 으로 MySQL 시작 후 재시도 |
| 2026-03-12 | mysql.service: Failed with result 'exit-code' | 기존 MySQL 설치가 손상됨 | `sudo apt remove --purge mysql-server` 로 완전 제거 후 재설치 |
| 2026-03-12 | SQLSTATE[HY000] [1698] Access denied for user 'root'@'localhost' | Ubuntu/Zorin MySQL root 계정이 auth_socket으로 잠김 | `sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY ''; FLUSH PRIVILEGES;"` 실행 |

> 빌드/실행 중 에러 발생 시 이 표에 기록합니다.