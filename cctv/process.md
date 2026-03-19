# CCTV 차량 속도 모니터링 시스템

LAMP 스택 기반의 CCTV 가상 데이터 생성 및 실시간 모니터링 시스템 구축 과정

---

## 시스템 개요

| 항목 | 내용 |
|------|------|
| 도로 제한 속도 | 100 km/h |
| 속도 편차 | ±10 km/h (랜덤) |
| 데이터 주입 주기 | 5초마다 랜덤 2개 카메라 |
| 모니터링 갱신 | 5초 자동 새로고침 |
| CCTV 카메라 수 | 5개 |

---

## 환경 구성 (LAMP Stack)

이미 설치된 환경을 확인 후 진행

```bash
apache2 -v     # Apache 2.4.58
mysql --version  # MySQL 8.0.45
php --version    # PHP 8.3.6
python3 --version  # Python 3.12.3
```

### pymysql 설치

Python에서 MySQL에 접속하기 위한 드라이버 설치

```bash
sudo apt install -y python3-pymysql
```

---

## 데이터베이스 구성

### DB 및 테이블 생성

`cctv_db` 데이터베이스와 `speed_logs` 테이블 생성
root 계정은 소켓 인증 방식으로 접속

```bash
mysql -u root --socket=/var/run/mysqld/mysqld.sock
```

```sql
CREATE DATABASE cctv_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON cctv_db.* TO 'chalice355'@'localhost';
FLUSH PRIVILEGES;

USE cctv_db;

CREATE TABLE speed_logs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    camera_id     VARCHAR(20)    NOT NULL,
    location      VARCHAR(100)   NOT NULL,
    vehicle_plate VARCHAR(20)    NOT NULL,
    measured_speed DECIMAL(5,1)  NOT NULL,
    speed_limit   INT            NOT NULL DEFAULT 100,
    is_violation  TINYINT(1)     NOT NULL DEFAULT 0,
    recorded_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_camera_id   (camera_id),
    INDEX idx_violation   (is_violation)
) ENGINE=InnoDB;
```

### 테이블 컬럼 설명

| 컬럼 | 설명 |
|------|------|
| `id` | 자동 증가 기본키 |
| `camera_id` | CCTV 카메라 식별자 (CAM_001 ~ CAM_005) |
| `location` | 카메라 설치 위치 |
| `vehicle_plate` | 차량 번호판 |
| `measured_speed` | 측정 속도 (km/h) |
| `speed_limit` | 제한 속도 (기본값 100) |
| `is_violation` | 과속 여부 (1: 과속, 0: 정상) |
| `recorded_at` | 감지 시각 |

---

## 파일 구조

```
2001218/
└── cctv/
    ├── injector.py   # 가상 데이터 생성기 (Python)
    ├── index.php     # 실시간 모니터링 대시보드 (PHP)
    ├── deploy.sh     # 배포 스크립트
    └── process.md    # 본 문서
```

---

## 주요 파일 설명

### injector.py

가상의 CCTV 속도 측정 데이터를 생성해 DB에 삽입하는 Python 스크립트

**동작 방식**
- 5개 카메라 중 매 주기마다 `random.sample()`로 2개를 랜덤 선택
- 속도는 `100 + random.uniform(-10, 10)` 으로 계산
- 100 km/h 초과 시 `is_violation = 1` 로 저장
- 5초(`time.sleep(5)`) 간격으로 반복

**가상 카메라 목록**

| 카메라 ID | 설치 위치 |
|-----------|-----------|
| CAM_001 | 경부고속도로 서울TG 북쪽 1km |
| CAM_002 | 경부고속도로 수원IC 부근 |
| CAM_003 | 올림픽대로 잠실대교 동측 |
| CAM_004 | 자유로 행주대교 남측 |
| CAM_005 | 중부고속도로 하남JC 서쪽 |

**실행**
```bash
python3 cctv/injector.py
```

---

### index.php

PHP로 작성된 실시간 모니터링 대시보드

**구성 요소**

| 섹션 | 내용 |
|------|------|
| 상단 통계 | 최근 10분 감지 수 / 과속 건수 / 평균·최고 속도 / 제한 속도 |
| 카메라 카드 | 카메라별 최신 감지 차량 속도 (과속 시 빨간색 강조) |
| 로그 테이블 | 최신 50건 감지 기록 (id DESC 내림차순) |

**자동 새로고침**
```html
<meta http-equiv="refresh" content="5">
```

**로그 정렬 기준**
`recorded_at` 대신 `id DESC` 사용 — 같은 초에 삽입된 여러 행도 정확한 순서 보장

**접속 주소**
```
http://localhost/cctv/
```

---

### deploy.sh

웹서버에 PHP 파일을 배포하는 스크립트

```bash
sudo bash cctv/deploy.sh
```

**수행 작업**
1. `python3-pymysql` apt 설치
2. `/var/www/html/cctv/` 디렉토리 생성 및 `index.php` 복사
3. Apache 실행 상태 확인

> `index.php` 수정 후 웹서버 반영 시 수동 복사 필요
> ```bash
> sudo cp cctv/index.php /var/www/html/cctv/index.php
> ```

---

## 실행 순서

```bash
# 1. 배포 (최초 1회)
sudo bash cctv/deploy.sh

# 2. 데이터 주입 시작
python3 cctv/injector.py

# 3. 브라우저에서 확인
# http://localhost/cctv/
```

---

## DB 초기화

```bash
mysql -u chalice355 -psb953379 cctv_db -e "TRUNCATE TABLE speed_logs;"
```

`TRUNCATE` 사용 시 모든 데이터 삭제 및 `id` auto_increment 1로 리셋

---

## GitHub

```
https://github.com/chalice355/embedded-linux
```