-- 도서 대출 관리 시스템 데이터베이스 스키마

CREATE DATABASE IF NOT EXISTS library_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE library_db;

-- 도서 테이블
CREATE TABLE IF NOT EXISTS books (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    author       VARCHAR(100) NOT NULL,
    isbn         VARCHAR(20)  UNIQUE,
    publisher    VARCHAR(100),
    quantity     INT NOT NULL DEFAULT 1,
    available_qty INT NOT NULL DEFAULT 1,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 회원 테이블
CREATE TABLE IF NOT EXISTS members (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50)  NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    phone      VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 대출 테이블
CREATE TABLE IF NOT EXISTS loans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    book_id     INT NOT NULL,
    member_id   INT NOT NULL,
    loan_date   DATE NOT NULL,
    due_date    DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status      ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
    FOREIGN KEY (book_id)   REFERENCES books(id)   ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 샘플 데이터
INSERT INTO books (title, author, isbn, publisher, quantity, available_qty) VALUES
('클린 코드', '로버트 C. 마틴', '9788966260959', '인사이트', 3, 3),
('리팩터링', '마틴 파울러', '9791162242742', '한빛미디어', 2, 2),
('파이썬 완벽 가이드', '마크 루츠', '9788965400189', '오라일리', 2, 2);

INSERT INTO members (name, email, phone) VALUES
('홍길동', 'hong@example.com', '010-1234-5678'),
('김철수', 'kim@example.com', '010-9876-5432');