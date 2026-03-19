#!/usr/bin/env python3
"""
CCTV 차량 속도 측정 가상 데이터 생성기
도로 제한 속도: 100 km/h | 속도 편차: ±10 km/h | 주입 주기: 5초
"""

import pymysql
import random
import time
from datetime import datetime

# DB 설정
DB_CONFIG = {
    'host': 'localhost',
    'db': 'cctv_db',
    'user': 'chalice355',
    'password': 'sb953379',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.Cursor,
}

# CCTV 카메라 목록 (카메라ID, 설치 위치)
CAMERAS = [
    ('CAM_001', '경부고속도로 서울TG 북쪽 1km'),
    ('CAM_002', '경부고속도로 수원IC 부근'),
    ('CAM_003', '올림픽대로 잠실대교 동측'),
    ('CAM_004', '자유로 행주대교 남측'),
    ('CAM_005', '중부고속도로 하남JC 서쪽'),
]

# 번호판 지역 코드
REGIONS = ['서울', '경기', '인천', '부산', '대구', '광주', '대전', '울산']
LETTERS = 'ABCDEFGHJKLMNPQRSTUVWXYZ'

SPEED_LIMIT = 100  # km/h
SPEED_VARIANCE = 10  # ±10 km/h


def generate_plate() -> str:
    """가상 차량 번호판 생성"""
    region = random.choice(REGIONS)
    number = random.randint(10, 99)
    letter = random.choice(LETTERS)
    seq = random.randint(1000, 9999)
    return f"{region} {number}{letter} {seq}"


def generate_speed() -> float:
    """제한 속도 ±10km/h 범위의 랜덤 속도 생성"""
    variance = random.uniform(-SPEED_VARIANCE, SPEED_VARIANCE)
    speed = SPEED_LIMIT + variance
    return round(speed, 1)


def insert_record(cursor, camera_id: str, location: str, plate: str, speed: float):
    """DB에 속도 측정 기록 삽입"""
    is_violation = 1 if speed > SPEED_LIMIT else 0
    sql = """
        INSERT INTO speed_logs (camera_id, location, vehicle_plate, measured_speed, speed_limit, is_violation)
        VALUES (%s, %s, %s, %s, %s, %s)
    """
    cursor.execute(sql, (camera_id, location, plate, speed, SPEED_LIMIT, is_violation))


def main():
    print("=" * 60)
    print("  CCTV 차량 속도 모니터링 데이터 주입기")
    print(f"  제한 속도: {SPEED_LIMIT} km/h | 편차: ±{SPEED_VARIANCE} km/h | 주기: 5초")
    print("=" * 60)
    print("Ctrl+C 로 종료\n")

    conn = pymysql.connect(**DB_CONFIG)
    conn.autocommit(True)
    cursor = conn.cursor()

    try:
        while True:
            # 매 주기마다 랜덤 2개 카메라에서 차량 감지
            for camera_id, location in random.sample(CAMERAS, 2):
                plate = generate_plate()
                speed = generate_speed()
                insert_record(cursor, camera_id, location, plate, speed)

                status = "🚨 과속" if speed > SPEED_LIMIT else "✅ 정상"
                ts = datetime.now().strftime("%H:%M:%S")
                print(f"[{ts}] {camera_id} | {plate} | {speed:5.1f} km/h | {status}")

            print("-" * 60)
            time.sleep(5)

    except KeyboardInterrupt:
        print("\n\n데이터 주입 종료.")
    finally:
        cursor.close()
        conn.close()


if __name__ == '__main__':
    main()