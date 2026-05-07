"""Standalone edge detection demo — generates a video file without ROS2 or a camera."""

import math
import sys

import cv2
import numpy as np

W, H = 1280, 480   # left=original, right=edge
FPS = 30
TOTAL_FRAMES = FPS * 12   # 12초


def draw_scene(t):
    """Generate a synthetic scene at time t (seconds) for edge detection demo."""
    frame = np.zeros((H, W // 2, 3), dtype=np.uint8)

    # 배경 그라디언트
    for y in range(H):
        v = int(30 + 20 * math.sin(y / 60 + t))
        frame[y, :] = [v, v // 2, v // 3]

    # 회전하는 사각형
    cx, cy = W // 4, H // 2
    angle = t * 40
    size = 120
    pts = cv2.boxPoints(((cx, cy), (size, size), angle))
    pts = pts.astype(np.int32)
    cv2.fillPoly(frame, [pts], (180, 100, 60))
    cv2.polylines(frame, [pts], True, (255, 200, 100), 3)

    # 이동하는 원
    cx2 = int(W // 8 + (W // 8) * math.sin(t * 1.2))
    cy2 = int(H // 2 + (H // 3) * math.cos(t * 0.8))
    cv2.circle(frame, (cx2, cy2), 60, (60, 180, 220), -1)
    cv2.circle(frame, (cx2, cy2), 60, (255, 255, 255), 2)

    # 삼각형
    tx = int(W // 4 + 80 * math.cos(t * 0.5))
    ty = int(H // 3 + 60 * math.sin(t * 0.7))
    tri = np.array([
        [tx, ty - 70],
        [tx - 60, ty + 40],
        [tx + 60, ty + 40],
    ], dtype=np.int32)
    cv2.fillPoly(frame, [tri], (220, 80, 160))
    cv2.polylines(frame, [tri], True, (255, 255, 100), 2)

    # 파동 선
    pts_wave = []
    for x in range(0, W // 2, 3):
        y_w = int(H * 0.75 + 25 * math.sin(x / 30 + t * 3))
        pts_wave.append([x, y_w])
    cv2.polylines(frame, [np.array(pts_wave, dtype=np.int32)], False, (100, 255, 150), 3)

    # 텍스트
    cv2.putText(frame, 'shkim_edge Demo', (10, 30),
                cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255, 255, 255), 2)
    cv2.putText(frame, f't={t:.1f}s', (10, 60),
                cv2.FONT_HERSHEY_SIMPLEX, 0.6, (200, 200, 200), 1)

    return frame


def apply_edge_canny(gray, t):
    """Apply Canny edge detection with oscillating thresholds."""
    thr1 = int(40 + 30 * math.sin(t * 0.5))
    thr2 = thr1 * 3
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    edges = cv2.Canny(blurred, thr1, thr2)
    return edges, thr1, thr2


def apply_edge_sobel(gray):
    """Apply Sobel edge detection."""
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    sx = cv2.Sobel(blurred, cv2.CV_64F, 1, 0, ksize=3)
    sy = cv2.Sobel(blurred, cv2.CV_64F, 0, 1, ksize=3)
    edges = np.sqrt(sx ** 2 + sy ** 2)
    return np.clip(edges, 0, 255).astype(np.uint8)


def apply_edge_laplacian(gray):
    """Apply Laplacian edge detection."""
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    lap = cv2.Laplacian(blurred, cv2.CV_64F)
    return np.clip(np.abs(lap), 0, 255).astype(np.uint8)


def main():
    """Generate edge detection demo video."""
    out_path = '/home/asdf/Desktop/ws/edge_detection_demo.mp4'
    fourcc = cv2.VideoWriter_fourcc(*'mp4v')
    writer = cv2.VideoWriter(out_path, fourcc, FPS, (W, H))

    if not writer.isOpened():
        print('VideoWriter 열기 실패. 경로 확인:', out_path)
        sys.exit(1)

    # 구간별 알고리즘: 0~4s Canny, 4~8s Sobel, 8~12s Laplacian
    SEGMENTS = [
        (0, 4, 'canny'),
        (4, 8, 'sobel'),
        (8, 12, 'laplacian'),
    ]

    print(f'총 {TOTAL_FRAMES}프레임 생성 중... ({TOTAL_FRAMES / FPS:.0f}초)')

    for frame_idx in range(TOTAL_FRAMES):
        t = frame_idx / FPS

        # 현재 구간 알고리즘 선택
        method = 'canny'
        for s, e, m in SEGMENTS:
            if s <= t < e:
                method = m
                break

        # 원본 씬 생성
        orig = draw_scene(t)
        gray = cv2.cvtColor(orig, cv2.COLOR_BGR2GRAY)

        # 에지 검출
        thr1 = thr2 = 0
        if method == 'canny':
            edges, thr1, thr2 = apply_edge_canny(gray, t)
        elif method == 'sobel':
            edges = apply_edge_sobel(gray)
        else:
            edges = apply_edge_laplacian(gray)

        edges_bgr = cv2.cvtColor(edges, cv2.COLOR_GRAY2BGR)

        # 에지 이미지에 정보 오버레이
        method_color = {'canny': (0, 255, 255), 'sobel': (255, 200, 0), 'laplacian': (200, 100, 255)}
        color = method_color[method]
        cv2.putText(edges_bgr, f'Method: {method.upper()}', (10, 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2)
        if method == 'canny':
            cv2.putText(edges_bgr, f'Thr: {thr1} / {thr2}', (10, 60),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (200, 200, 200), 1)
        edge_px = int(np.sum(edges > 0))
        cv2.putText(edges_bgr, f'Edge pixels: {edge_px}', (10, 85),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.6, (180, 255, 180), 1)

        # 구간 안내바 (하단)
        bar_w = int((W // 2) * (t % 4) / 4)
        cv2.rectangle(edges_bgr, (0, H - 8), (W // 2, H), (50, 50, 50), -1)
        cv2.rectangle(edges_bgr, (0, H - 8), (bar_w, H), color, -1)

        # 원본에 구간 표시
        seg_label = f'[{int(t // 4) + 1}/3] {method.upper()} ({int(t % 4) + 1}/4s)'
        cv2.putText(orig, seg_label, (10, H - 15),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.55, color, 2)

        # 좌우 합성
        combined = np.hstack([orig, edges_bgr])

        # 구분선
        cv2.line(combined, (W // 2, 0), (W // 2, H), (255, 255, 255), 2)

        writer.write(combined)

        if frame_idx % (FPS * 2) == 0:
            pct = frame_idx / TOTAL_FRAMES * 100
            print(f'  {pct:.0f}% ({frame_idx}/{TOTAL_FRAMES}) | t={t:.1f}s | {method}')

    writer.release()
    print(f'\n완료! 영상 저장: {out_path}')
    return out_path


if __name__ == '__main__':
    main()
