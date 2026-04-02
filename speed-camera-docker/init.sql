CREATE DATABASE IF NOT EXISTS speed_camera;
USE speed_camera;

CREATE TABLE IF NOT EXISTS camera_logs (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  captured_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
  vehicle_no   INT          NOT NULL COMMENT '차량 번호 (1 or 2)',
  vehicle_type VARCHAR(20)  NOT NULL COMMENT '차종',
  speed_kmh    INT          NOT NULL COMMENT '측정 속도 (km/h)',
  speed_limit  INT          NOT NULL DEFAULT 80 COMMENT '제한 속도 (km/h)',
  status       VARCHAR(10)  NOT NULL COMMENT '정상 / 과속',
  location     VARCHAR(100) NOT NULL COMMENT '단속 위치'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
